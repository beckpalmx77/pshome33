<?php
require_once '../config/connect_db.php';

set_time_limit(0);
date_default_timezone_set('Asia/Bangkok');

function writeLog($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message" . PHP_EOL;
}

$successCount = 0;
$errorCount   = 0;
$skippedCount = 0;

try {
    $dbName = $conn->query("SELECT DATABASE()")->fetchColumn();
    $stmt   = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    writeLog("=== เริ่มต้น Optimize/Reindex ทั้งหมด " . count($tables) . " ตาราง ใน DB: $dbName ===");

    foreach ($tables as $table) {
        try {
            $infoStmt = $conn->prepare(
                "SELECT TABLE_TYPE, ENGINE FROM information_schema.TABLES 
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?"
            );
            $infoStmt->execute([$dbName, $table]);
            $info = $infoStmt->fetch(PDO::FETCH_ASSOC);

            // ข้าม View
            if ($info['TABLE_TYPE'] === 'VIEW') {
                writeLog("⏭️  [VIEW] `$table` → ข้าม");
                $skippedCount++;
                continue;
            }

            $engine = strtoupper($info['ENGINE']);

            $analyzeResult = $conn->query("ANALYZE TABLE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            $analyzeMsg    = $analyzeResult[0]['Msg_text'] ?? 'OK';

            if (in_array($engine, ['MYISAM', 'ARIA'])) {
                $optResult = $conn->query("OPTIMIZE TABLE `$table`")->fetchAll(PDO::FETCH_ASSOC);
                $optMsg    = $optResult[0]['Msg_text'] ?? 'OK';
                writeLog("✅ [$engine] `$table` | ANALYZE: $analyzeMsg | OPTIMIZE: $optMsg");

            } elseif ($engine === 'INNODB') {
                $optResult = $conn->query("OPTIMIZE TABLE `$table`")->fetchAll(PDO::FETCH_ASSOC);
                $optMsg    = $optResult[0]['Msg_text'] ?? 'OK';
                writeLog("✅ [INNODB] `$table` | ANALYZE: $analyzeMsg | OPTIMIZE: $optMsg");

            } else {
                writeLog("⏭️  [$engine] `$table` → ข้าม (ไม่รองรับ OPTIMIZE)");
                $skippedCount++;
                continue;
            }

            $successCount++;

        } catch (PDOException $e) {
            writeLog("❌ ERROR: `$table` → " . $e->getMessage());
            $errorCount++;
        }
    }

    writeLog("=== สรุป: สำเร็จ $successCount | ข้าม $skippedCount | ผิดพลาด $errorCount ===");
    writeLog("=== สิ้นสุดกระบวนการทั้งหมด ===");

} catch (PDOException $e) {
    writeLog("CRITICAL ERROR: " . $e->getMessage());
}