<?php
require_once '../config/connect_db.php';

// ป้องกัน Timeout สำหรับกรณีตารางมีขนาดใหญ่มาก
set_time_limit(0);
date_default_timezone_set('Asia/Bangkok');

$logFile = 'db_optimize_log_' . date('Y-m-d') . '.log';

function writeLog($message, $file) {
    $timestamp = date('Y-m-d H:i:s');
    // หากรันผ่าน Command Line ใช้ PHP_EOL จะขึ้นบรรทัดใหม่สวยงามกว่า <br>
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    //file_put_contents($file, $logEntry, FILE_APPEND);
    echo $logEntry;
}

try {
    // 1. ดึงชื่อ Database ปัจจุบันออกมาแสดงใน Log
    $dbName = $conn->query("SELECT DATABASE()")->fetchColumn();

    // 2. ดึงรายชื่อตารางทั้งหมด
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    //writeLog("--- เริ่มต้น Optimize ทั้งหมด " . count($tables) . " ตาราง ใน DB: $dbName ---", $logFile);

    foreach ($tables as $table) {
        try {
            // 3. รันคำสั่ง OPTIMIZE (ลบคำว่า Susan ออกแล้ว)
            // การใช้ backticks (`) ครอบชื่อตารางช่วยป้องกัน Error กรณีชื่อตารางเป็นคำสงวน
            $conn->query("OPTIMIZE TABLE `$table` ");
            writeLog("สำเร็จ: ตาราง `$table` ", $logFile);
        } catch (PDOException $e) {
            //writeLog("ผิดพลาด: ตาราง `$table` - " . $e->getMessage(), $logFile);
        }
    }

    //writeLog("--- สิ้นสุดกระบวนการทั้งหมด ---", $logFile);

} catch (PDOException $e) {
    //writeLog("CRITICAL ERROR: " . $e->getMessage(), $logFile);
}