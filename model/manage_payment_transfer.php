<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $house_number = $_POST['house_number'];
    $detail = $_POST['detail'];
    $amount = $_POST['amount'];
    $remark = $_POST['remark'];
    $picture_payment = $_FILES['picture_payment'];

    // ตรวจสอบว่าไฟล์ถูกอัปโหลดหรือไม่
    if ($picture_payment['error'] == 0) {
        $upload_dir = '../uploads/slips/';
        $file_name = time() . "_" . basename($picture_payment['name']);
        $file_path = $upload_dir . $file_name;

        // อัปโหลดไฟล์
        if (move_uploaded_file($picture_payment['tmp_name'], $file_path)) {
            // บันทึกข้อมูลลงในฐานข้อมูล
            $stmt = $conn->prepare("INSERT INTO ims_house_payment (house_number, detail, amount, remark, picture_payment) 
            VALUES (:house_number, :detail, :amount, :remark, :picture_payment) ");

            // Bind ข้อมูล
            $stmt->bindParam(':house_number', $house_number);
            $stmt->bindParam(':detail', $detail);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':remark', $remark);
            $stmt->bindParam(':picture_payment', $file_name);

            if ($stmt->execute()) {
                echo 1; // สำเร็จ
            } else {
                echo 0; // เกิดข้อผิดพลาดในฐานข้อมูล
            }
        } else {
            echo 0; // เกิดข้อผิดพลาดในการอัปโหลดไฟล์
        }
    } else {
        echo 0; // ไม่มีไฟล์ถูกอัปโหลด
    }
}

