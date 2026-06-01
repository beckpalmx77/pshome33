<?php
include '../config/connect_db.php';

$sql = "SELECT house_number, 
        car_no1,
        car_no2,
        car_no3,
        car_no4,
        car_no5,
        car_no6,
        car_no7,
        car_no8,
        sticker_receive_status
        FROM ims_house 
        WHERE (sticker_receive_status IS NULL OR sticker_receive_status <> 'Y') AND car_no1 IS NOT NULL AND car_no1 <> '' 
        ORDER BY CAST(house_number AS UNSIGNED) ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$return_arr = array();
$total_house = count($results);

foreach ($results as $result) {
    $carCount = 0;
    if (!empty($result['car_no1'])) $carCount++;
    if (!empty($result['car_no2'])) $carCount++;
    if (!empty($result['car_no3'])) $carCount++;
    if (!empty($result['car_no4'])) $carCount++;
    if (!empty($result['car_no5'])) $carCount++;
    if (!empty($result['car_no6'])) $carCount++;
    if (!empty($result['car_no7'])) $carCount++;
    if (!empty($result['car_no8'])) $carCount++;
    
    $return_arr[] = array(
        "house_number" => $result['house_number'],
        "car_no1" => $result['car_no1'] ?? '',
        "car_no2" => $result['car_no2'] ?? '',
        "car_no3" => $result['car_no3'] ?? '',
        "car_no4" => $result['car_no4'] ?? '',
        "car_no5" => $result['car_no5'] ?? '',
        "car_no6" => $result['car_no6'] ?? '',
        "car_no7" => $result['car_no7'] ?? '',
        "car_no8" => $result['car_no8'] ?? '',
        "car_count" => $carCount
    );
}

echo json_encode([
    "data" => $return_arr,
    "total_house" => $total_house
]);
