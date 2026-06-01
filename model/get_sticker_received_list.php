<?php
include '../config/connect_db.php';

$sql = "SELECT house_number, 
        car_no1, car_no1_province, car_no1_brand, car_no1_color, car_no1_type,
        car_no2, car_no2_province, car_no2_brand, car_no2_color, car_no2_type,
        car_no3, car_no3_province, car_no3_brand, car_no3_color, car_no3_type,
        car_no4, car_no4_province, car_no4_brand, car_no4_color, car_no4_type,
        car_no5, car_no5_province, car_no5_brand, car_no5_color, car_no5_type,
        car_no6, car_no6_province, car_no6_brand, car_no6_color, car_no6_type,
        car_no7, car_no7_province, car_no7_brand, car_no7_color, car_no7_type,
        car_no8, car_no8_province, car_no8_brand, car_no8_color, car_no8_type,
        sticker_receive_date 
        FROM ims_house 
        WHERE sticker_receive_status = 'Y' 
        ORDER BY sticker_receive_date DESC, CAST(house_number AS UNSIGNED) DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$return_arr = array();
$total_cars = 0;
$total_extra_fee = 0;
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
    
    $extraCarFee = 0;
    if ($carCount > 2) {
        $extraCarFee = ($carCount - 2) * 100;
    }
    
    $total_cars += $carCount;
    $total_extra_fee += $extraCarFee;
    
    $return_arr[] = array(
        "house_number" => $result['house_number'],
        "car_no1" => $result['car_no1'] ?? '',
        "car_no1_province" => $result['car_no1_province'] ?? '',
        "car_no1_brand" => $result['car_no1_brand'] ?? '',
        "car_no1_color" => $result['car_no1_color'] ?? '',
        "car_no1_type" => $result['car_no1_type'] ?? '',
        "car_no2" => $result['car_no2'] ?? '',
        "car_no2_province" => $result['car_no2_province'] ?? '',
        "car_no2_brand" => $result['car_no2_brand'] ?? '',
        "car_no2_color" => $result['car_no2_color'] ?? '',
        "car_no2_type" => $result['car_no2_type'] ?? '',
        "car_no3" => $result['car_no3'] ?? '',
        "car_no3_province" => $result['car_no3_province'] ?? '',
        "car_no3_brand" => $result['car_no3_brand'] ?? '',
        "car_no3_color" => $result['car_no3_color'] ?? '',
        "car_no3_type" => $result['car_no3_type'] ?? '',
        "car_no4" => $result['car_no4'] ?? '',
        "car_no4_province" => $result['car_no4_province'] ?? '',
        "car_no4_brand" => $result['car_no4_brand'] ?? '',
        "car_no4_color" => $result['car_no4_color'] ?? '',
        "car_no4_type" => $result['car_no4_type'] ?? '',
        "car_no5" => $result['car_no5'] ?? '',
        "car_no5_province" => $result['car_no5_province'] ?? '',
        "car_no5_brand" => $result['car_no5_brand'] ?? '',
        "car_no5_color" => $result['car_no5_color'] ?? '',
        "car_no5_type" => $result['car_no5_type'] ?? '',
        "car_no6" => $result['car_no6'] ?? '',
        "car_no6_province" => $result['car_no6_province'] ?? '',
        "car_no6_brand" => $result['car_no6_brand'] ?? '',
        "car_no6_color" => $result['car_no6_color'] ?? '',
        "car_no6_type" => $result['car_no6_type'] ?? '',
        "car_no7" => $result['car_no7'] ?? '',
        "car_no7_province" => $result['car_no7_province'] ?? '',
        "car_no7_brand" => $result['car_no7_brand'] ?? '',
        "car_no7_color" => $result['car_no7_color'] ?? '',
        "car_no7_type" => $result['car_no7_type'] ?? '',
        "car_no8" => $result['car_no8'] ?? '',
        "car_no8_province" => $result['car_no8_province'] ?? '',
        "car_no8_brand" => $result['car_no8_brand'] ?? '',
        "car_no8_color" => $result['car_no8_color'] ?? '',
        "car_no8_type" => $result['car_no8_type'] ?? '',
        "car_count" => $carCount,
        "extra_car_fee" => $extraCarFee,
        "sticker_receive_date" => $result['sticker_receive_date'] ?? ''
    );
}

echo json_encode([
    "data" => $return_arr,
    "summary" => [
        "total_house" => $total_house,
        "total_cars" => $total_cars,
        "total_extra_fee" => $total_extra_fee
    ]
]);