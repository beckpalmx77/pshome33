<?php
session_start();
error_reporting(0);
header('Content-Type: application/json');
include('../config/connect_db.php');

// ตรวจสอบ Session
if ($_SESSION['alogin'] == "") {
    echo json_encode(["status" => "error", "message" => "Session Timeout"]);
    exit();
}

// รับค่า Action
$action = isset($_POST['action']) ? $_POST['action'] : '';

// --------------------------------------------------------------------------
// 1. GET_MEETING_LIST : สำหรับแสดงผลใน DataTables
// --------------------------------------------------------------------------
if ($action == 'GET_MEETING_LIST') {
    // รับ Parameter จาก DataTables
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length'];
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];
    $searchValue = $_POST['search']['value'];

    $meeting_year_filter = isset($_POST['meeting_year']) ? $_POST['meeting_year'] : '';
    $meeting_date_filter = isset($_POST['meeting_date']) ? $_POST['meeting_date'] : '';

    $columns = array(
        0 => 'house_number',
        1 => 'fullname',
        2 => 'phone_number',
        3 => 'meeting_date',
        4 => 'meeting_detail',
        5 => 'checkin_point',
        6 => 'id',
    );

    if (isset($columns[$columnIndex])) {
        $sortColumn = $columns[$columnIndex];
    } else {
        $sortColumn = 'id';
        $columnSortOrder = 'DESC';
    }

    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (house_number LIKE :search 
                          OR fullname LIKE :search 
                          OR phone_number LIKE :search 
                          OR meeting_detail LIKE :search ) ";
    }

    $filterQuery = " ";
    $filterParams = [];
    if ($meeting_year_filter != '') {
        $filterQuery .= " AND meeting_date LIKE :meeting_year ";
        $filterParams[':meeting_year'] = '%' . $meeting_year_filter;
    }
    if ($meeting_date_filter != '') {
        $filterQuery .= " AND meeting_date = :meeting_date ";
        $filterParams[':meeting_date'] = $meeting_date_filter;
    }

    $sql_count = "SELECT COUNT(*) AS allcount FROM ims_register_meeting WHERE 1=1 " . $filterQuery;
    $stmt = $conn->prepare($sql_count);
    foreach ($filterParams as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    $sql_count_filter = "SELECT COUNT(*) AS allcount FROM ims_register_meeting WHERE 1=1 " . $filterQuery . $searchQuery;
    $stmt = $conn->prepare($sql_count_filter);
    foreach ($filterParams as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    if ($searchValue != '') {
        $stmt->bindValue(':search', '%' . $searchValue . '%', PDO::PARAM_STR);
    }
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    $sql = "SELECT * FROM ims_register_meeting WHERE 1=1 " . $filterQuery . $searchQuery .
        " ORDER BY " . $sortColumn . " " . $columnSortOrder .
        " LIMIT :offset, :limit";

    $stmt = $conn->prepare($sql);
    foreach ($filterParams as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    if ($searchValue != '') {
        $stmt->bindValue(':search', '%' . $searchValue . '%', PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$row, PDO::PARAM_INT);

    $stmt->execute();
    $empRecords = $stmt->fetchAll();

    $data = array();
    foreach ($empRecords as $row) {
        $data[] = array(
            "id" => $row['id'],
            "house_number" => $row['house_number'],
            "fullname" => $row['fullname'],
            "phone_number" => $row['phone_number'],
            "meeting_date" => $row['meeting_date'],
            "meeting_detail" => $row['meeting_detail'],
            "checkin_point" => $row['checkin_point'],
            "lat_addr" => $row['lat_addr'],
            "long_addr" => $row['long_addr'],
            "created_at" => $row['created_at']
        );
    }

    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
    exit();
}

// --------------------------------------------------------------------------
// 2. GET_DATA : ดึงข้อมูลมาแสดงใน Modal เพื่อแก้ไข
// --------------------------------------------------------------------------
if ($action == 'GET_DATA') {
    $id = $_POST['id'];
    $sql = "SELECT * FROM ims_register_meeting WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
    exit();
}

// --------------------------------------------------------------------------
// 3. UPDATE_ATTENDANCE : แก้ไขข้อมูลการลงทะเบียน (เช่น พิมพ์ชื่อผิด)
// --------------------------------------------------------------------------
if ($action == 'UPDATE_ATTENDANCE') {
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $house_number = $_POST['house_number'];
    $phone_number = $_POST['phone_number'];

    // meeting_date ไม่แนะนำให้แก้ง่ายๆ เพราะเป็น Key ในการเช็คซ้ำ แต่ถ้าต้องการแก้เพิ่มได้ที่นี่

    if (empty($id)) {
        echo json_encode(["status" => "error", "message" => "Error: ID is missing."]);
        exit();
    }

    try {
        $sql = "UPDATE ims_register_meeting 
                SET fullname = :fullname, 
                    house_number = :house_number,
                    phone_number = :phone_number
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':house_number', $house_number);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "แก้ไขข้อมูลเรียบร้อย"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error: ไม่สามารถบันทึกข้อมูลได้"]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
    }
    exit();
}