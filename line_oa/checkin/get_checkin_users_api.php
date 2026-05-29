<?php
header("Content-Type: application/json");
require "../../config/connect_db.php";

try {
    // ดึงข้อมูล user_id และ display_name แบบไม่ซ้ำ โดยยึดตาม user_id เป็นหลัก
    $sql = "SELECT user_id, display_name FROM v_checkins_time WHERE user_id IS NOT NULL AND user_id <> '' GROUP BY user_id ORDER BY display_name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
