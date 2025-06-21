<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');

// ลบข้อมูล
if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT COUNT(*) FROM ims_house_payment WHERE id = :id";
    $stmt = $conn->prepare($sql_find);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $nRows = $stmt->fetchColumn();

    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_house_payment WHERE id = :id";
            $query = $conn->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }

    exit;
}

if ($_POST["action"] === 'GET_COMMON_FEE') {

    // อ่านค่าที่ส่งมา
    $draw = $_POST['draw'];
    $row = isset($_POST['start']) ? (int)$_POST['start'] : 0;               // แถวเริ่มต้น (offset)
    $rowperpage = isset($_POST['length']) ? (int)$_POST['length'] : 10;     // จำนวนแถวต่อหน้า (limit)
    $columnIndex = $_POST['order'][0]['column'];                            // ดัชนีคอลัมน์ที่สั่งเรียง
    $columnName = $_POST['columns'][$columnIndex]['data'];                  // ชื่อคอลัมน์ที่ใช้เรียง
    $columnSortOrder = 'desc';                                              // สั่งเรียง (แก้ได้ตามต้องการ)
    $searchValue = $_POST['search']['value'];                              // คำค้นหา

    $searchArray = [];

    // เงื่อนไขค้นหา
    $searchQuery = "";
    if ($searchValue != '') {
        $searchQuery = " AND house_number LIKE :house_number ";
        $searchArray['house_number'] = "%$searchValue%";
    }

    // เงื่อนไขกรองสิทธิ์ user (ถ้า account_type เป็น user)
    $where_house_number = "";
    if (isset($_SESSION['account_type']) && $_SESSION['account_type'] === "user") {
        $houseNum = $_SESSION['house_number'] ?? '';
        $where_house_number = " AND house_number = :session_house_number ";
        $searchArray['session_house_number'] = $houseNum;
    }

    // 1. นับจำนวนข้อมูลทั้งหมด (ไม่มี filter)
    $sql_total = "
        SELECT COUNT(*) AS allcount 
        FROM ims_house_payment 
        WHERE (house_number, period_year, period_month_start) IN (
            SELECT house_number, period_year, period_month_start
            FROM ims_house_payment
            GROUP BY house_number, period_year, period_month_start
            HAVING COUNT(*) > 1
        ) $where_house_number
    ";
    $stmt = $conn->prepare($sql_total);
    // bind เฉพาะถ้ามีเงื่อนไข session house_number
    if (isset($searchArray['session_house_number'])) {
        $stmt->bindValue(':session_house_number', $searchArray['session_house_number'], PDO::PARAM_STR);
    }
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();

    // 2. นับจำนวนข้อมูลที่ผ่านการ filter (ค้นหา)
    $sql_filtered = "
        SELECT COUNT(*) AS allcount 
        FROM ims_house_payment 
        WHERE (house_number, period_year, period_month_start) IN (
            SELECT house_number, period_year, period_month_start
            FROM ims_house_payment
            GROUP BY house_number, period_year, period_month_start
            HAVING COUNT(*) > 1
        )
        $searchQuery
        $where_house_number
    ";
    $stmt = $conn->prepare($sql_filtered);

    // bind ค่าที่ใช้ใน filter ทั้ง search และ session house_number
    foreach ($searchArray as $key => $value) {
        $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
    }
    $stmt->execute();
    $totalRecordwithFilter = $stmt->fetchColumn();

    // 3. ดึงข้อมูลหลัก (พร้อมการ filter, pagination, และ sort)

    $columnName = " house_number, period_year, period_month_start, id ";

    $sql_data = "
        SELECT 
            id,
            house_number,
            period_year,
            period_month_start,
            month_name_start,
            period_month_to,
            month_name_to,
            payment_status,
            picture_payment,
            amount,
            COUNT(*) OVER (PARTITION BY house_number, period_year, period_month_start) AS dup_count
        FROM v_ims_house_payment
        WHERE (house_number, period_year, period_month_start) IN (
            SELECT house_number, period_year, period_month_start
            FROM ims_house_payment
            GROUP BY house_number, period_year, period_month_start
            HAVING COUNT(*) > 1
        )
        $searchQuery
        $where_house_number
        ORDER BY $columnName 
        LIMIT :offset, :limit
    ";
    $stmt = $conn->prepare($sql_data);

    // bind ค่า filter
    foreach ($searchArray as $key => $value) {
        $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
    }
    // bind ค่า pagination
    $stmt->bindValue(':offset', $row, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $rowperpage, PDO::PARAM_INT);

    $stmt->execute();
    $empRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    $isUser = (isset($_SESSION['account_type']) && $_SESSION['account_type'] === "user");
    $isMaster = (isset($_POST['sub_action']) && $_POST['sub_action'] === "GET_MASTER");

    $statusMeta = [
        'Y' => ['desc' => "ชำระเรียบร้อยแล้ว", 'color' => 'green', 'can_print' => true],
        'N' => ['desc' => "ยังไม่ยืนยันการชำระ", 'color' => 'gray', 'can_print' => false],
    ];

    foreach ($empRecords as $row) {
        if ($isMaster) {
            $status = $row['payment_status'];
            $meta = $statusMeta[$status] ?? ['desc' => '-', 'color' => 'gray', 'can_print' => false];

            $data[] = [
                "id" => $row['id'],
                "house_number" => $row['house_number'],
                "period_month_start" => $row['period_month_start'],
                "period_month_to" => $row['period_month_to'],
                "month_name_start" => $row['month_name_start'],
                "month_name_to" => $row['month_name_to'],
                "period_year" => $row['period_year'],
                "dup_count" => $row['dup_count'],
                "amount" => $row['amount'],
                "payment_status" => $row['payment_status'],
                "payment_status_desc" => "<span style='color: {$meta['color']}'>{$meta['desc']}</span>",
                "slip" => "<button type='button' name='slip' id='{$row['id']}' class='btn btn-info btn-xs slip'>Slip</button>",
                "delete" => $isUser
                    ? "<button type='button' class='btn btn-danger btn-xs delete' disabled>Delete</button>"
                    : "<button type='button' name='delete' id='{$row['id']}' class='btn btn-danger btn-xs delete'>Delete</button>"
            ];
        } else {
            // กรณี sub_action ไม่ใช่ GET_MASTER (ยังไม่ทราบโครงสร้าง contact_name)
            $data[] = [
                "id" => $row['id'],
                "house_number" => $row['house_number'],
                "contact_name" => $row['contact_name'] ?? '',
                "select" => "<button type='button' name='select' id='{$row['house_number']}@{$row['contact_name']}' class='btn btn-outline-success btn-xs select'>select <i class='fa fa-check'></i></button>"
            ];
        }
    }

    // สร้าง JSON Response
    $response = [
        "draw" => intval($draw),
        "iTotalRecords" => intval($totalRecords),
        "iTotalDisplayRecords" => intval($totalRecordwithFilter),
        "aaData" => $data
    ];

    // ส่ง JSON response กลับ
    echo json_encode($response);
}


