<?php
// save_checkin_meeting_data.php
header('Content-Type: text/html; charset=utf-8');
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
        // Insert ลงตาราง ims_register_meeting
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
            echo "
            <script>
                alert('✅ ลงทะเบียนสำเร็จ!\\nขอบคุณคุณ $fullname');
                // ส่งกลับไปหน้าเดิม (ชื่อไฟล์ใหม่)
                // window.location.href = 'checkin_meeting_register.php?point=$checkin_point'; 
            </script>
            ";
        } else {
            echo "บันทึกข้อมูลไม่สำเร็จ";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request";
}