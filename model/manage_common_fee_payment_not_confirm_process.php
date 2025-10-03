<?php
session_start();
error_reporting(0);

require_once('../config/connect_db.php');
require_once('../config/lang.php');
require_once('../util/record_util.php');
require_once('../util/reorder_record.php');

if ($_POST["action"] === 'GET_COMMON_FEE') {
    $draw = intval($_POST['draw']);
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $columnIndex = intval($_POST['order'][0]['column']);
    $columnName = $_POST['columns'][$columnIndex]['data'] ?? 'id';
    $columnSortOrder = strtolower($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
    $searchValue = trim($_POST['search']['value']);

    // ป้องกัน injection ใน column name (white-list)
    $validColumns = ['id', 'doc_id', 'payment_date', 'house_number', 'contact_name', 'payment_status'];
    if (!in_array($columnName, $validColumns)) {
        $columnName = 'id';
    }

    // สร้างเงื่อนไข WHERE
    $whereClauses = ["payment_status = 'N'"];
    $params = [];

    if ($_SESSION['account_type'] === "user") {
        $whereClauses[] = "house_number = :user_house";
        $params['user_house'] = $_SESSION['house_number'];
    }

    if ($searchValue !== '') {
        $whereClauses[] = "house_number LIKE :search_house";
        $params['search_house'] = "%$searchValue%";
    }

    $whereSQL = implode(' AND ', $whereClauses);

    // ฟังก์ชันช่วยนับจำนวน record
    function countRecords($conn, $whereSQL, $params) {
        $sql = "SELECT COUNT(*) AS cnt FROM v_ims_house_payment WHERE $whereSQL";
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    $totalRecords = countRecords($conn, "payment_status = 'N'" . (isset($params['user_house']) ? " AND house_number = :user_house" : ""), array_filter($params, fn($k) => $k === 'user_house', ARRAY_FILTER_USE_KEY));
    $totalRecordwithFilter = countRecords($conn, $whereSQL, $params);

    // ดึงข้อมูล
    //$sql = "SELECT * FROM v_ims_house_payment WHERE $whereSQL ORDER BY $columnName $columnSortOrder LIMIT :start, :length";
    $sql = "SELECT * FROM v_ims_house_payment 
        WHERE $whereSQL 
        ORDER BY id DESC 
        LIMIT :start, :length";

    $stmt = $conn->prepare($sql);

    foreach ($params as $key => $val) {
        $stmt->bindValue(":$key", $val);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ฟังก์ชันช่วยแปลงข้อมูลให้เหมาะสม
    function formatRow($row, $isUser, $sub_action) {
        if ($sub_action === "GET_MASTER") {
            $isPaid = $row['payment_status'] === 'Y';
            $payment_status_desc = $isPaid ? "ชำระเรียบร้อยแล้ว" : "ยังไม่ยืนยันการชำระ";
            $color = $isPaid ? "green" : "gray";
            $print_disabled = $isPaid ? "" : "disabled";

            $isUser = $_SESSION['account_type'] === "user";
            $isManager = $_SESSION['account_type'] === "manager";

            return [
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
                // "line_picture_profile" => $row['line_picture_profile_show'],
                "line_picture_profile" => "<img src='" . ($row['line_picture_profile_show'] ?: 'img/icon/none_img.png') . "' alt='image' style='width: 50px; height: auto;'>",
                "payment_status_desc" => "<span style='color: $color;'>$payment_status_desc</span>",
                "print" => "<button type='button' name='print' id='{$row['id']}' class='btn btn-outline-success btn-xs print' $print_disabled>Print</button>",
                "slip" => "<button type='button' name='slip' id='{$row['id']}' class='btn btn-info btn-xs slip'>Slip</button>",
                "update" => "<button type='button' class='btn btn-info btn-xs update' name='update' id='{$row['id']}' " . ($isUser ? "disabled" : "") . ">Update</button>",
                //"delete" => "<button type='button' class='btn btn-danger btn-xs delete' name='delete' id='{$row['id']}' " . ($isUser ? "disabled" : "") . ">Delete</button>",
                "delete" => $isUser || $isManager ? "<button type='button' class='btn btn-danger btn-xs delete' disabled>Delete</button>"
                    : "<button type='button' name='delete' id='{$row['id']}' class='btn btn-danger btn-xs delete'>Delete</button>",
                "remark" => $row['remark']
            ];
        } else {
            return [
                "id" => $row['id'],
                "house_number" => $row['house_number'],
                "contact_name" => $row['contact_name'],
                "select" => "<button type='button' name='select' id='{$row['house_number']}@{$row['contact_name']}' class='btn btn-outline-success btn-xs select'>select <i class='fa fa-check'></i></button>"
            ];
        }
    }

    $data = [];
    $isUser = $_SESSION['account_type'] === "user";
    $sub_action = $_POST['sub_action'] ?? '';

    foreach ($rows as $row) {
        $data[] = formatRow($row, $isUser, $sub_action);
    }

    echo json_encode([
        "draw" => $draw,
        "iTotalRecords" => intval($totalRecords),
        "iTotalDisplayRecords" => intval($totalRecordwithFilter),
        "aaData" => $data
    ]);
}
