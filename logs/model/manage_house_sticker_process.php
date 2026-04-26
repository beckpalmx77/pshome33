<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจาก POST และตัดช่องว่าง
    $car_no1 = trim($_POST['car_no1'] ?? '');
    $car_no1_province = trim($_POST['car_no1_province'] ?? '');
    $car_no1_brand = trim($_POST['car_no1_brand'] ?? '');
    $car_no1_color = trim($_POST['car_no1_color'] ?? '');
    $car_no1_type = trim($_POST['car_no1_type'] ?? '');
    $car_no2 = trim($_POST['car_no2'] ?? '');
    $car_no2_province = trim($_POST['car_no2_province'] ?? '');
    $car_no2_brand = trim($_POST['car_no2_brand'] ?? '');
    $car_no2_color = trim($_POST['car_no2_color'] ?? '');
    $car_no2_type = trim($_POST['car_no2_type'] ?? '');
    $car_no3 = trim($_POST['car_no3'] ?? '');
    $car_no3_province = trim($_POST['car_no3_province'] ?? '');
    $car_no3_brand = trim($_POST['car_no3_brand'] ?? '');
    $car_no3_color = trim($_POST['car_no3_color'] ?? '');
    $car_no3_type = trim($_POST['car_no3_type'] ?? '');
    $car_no4 = trim($_POST['car_no4'] ?? '');
    $car_no4_province = trim($_POST['car_no4_province'] ?? '');
    $car_no4_brand = trim($_POST['car_no4_brand'] ?? '');
    $car_no4_color = trim($_POST['car_no4_color'] ?? '');
    $car_no4_type = trim($_POST['car_no4_type'] ?? '');
    $car_no5 = trim($_POST['car_no5'] ?? '');
    $car_no5_province = trim($_POST['car_no5_province'] ?? '');
    $car_no5_brand = trim($_POST['car_no5_brand'] ?? '');
    $car_no5_color = trim($_POST['car_no5_color'] ?? '');
    $car_no5_type = trim($_POST['car_no5_type'] ?? '');
    $car_no6 = trim($_POST['car_no6'] ?? '');
    $car_no6_province = trim($_POST['car_no6_province'] ?? '');
    $car_no6_brand = trim($_POST['car_no6_brand'] ?? '');
    $car_no6_color = trim($_POST['car_no6_color'] ?? '');
    $car_no6_type = trim($_POST['car_no6_type'] ?? '');
    $car_no7 = trim($_POST['car_no7'] ?? '');
    $car_no7_province = trim($_POST['car_no7_province'] ?? '');
    $car_no7_brand = trim($_POST['car_no7_brand'] ?? '');
    $car_no7_color = trim($_POST['car_no7_color'] ?? '');
    $car_no7_type = trim($_POST['car_no7_type'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $house_status = trim($_POST['house_status'] ?? '');
    $house_number = trim($_POST['house_number'] ?? '');
    $house_number_old = trim($_POST['house_number_old'] ?? '');

    $sql = "UPDATE ims_house 
            SET car_no1 = :car_no1, 
                car_no1_province = :car_no1_province,
                car_no1_brand = :car_no1_brand,
                car_no1_color = :car_no1_color,
                car_no1_type = :car_no1_type,
                car_no2 = :car_no2, 
                car_no2_province = :car_no2_province,
                car_no2_brand = :car_no2_brand,
                car_no2_color = :car_no2_color,
                car_no2_type = :car_no2_type,
                car_no3 = :car_no3, 
                car_no3_province = :car_no3_province,
                car_no3_brand = :car_no3_brand,
                car_no3_color = :car_no3_color,
                car_no3_type = :car_no3_type,
                car_no4 = :car_no4,
                car_no4_province = :car_no4_province,
                car_no4_brand = :car_no4_brand,
                car_no4_color = :car_no4_color,
                car_no4_type = :car_no4_type,
                car_no5 = :car_no5,
                car_no5_province = :car_no5_province,
                car_no5_brand = :car_no5_brand,
                car_no5_color = :car_no5_color,
                car_no5_type = :car_no5_type,
                car_no6 = :car_no6,
                car_no6_province = :car_no6_province,
                car_no6_brand = :car_no6_brand,
                car_no6_color = :car_no6_color,
                car_no6_type = :car_no6_type,
                car_no7 = :car_no7,
                car_no7_province = :car_no7_province,
                car_no7_brand = :car_no7_brand,
                car_no7_color = :car_no7_color,
                car_no7_type = :car_no7_type,
                house_status = :house_status
            WHERE house_number = :house_number AND sticker_receive_status = 'N' ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':car_no1', $car_no1);
    $stmt->bindParam(':car_no1_province', $car_no1_province);
    $stmt->bindParam(':car_no1_brand', $car_no1_brand);
    $stmt->bindParam(':car_no1_color', $car_no1_color);
    $stmt->bindParam(':car_no1_type', $car_no1_type);
    $stmt->bindParam(':car_no2', $car_no2);
    $stmt->bindParam(':car_no2_province', $car_no2_province);
    $stmt->bindParam(':car_no2_brand', $car_no2_brand);
    $stmt->bindParam(':car_no2_color', $car_no2_color);
    $stmt->bindParam(':car_no2_type', $car_no2_type);
    $stmt->bindParam(':car_no3', $car_no3);
    $stmt->bindParam(':car_no3_province', $car_no3_province);
    $stmt->bindParam(':car_no3_brand', $car_no3_brand);
    $stmt->bindParam(':car_no3_color', $car_no3_color);
    $stmt->bindParam(':car_no3_type', $car_no3_type);
    $stmt->bindParam(':car_no4', $car_no4);
    $stmt->bindParam(':car_no4_province', $car_no4_province);
    $stmt->bindParam(':car_no4_brand', $car_no4_brand);
    $stmt->bindParam(':car_no4_color', $car_no4_color);
    $stmt->bindParam(':car_no4_type', $car_no4_type);
    $stmt->bindParam(':car_no5', $car_no5);
    $stmt->bindParam(':car_no5_province', $car_no5_province);
    $stmt->bindParam(':car_no5_brand', $car_no5_brand);
    $stmt->bindParam(':car_no5_color', $car_no5_color);
    $stmt->bindParam(':car_no5_type', $car_no5_type);
    $stmt->bindParam(':car_no6', $car_no6);
    $stmt->bindParam(':car_no6_province', $car_no6_province);
    $stmt->bindParam(':car_no6_brand', $car_no6_brand);
    $stmt->bindParam(':car_no6_color', $car_no6_color);
    $stmt->bindParam(':car_no6_type', $car_no6_type);
    $stmt->bindParam(':car_no7', $car_no7);
    $stmt->bindParam(':car_no7_province', $car_no7_province);
    $stmt->bindParam(':car_no7_brand', $car_no7_brand);
    $stmt->bindParam(':car_no7_color', $car_no7_color);
    $stmt->bindParam(':car_no7_type', $car_no7_type);
    $stmt->bindParam(':house_status', $house_status);
    $stmt->bindParam(':house_number', $house_number);

    if ($stmt->execute()) {
        echo 1;
    } else {
        error_log("SQL Error: " . implode(", ", $stmt->errorInfo()));
        echo 0;
    }
}
