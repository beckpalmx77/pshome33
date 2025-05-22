<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // รับค่าจาก POST และตัดช่องว่าง
        $car_no1 = trim($_POST['car_no1'] ?? '');
        $car_no2 = trim($_POST['car_no2'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $house_status = trim($_POST['house_status'] ?? '');
        $house_number = trim($_POST['house_number'] ?? '');
        $house_number_old = trim($_POST['house_number_old'] ?? '');

        // Debug ข้อมูลที่รับเข้ามา (ในช่วงพัฒนาเท่านั้น)
        // $txt = "Current " . $house_number . " | Old ".  $house_number_old . " | Phone ". $phone_number;

        // อัปเดตข้อมูลใน ims_house
        $sql = "UPDATE ims_house 
                SET car_no1 = :car_no1, car_no2 = :car_no2, house_status = :house_status, house_number = :house_number
                WHERE house_number = :house_number_old AND phone_number = :phone_number";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':car_no1', $car_no1);
        $stmt->bindParam(':car_no2', $car_no2);
        $stmt->bindParam(':house_status', $house_status);
        $stmt->bindParam(':house_number', $house_number);
        $stmt->bindParam(':house_number_old', $house_number_old);
        $stmt->bindParam(':phone_number', $phone_number);

        if ($stmt->execute()) {

            // อัปเดต house_number ใน ims_house_line_user ด้วย
            $sql_2 = "UPDATE ims_house_line_user 
                      SET house_number = :house_number
                      WHERE house_number = :house_number_old AND phone_number = :phone_number";

            $stmt_2 = $conn->prepare($sql_2);
            $stmt_2->bindParam(':house_number', $house_number);
            $stmt_2->bindParam(':house_number_old', $house_number_old);
            $stmt_2->bindParam(':phone_number', $phone_number);
            $stmt_2->execute();

            echo 1;
        } else {
            echo 0;
        }
    } catch (PDOException $e) {
        // กรณีเกิดข้อผิดพลาดกับฐานข้อมูล
        echo "Error: " . $e->getMessage();
    }
}
?>
