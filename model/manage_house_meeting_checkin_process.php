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

    // รับค่าปีการประชุม (เอาไว้กรอง String meeting_date)
    $meeting_year_filter = isset($_POST['meeting_year']) ? $_POST['meeting_year'] : '';

    // Mapping Column สำหรับ Sort
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
        $sortColumn = 'id'; // Default sort by ID (Latest)
        $columnSortOrder = 'DESC';
    }

    // สร้างเงื่อนไขการค้นหา (Search)
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (house_number LIKE :search 
                          OR fullname LIKE :search 
                          OR phone_number LIKE :search 
                          OR meeting_detail LIKE :search ) ";
    }

    // --- สร้างเงื่อนไขกรองปี (Year Filter) ---
    // เนื่องจาก meeting_date เป็น varchar (เช่น 25-12-2025) เราจะใช้ LIKE '%YYYY'
    $yearQuery = " ";
    if ($meeting_year_filter != '') {
        $yearQuery = " AND meeting_date LIKE :meeting_year ";
    }

    // 1. นับจำนวนทั้งหมด (Total Records)
    $sql_count = "SELECT COUNT(*) AS allcount FROM ims_register_meeting WHERE 1=1 " . $yearQuery;
    $stmt = $conn->prepare($sql_count);
    if ($meeting_year_filter != '') {
        $stmt->bindValue(':meeting_year', '%' . $meeting_year_filter, PDO::PARAM_STR);
    }
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    // 2. นับจำนวนแบบมี Search (Filtered Records)
    $sql_count_filter = "SELECT COUNT(*) AS allcount FROM ims_register_meeting WHERE 1=1 " . $yearQuery . $searchQuery;
    $stmt = $conn->prepare($sql_count_filter);
    if ($meeting_year_filter != '') {
        $stmt->bindValue(':meeting_year', '%' . $meeting_year_filter, PDO::PARAM_STR);
    }
    if ($searchValue != '') {
        $stmt->bindValue(':search', '%' . $searchValue . '%', PDO::PARAM_STR);
    }
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    // 3. ดึงข้อมูล (Select Data)
    $sql = "SELECT * FROM ims_register_meeting WHERE 1=1 " . $yearQuery . $searchQuery .
        " ORDER BY " . $sortColumn . " " . $columnSortOrder .
        " LIMIT :offset, :limit";

    $stmt = $conn->prepare($sql);

    // Bind Parameters
    if ($meeting_year_filter != '') {
        $stmt->bindValue(':meeting_year', '%' . $meeting_year_filter, PDO::PARAM_STR);
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
            "long_addr" => $row['long_addr']
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