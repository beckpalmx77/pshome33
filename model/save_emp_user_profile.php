<?php
require_once '../config/connect_db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_POST['userId'] ?? '';
    $displayName = $_POST['displayName'] ?? '';
    $pictureUrl = $_POST['pictureUrl'] ?? '';

    if (!empty($userId)) {
        try {
            // ตรวจสอบว่ามีผู้ใช้นี้อยู่แล้วหรือไม่
            $stmt = $conn->prepare("SELECT COUNT(*) FROM ims_employee_line_user WHERE line_user_id = :userId");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $exists = $stmt->fetchColumn();

            if ($exists) {
                // ถ้ามีอยู่แล้ว ให้ UPDATE
                $sql = "UPDATE ims_employee_line_user 
                        SET line_user_name = :displayName, line_picture_profile = :pictureUrl 
                        WHERE line_user_id = :userId";
            } else {
                // ถ้าไม่มี ให้ INSERT ใหม่
                $sql = "INSERT INTO ims_employee_line_user (line_user_id, line_user_name, line_picture_profile)
                        VALUES (:userId, :displayName, :pictureUrl)";
            }

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':displayName', $displayName);
            $stmt->bindParam(':pictureUrl', $pictureUrl);
            $stmt->execute();

            echo 'success';
        } catch (PDOException $e) {
            echo 'error: ' . $e->getMessage(); // หรือจะ log ลงไฟล์
        }
    } else {
        echo 'invalid';
    }
}
?>
