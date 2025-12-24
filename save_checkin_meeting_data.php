<?php
// save_checkin_meeting_data.php

// บอก Browser ว่าเป็น UTF-8 (เผื่อกรณี error จะได้อ่านออก)
header('Content-Type: text/html; charset=utf-8');

require_once 'config/connect_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname      = $_POST['fullname'] ?? '';
    $house_number  = $_POST['house_number'] ?? '';
    $phone_number  = $_POST['phone_number'] ?? '';
    $checkin_point = $_POST['checkin_point'] ?? '';
    $lat_addr      = $_POST['lat_addr'] ?? '';
    $long_addr     = $_POST['long_addr'] ?? '';

    // วันที่ปัจจุบันสำหรับเช็คข้อมูลซ้ำ
    $current_date  = date('Y-m-d');

    try {
        // ---------------------------------------------------------
        // 1. ตรวจสอบข้อมูลซ้ำ (บ้านเลขที่ + เบอร์โทร + วันที่เดิม)
        // ---------------------------------------------------------
        $check_sql = "SELECT id FROM ims_register_meeting 
                      WHERE house_number = :house_number 
                      AND phone_number = :phone_number 
                      AND meeting_date = :meeting_date 
                      LIMIT 1";

        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':house_number', $house_number);
        $check_stmt->bindParam(':phone_number', $phone_number);
        $check_stmt->bindParam(':meeting_date', $current_date);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            // --- กรณีซ้ำ: ไม่บันทึก ส่งไปหน้า Complete แบบ Duplicate ---
            $safe_fullname = urlencode($fullname);
            $safe_point = urlencode($checkin_point);

            // ** แก้ไขชื่อไฟล์ตรงนี้ **
            header("Location: checkin_meeting_complete.php?name=$safe_fullname&point=$safe_point&status=duplicate");
            exit();

        } else {
            // ---------------------------------------------------------
            // 2. ถ้าไม่ซ้ำ: บันทึกข้อมูลใหม่
            // ---------------------------------------------------------
            $sql = "INSERT INTO ims_register_meeting 
                    (fullname, house_number, phone_number, checkin_point, lat_addr, long_addr, meeting_date) 
                    VALUES 
                    (:fullname, :house_number, :phone_number, :checkin_point, :lat_addr, :long_addr, :meeting_date)";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':house_number', $house_number);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':checkin_point', $checkin_point);
            $stmt->bindParam(':lat_addr', $lat_addr);
            $stmt->bindParam(':long_addr', $long_addr);
            $stmt->bindParam(':meeting_date', $current_date);

            if ($stmt->execute()) {
                // --- บันทึกสำเร็จ ---
                $safe_fullname = urlencode($fullname);
                $safe_point = urlencode($checkin_point);

                // ** แก้ไขชื่อไฟล์ตรงนี้ **
                header("Location: checkin_meeting_complete?name=$safe_fullname&point=$safe_point&status=success");
                exit();
            } else {
                echo "บันทึกข้อมูลไม่สำเร็จ";
            }
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request";
}