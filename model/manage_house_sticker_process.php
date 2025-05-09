<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจาก POST และตัดช่องว่าง
    $car_no1 = trim($_POST['car_no1'] ?? '');
    $car_no2 = trim($_POST['car_no2'] ?? '');
    $house_status = trim($_POST['house_status'] ?? '');
    $house_number = trim($_POST['house_number'] ?? '');

    $sql = "UPDATE ims_house 
            SET car_no1 = :car_no1, car_no2 = :car_no2 ,house_status = :house_status
            WHERE house_number = :house_number";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':car_no1', $car_no1);
    $stmt->bindParam(':car_no2', $car_no2);
    $stmt->bindParam(':house_status', $house_status);
    $stmt->bindParam(':house_number', $house_number);

    if ($stmt->execute()) {
        echo 1;
    } else {
        echo 0;
    }
}
