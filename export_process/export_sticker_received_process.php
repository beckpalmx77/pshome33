<?php
include '../config/connect_db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sticker_received_' . date('YmdHis') . '.csv');

$output = fopen('php://output', 'w');

fprintf($output, "\xEF\xBB\xBF");

$sql = "SELECT car_no1, car_no2, car_no3, car_no4, car_no5, car_no6, car_no7
        FROM ims_house 
        WHERE sticker_receive_status = 'Y'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    $cars = array(
        $row['car_no1'] ?? '',
        $row['car_no2'] ?? '',
        $row['car_no3'] ?? '',
        $row['car_no4'] ?? '',
        $row['car_no5'] ?? '',
        $row['car_no6'] ?? '',
        $row['car_no7'] ?? ''
    );
    
    $nonEmpty = array_filter($cars, function($v) {
        return !empty(trim($v));
    });
    
    if (!empty($nonEmpty)) {
        fputcsv($output, array_values($nonEmpty), ',', '"');
    }
}

fclose($output);