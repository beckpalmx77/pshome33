<?php
// model/get_employees_for_datatable.php

// 1. ตรวจสอบ Session และการเชื่อมต่อฐานข้อมูล
// ต้องมีไฟล์เชื่อมต่อฐานข้อมูล
include("../config/connect_db.php");

// ตรวจสอบว่ามีข้อมูลถูกส่งมาจากการเรียกของ Datatable หรือไม่
if (empty($_POST)) {
    die(json_encode(["error" => "No data received."]));
}

// 2. รับค่าจาก Datatable
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // จำนวนแถวต่อหน้า
$columnIndex = $_POST['order'][0]['column']; // Index ของคอลัมน์ที่ถูกเรียง
$columnName = $_POST['columns'][$columnIndex]['data']; // ชื่อคอลัมน์ที่ถูกเรียง (จาก Datatable JS)
$columnSortOrder = $_POST['order'][0]['dir']; // ทิศทางการเรียง (asc/desc)
$searchValue = $_POST['search']['value']; // ค่าที่ใช้ค้นหาทั่วตาราง

// 3. เตรียมคอลัมน์ที่อนุญาตให้เรียง/ค้นหาได้ (เพื่อความปลอดภัย)
$columns = array(
    0 => 'emp_id',
    1 => 'full_name',
    2 => 'department_name',
    3 => 'emp_status', // ใช้ชื่อคอลัมน์จริงใน DB
    4 => 'salary_base',
    5 => 'start_date'
);

// ป้องกัน SQL Injection โดยการตรวจสอบชื่อคอลัมน์
$orderColumn = $columns[$columnIndex];

// 4. เงื่อนไขการค้นหา (Search)
$searchQuery = "";
if ($searchValue != '') {
    // ค้นหาในหลายคอลัมน์
    $searchQuery = " AND (
        e.emp_id LIKE :search OR 
        e.full_name LIKE :search OR
        d.department_name LIKE :search 
    ) ";
}

// 5. การนับจำนวน Total Records (ไม่รวม Filter)
$stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM memployee WHERE status = 'Y' ");
$stmt->execute();
$records = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRecords = $records['allcount'];

// 6. การนับจำนวน Records (รวม Filter)
$stmt = $conn->prepare("
    SELECT COUNT(*) AS filteredcount 
    FROM memployee e    
    WHERE status = 'Y' " . $searchQuery
);
// Bind ค่าค้นหา
if ($searchValue != '') {
    $stmt->bindValue(':search', '%' . $searchValue . '%', PDO::PARAM_STR);
}
$stmt->execute();
$records = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRecordwithFilter = $records['filteredcount'];

// 7. ดึงข้อมูลพนักงานจริง
$sql = "
    SELECT 
        e.emp_id, 
        e.f_name ,
        e.l_name ,
        CONCAT(e.f_name, ' ', e.l_name) AS full_name,
        e.position_desc,    
        e.status,        
        e.salary,
        e.salary_type,
        e.start_work_date
    FROM v_memployee e
    WHERE status = 'Y' " . $searchQuery . "
    ORDER BY " . $orderColumn . " " . $columnSortOrder . " 
    LIMIT :limit OFFSET :offset
";

$stmt = $conn->prepare($sql);

// Bind ค่าค้นหา (ซ้ำอีกครั้ง)
if ($searchValue != '') {
    $stmt->bindValue(':search', '%' . $searchValue . '%', PDO::PARAM_STR);
}

// Bind ค่า Pagination
$stmt->bindValue(':limit', (int)$rowperpage, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$row, PDO::PARAM_INT);

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);


// 8. จัดรูปแบบข้อมูลให้ Datatable เข้าใจ
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);
exit();