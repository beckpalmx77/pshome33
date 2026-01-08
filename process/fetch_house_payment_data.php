<?php
// process/fetch_house_payment_data.php
require_once '../config/connect_db.php';

$year = isset($_POST['year']) ? $_POST['year'] : date('Y');

// แก้ไข SQL: เอา Backtick (`) ที่ครอบ b.alley ออก หรือครอบแยกเป็น `b`.`alley`
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
    // ป้องกันค่า NULL กรณีไม่พบซอย
    $row['alley'] = $row['alley'] ?? '-';
    $result[] = $row;
}

echo json_encode(['data' => $result]);