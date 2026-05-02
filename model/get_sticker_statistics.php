<?php

include '../config/connect_db.php';

// นับจำนวนรถทั้งหมดที่ลงทะเบียน (นับเฉพาะช่องที่ไม่ว่าง car_no1-car_no10)
$sql_total = "SELECT 
    SUM(
        CASE WHEN car_no1 IS NOT NULL AND car_no1 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no2 IS NOT NULL AND car_no2 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no3 IS NOT NULL AND car_no3 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no4 IS NOT NULL AND car_no4 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no5 IS NOT NULL AND car_no5 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no6 IS NOT NULL AND car_no6 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no7 IS NOT NULL AND car_no7 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no8 IS NOT NULL AND car_no8 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no9 IS NOT NULL AND car_no9 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no10 IS NOT NULL AND car_no10 != '' THEN 1 ELSE 0 END
    ) AS total_cars
FROM ims_house";

$stmt_total = $conn->prepare($sql_total);
$stmt_total->execute();
$row_total = $stmt_total->fetch(PDO::FETCH_ASSOC);
$total_cars = $row_total['total_cars'] ?? 0;

// นับจำนวนบ้านที่รับสติกเกอร์แล้ว (sticker_receive_status = 'Y')
$sql_sticker = "SELECT COUNT(*) AS sticker_received 
                FROM ims_house 
                WHERE sticker_receive_status = 'Y'";

$stmt_sticker = $conn->prepare($sql_sticker);
$stmt_sticker->execute();
$row_sticker = $stmt_sticker->fetch(PDO::FETCH_ASSOC);
$sticker_received = $row_sticker['sticker_received'] ?? 0;

// นับจำนวนรถที่รับสติกเกอร์แล้ว (รถจากบ้านที่รับสติกเกอร์แล้ว)
$sql_cars_sticker = "SELECT 
    SUM(
        CASE WHEN car_no1 IS NOT NULL AND car_no1 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no2 IS NOT NULL AND car_no2 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no3 IS NOT NULL AND car_no3 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no4 IS NOT NULL AND car_no4 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no5 IS NOT NULL AND car_no5 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no6 IS NOT NULL AND car_no6 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no7 IS NOT NULL AND car_no7 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no8 IS NOT NULL AND car_no8 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no9 IS NOT NULL AND car_no9 != '' THEN 1 ELSE 0 END +
        CASE WHEN car_no10 IS NOT NULL AND car_no10 != '' THEN 1 ELSE 0 END
    ) AS cars_with_sticker
FROM ims_house
WHERE sticker_receive_status = 'Y'";

$stmt_cars_sticker = $conn->prepare($sql_cars_sticker);
$stmt_cars_sticker->execute();
$row_cars_sticker = $stmt_cars_sticker->fetch(PDO::FETCH_ASSOC);
$cars_with_sticker = $row_cars_sticker['cars_with_sticker'] ?? 0;

echo json_encode([
    'total_cars' => $total_cars,
    'sticker_received' => $sticker_received,
    'cars_with_sticker' => $cars_with_sticker
]);
