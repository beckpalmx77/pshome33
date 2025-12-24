<?php
// save_checkin_meeting_data.php
require_once 'config/connect_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าตัวแปร
    $fullname      = $_POST['fullname'] ?? '';
    $house_number  = $_POST['house_number'] ?? '';
    $phone_number  = $_POST['phone_number'] ?? '';
    $checkin_point = $_POST['checkin_point'] ?? '';
    $lat_addr      = $_POST['lat_addr'] ?? '';
    $long_addr     = $_POST['long_addr'] ?? '';

    try {
        $sql = "INSERT INTO ims_register_meeting 
                (fullname, house_number, phone_number, checkin_point, lat_addr, long_addr) 
                VALUES 
                (:fullname, :house_number, :phone_number, :checkin_point, :lat_addr, :long_addr)";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':house_number', $house_number);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':checkin_point', $checkin_point);
        $stmt->bindParam(':lat_addr', $lat_addr);
        $stmt->bindParam(':long_addr', $long_addr);

        if ($stmt->execute()) {
            // --- ส่วนที่แก้ไข ---

            // แปลงข้อมูลให้ปลอดภัยสำหรับส่งผ่าน URL (เผื่อมีภาษาไทยหรืออักขระพิเศษ)
            $safe_fullname = urlencode($fullname);
            $safe_point = urlencode($checkin_point);

            // สั่ง Redirect ไปยังหน้าสำเร็จ พร้อมส่งชื่อและจุดเช็คอินไปด้วย
            header("Location: checkin_meeting_complete?name=$safe_fullname&point=$safe_point");
            exit(); // จบการทำงานของ Script ทันทีหลังจากสั่ง Redirect

            // --- จบส่วนที่แก้ไข ---

        } else {
            // กรณี error เล็กน้อยอาจจะ echo ได้ แต่ถ้า production ควรทำหน้า error แยก
            header('Content-Type: text/html; charset=utf-8');
            echo "บันทึกข้อมูลไม่สำเร็จ";
        }

    } catch (PDOException $e) {
        header('Content-Type: text/html; charset=utf-8');
        echo "Error: " . $e->getMessage();
    }
} else {
    header('Content-Type: text/html; charset=utf-8');
    echo "Invalid Request";
}