<?php
// process/fetch_house_payment_data.php
require_once '../config/connect_db.php';

$year = isset($_POST['year']) ? $_POST['year'] : date('Y');

// SQL ดึงข้อมูล:
// 1. LEFT JOIN กับตาราง ims_house_master เพื่อเอาซอย (alley)
// 2. ORDER BY แปลงซอยเป็นตัวเลข (CAST AS UNSIGNED) เพื่อให้เรียง 1, 2, 10 ถูกต้อง
$sql = "SELECT a.*, b.alley 
        FROM ims_house_payment_split_monthly_summary a
        LEFT JOIN ims_house_master b ON a.house_number = b.house_number
        WHERE a.period_year = :year 
        ORDER BY CAST(b.alley AS UNSIGNED) ASC, a.house_number ASC";

$stmt = $conn->prepare($sql);
$stmt->execute([':year' => $year]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($data as $row) {
    // ป้องกันค่า NULL กรณีไม่พบข้อมูลใน Master
    $row['alley'] = $row['alley'] ?? '-';
    $result[] = $row;
}

echo json_encode(['data' => $result]);