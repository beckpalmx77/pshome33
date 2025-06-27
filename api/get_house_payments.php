<?php
// api/get_house_payments.php

// เรียกไฟล์เชื่อมต่อฐานข้อมูล
require_once '../config/connect_db.php'; // ตรวจสอบ path ให้ถูกต้องหากไฟล์ db_connect.php ไม่อยู่ในโฟลเดอร์เดียวกัน

// รับค่าจาก DataTables (สำหรับ Server-side processing)
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // จำนวนแถวต่อหน้า
$columnIndex = $_POST['order'][0]['column']; // Index ของคอลัมน์ที่ต้องการเรียง
$columnName = $_POST['columns'][$columnIndex]['data']; // ชื่อคอลัมน์ที่ต้องการเรียง
$columnSortOrder = $_POST['order'][0]['dir']; // asc หรือ desc
$searchValue = $_POST['search']['value']; // ค่าที่ใช้ค้นหาทั่วโลก

## Search
$searchQuery = "";
$searchParams = [];
if($searchValue != ''){
    $searchQuery = " AND (
        ims_house_payment.runno LIKE :searchValue OR 
        ims_house_payment.doc_id LIKE :searchValue OR 
        ims_house_payment.house_number LIKE :searchValue OR 
        ims_house_payment.detail LIKE :searchValue OR 
        ims_house.alley LIKE :searchValue OR 
        v_ims_user.line_user_name LIKE :searchValue OR 
        (CASE WHEN (ims_house_payment.payment_status = 'Y') THEN 'ชำระเรียบร้อยแล้ว' WHEN (ims_house_payment.payment_status = 'N') THEN 'ยังไม่ยืนยันการชำระ' ELSE 'ไม่ทราบสถานะ' END) LIKE :searchValue
    )";
    $searchParams[':searchValue'] = '%' . $searchValue . '%';
}

## Total number of records without filtering
$stmt = $pdo->prepare("SELECT COUNT(*) AS allcount FROM ims_house_payment");
$stmt->execute();
$records = $stmt->fetch();
$totalRecords = $records['allcount'];

## Total number of records with filtering
$sqlFilter = "SELECT COUNT(*) AS allcount 
              FROM v_ims_house_payment 
              WHERE 1 ".$searchQuery;

$stmt = $pdo->prepare($sqlFilter);
$stmt->execute($searchParams);
$records = $stmt->fetch();
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$allowedSortColumns = [
    'id', 'runno', 'doc_id', 'payment_date', 'house_number', 'detail',
    'period_month_start', 'period_month_to', 'period_year', 'amount',
    'remark', 'payment_type', 'payment_status', 'payment_status_desc',
    'created_at', 'updated_at', 'print_first_date', 'print_last_date',
    'print_status', 'month_name_start', 'month_name_to', 'alley',
    'line_user_id', 'line_user_name', 'area_size', 'garbage_collection_fee',
    'common_fee', 'payment_method', 'create_by', 'approve_by'
];

if (!in_array($columnName, $allowedSortColumns)) {
    $columnName = 'id';
}
if (!in_array(strtolower($columnSortOrder), ['asc', 'desc'])) {
    $columnSortOrder = 'asc';
}


$sql = "SELECT 
    * FROM v_ims_
WHERE 1 ".$searchQuery." 
ORDER BY ".$columnName." ".$columnSortOrder." 
LIMIT :row, :rowperpage";

$stmt = $pdo->prepare($sql);

$stmt->bindParam(':row', $row, PDO::PARAM_INT);
$stmt->bindParam(':rowperpage', $rowperpage, PDO::PARAM_INT);

foreach ($searchParams as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}

$stmt->execute();
$data = $stmt->fetchAll();

## Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);
?>