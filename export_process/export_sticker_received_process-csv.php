<?php
include '../config/connect_db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sticker_received_' . date('YmdHis') . '.csv');

$output = fopen('php://output', 'w');

fprintf($output, "\xEF\xBB\xBF");

fputcsv($output, array('บ้านเลขที่', 'ทะเบียนรถ 1', 'ทะเบียนรถ 2', 'ทะเบียนรถ 3', 'ทะเบียนรถ 4', 'ทะเบียนรถ 5', 'ทะเบียนรถ 6', 'ทะเบียนรถ 7', 'วันที่รับสติกเกอร์'), ',', '"');

$sql = "SELECT house_number, 
        car_no1, car_no2, car_no3, car_no4, car_no5, car_no6, car_no7,
        sticker_receive_date 
        FROM ims_house 
        WHERE sticker_receive_status = 'Y' 
        ORDER BY CAST(house_number AS UNSIGNED) ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    fputcsv($output, array(
        $row['house_number'] ?? '',
        $row['car_no1'] ?? '',
        $row['car_no2'] ?? '',
        $row['car_no3'] ?? '',
        $row['car_no4'] ?? '',
        $row['car_no5'] ?? '',
        $row['car_no6'] ?? '',
        $row['car_no7'] ?? '',
        $row['sticker_receive_date'] ?? ''
    ), ',', '"');
}

fclose($output);