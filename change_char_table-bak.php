<?php
// alter_db.php

// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล
require_once 'config/connect_db.php';

try {
    echo "<h3>Start Collation : " . DB_NAME . "</h3>";
    echo "<hr>";

    // 1. เปลี่ยน Default ของ Database
    $db_sql = "ALTER DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    $conn->exec($db_sql);
    echo "<p style='color: green;'>✔ Change Default Database to utf8mb4_general_ci Success</p>";

    // 2. ดึงรายชื่อTableทั้งหมดที่เป็น BASE TABLE (ไม่รวม View)
    $stmt = $conn->prepare("
        SELECT table_name 
        FROM information_schema.TABLES 
        WHERE table_schema = :dbname 
          AND table_type = 'BASE TABLE'
    ");
    $stmt->execute(['dbname' => DB_NAME]);
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "<p>Table Not Found</p>";
        exit;
    }

    echo "<h4> Process All Table (" . count($tables) . " Table):</h4>";
    echo "<ul>";

    // 3. วนลูปเปลี่ยนTableและฟิลด์ภายในทั้งหมด
    foreach ($tables as $table) {
        try {
            // CONVERT TO จะเปลี่ยนโครงสร้างTableและคอลัมน์ที่เป็น Text/Varchar ทั้งหมดพร้อมกัน
            $alter_sql = "ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
            $conn->exec($alter_sql);

            echo "<li>Table <strong>$table</strong>: <span style='color: green;'>Success</span></li>";
        } catch (PDOException $e) {
            // หากเกิด Error (เช่น ปัญหาเรื่องความยาว Index ของTableนั้นๆ) จะแจ้งเตือนแล้วข้ามไปทำTableถัดไป
            echo "<li>Table <strong>$table</strong>: <span style='color: red;'>Error! (" . $e->getMessage() . ")</span></li>";
        }
    }

    echo "</ul>";
    echo "<hr>";
    echo "<h3>=== Process Success ===</h3>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}