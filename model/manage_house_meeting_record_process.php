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
// 1. GET_MEETING_LIST : สำหรับแสดงผลใน DataTables (Server-side)
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
        1 => 'alley',
        2 => 'meeting_date',
        3 => 'meeting_name',
        4 => 'attendance_name',
        5 => 'meeting_status',
        6 => 'id',
    );

    if (isset($columns[$columnIndex])) {
        $sortColumn = $columns[$columnIndex];
    } else {
        $sortColumn = 'house_number';
    }

    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (house_number LIKE :search 
                          OR meeting_name LIKE :search 
                          OR attendance_name LIKE :search 
                          OR meeting_year LIKE :search ) ";
    }

    $filterQuery = " ";
    $filterParams = [];
    if ($meeting_year_filter != '') {
        $filterQuery .= " AND meeting_year = :meeting_year ";
        $filterParams[':meeting_year'] = $meeting_year_filter;
    }
    if ($meeting_date_filter != '') {
        $filterQuery .= " AND meeting_date = :meeting_date ";
        $filterParams[':meeting_date'] = $meeting_date_filter;
    }

    $sql_count = "SELECT COUNT(*) AS allcount FROM v_ims_house_meeting WHERE 1=1 " . $filterQuery;
    $stmt = $conn->prepare($sql_count);
    foreach ($filterParams as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    $sql_count_filter = "SELECT COUNT(*) AS allcount FROM v_ims_house_meeting WHERE 1=1 " . $filterQuery . $searchQuery;
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

    $sql = "SELECT * FROM v_ims_house_meeting WHERE 1=1 " . $filterQuery . $searchQuery .
        " ORDER BY cast(alley as unsigned) ASC, house_number ASC " .
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
            "alley" => $row['alley'],
            "meeting_year" => $row['meeting_year'],
            "meeting_date" => $row['meeting_date'],
            "meeting_name" => $row['meeting_name'],
            "attendance_name" => $row['attendance_name'],
            "meeting_status" => $row['meeting_status'],
            "discount_value" => $row['discount_value']
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
// 2. GET_DATA
// --------------------------------------------------------------------------
if ($action == 'GET_DATA') {
    $id = $_POST['id'];
    $sql = "SELECT * FROM v_ims_house_meeting WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
    exit();
}

// --------------------------------------------------------------------------
// 3. UPDATE_ATTENDANCE
// --------------------------------------------------------------------------
if ($action == 'UPDATE_ATTENDANCE') {
    $id = $_POST['id'];
    $attendance_name = $_POST['attendance_name'];
    $meeting_status = $_POST['meeting_status'];

    if (empty($id)) {
        echo json_encode(["status" => "error", "message" => "Error: ID is missing."]);
        exit();
    }

    try {
        // *** แก้ไข: Update ที่ Table จริง (ims_house_meeting) แทน View (v_ims_house_meeting) ***
        // เพื่อป้องกัน Error: Target table ... is not updatable
        $sql = "UPDATE ims_house_meeting 
                SET attendance_name = :attendance_name, 
                    meeting_status = :meeting_status 
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':attendance_name', $attendance_name);
        $stmt->bindParam(':meeting_status', $meeting_status);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "บันทึกข้อมูลเรียบร้อย"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error: ไม่สามารถบันทึกข้อมูลได้"]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
    }
    exit();
}