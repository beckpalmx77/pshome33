<?php
session_start();
error_reporting(0);

require_once('../config/connect_db.php');
require_once('../config/lang.php');
require_once('../util/record_util.php');
require_once('../util/reorder_record.php');

if ($_POST["action"] === 'GET_COMMON_FEE') {

    // รับค่าเบื้องต้นจาก DataTables
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length'];
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = 'desc';
    $searchValue = $_POST['search']['value'];

    // ค้นหา
    $searchQuery = '';
    $searchArray = [];
    if (!empty($searchValue)) {
        $searchQuery = " AND house_number LIKE :house_number ";
        $searchArray['house_number'] = "%$searchValue%";
    }

    // เงื่อนไขบ้านของ user เฉพาะกรณี account_type = user
    $where_house_number = ($_SESSION['account_type'] === "user")
        ? " AND house_number = :user_house "
        : "";

    if ($_SESSION['account_type'] === "user") {
        $searchArray['user_house'] = $_SESSION['house_number'];
    }

    // นับทั้งหมด (ไม่กรอง)
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_house_payment WHERE payment_status = 'N' $where_house_number");
    $stmt->execute(array_filter($searchArray, fn($k) => $k === 'user_house', ARRAY_FILTER_USE_KEY));
    $totalRecords = $stmt->fetch()['allcount'];

    // นับหลังกรอง
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_house_payment WHERE payment_status = 'N' $searchQuery $where_house_number");
    $stmt->execute($searchArray);
    $totalRecordwithFilter = $stmt->fetch()['allcount'];

    // ดึงข้อมูลจริง
    $sql = "SELECT * FROM v_ims_house_payment 
            WHERE payment_status = 'N' $searchQuery $where_house_number 
            ORDER BY id DESC LIMIT :start, :limit";
    $stmt = $conn->prepare($sql);

    foreach ($searchArray as $key => $value) {
        $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
    }

    $stmt->bindValue(':start', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();

    $data = [];

    foreach ($empRecords as $row) {
        if ($_POST['sub_action'] === "GET_MASTER") {
            $isPaid = $row['payment_status'] === 'Y';
            $isUser = $_SESSION['account_type'] === "user";

            $payment_status_desc = $isPaid ? "ชำระเรียบร้อยแล้ว" : "ยังไม่ยืนยันการชำระ";
            $color = $isPaid ? "green" : "gray";
            $print_disabled = $isPaid ? "" : "disabled";

            $data[] = [
                "id" => $row['id'],
                "doc_id" => $row['doc_id'],
                "payment_date" => $row['payment_date'],
                "detail" => $row['detail'],
                "house_number" => $row['house_number'],
                "alley" => $row['alley'],
                "contact_name" => $row['contact_name'],
                "phone_number" => $row['phone_number'],
                "payment_type" => $row['payment_type'],
                "period_month_start" => $row['period_month_start'],
                "period_month_to" => $row['period_month_to'],
                "month_name_start" => $row['month_name_start'],
                "month_name_to" => $row['month_name_to'],
                "month_name_period" => "{$row['month_name_start']} - {$row['month_name_to']}",
                "period_year" => $row['period_year'],
                "area_size" => $row['area_size'],
                "garbage_collection_fee" => $row['garbage_collection_fee'],
                "common_fee" => $row['common_fee'],
                "amount" => $row['amount'],
                "payment_status" => $row['payment_status'],
                "line_picture_profile" => $row['line_picture_profile_show'],
                "payment_status_desc" => "<span style='color: $color;'>$payment_status_desc</span>",
                "print" => "<button type='button' name='print' id='{$row['id']}' class='btn btn-outline-success btn-xs print' $print_disabled>Print</button>",
                "slip" => "<button type='button' name='slip' id='{$row['id']}' class='btn btn-info btn-xs slip'>Slip</button>",
                "update" => "<button type='button' class='btn btn-info btn-xs update' name='update' id='{$row['id']}' " . ($isUser ? "disabled" : "") . ">Update</button>",
                "delete" => "<button type='button' class='btn btn-danger btn-xs delete' name='delete' id='{$row['id']}' " . ($isUser ? "disabled" : "") . ">Delete</button>",
                "remark" => $row['remark']
            ];
        } else {
            $data[] = [
                "id" => $row['id'],
                "house_number" => $row['house_number'],
                "contact_name" => $row['contact_name'],
                "select" => "<button type='button' name='select' id='{$row['house_number']}@{$row['contact_name']}' class='btn btn-outline-success btn-xs select'>select <i class='fa fa-check'></i></button>"
            ];
        }
    }

    // ส่งผลลัพธ์กลับ
    echo json_encode([
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    ]);
}
