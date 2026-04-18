<?php
include '../config/connect_db.php';

$sql = "SELECT house_number, car_no1, car_no2, car_no3, car_no4, car_no5, sticker_receive_date 
        FROM ims_house 
        WHERE sticker_receive_status = 'Y' 
        ORDER BY house_number ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$return_arr = array();
foreach ($results as $result) {
    $return_arr[] = array(
        "house_number" => $result['house_number'],
        "car_no1" => $result['car_no1'] ?? '',
        "car_no2" => $result['car_no2'] ?? '',
        "car_no3" => $result['car_no3'] ?? '',
        "car_no4" => $result['car_no4'] ?? '',
        "car_no5" => $result['car_no5'] ?? '',
        "sticker_receive_date" => $result['sticker_receive_date'] ?? ''
    );
}

echo json_encode(["data" => $return_arr]);