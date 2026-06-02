<?php
// ไฟล์นี้สำหรับรันผ่าน Cronjob เพื่ออัพโหลดรูปไป Google Drive แบบ Background
include(__DIR__ . '/config/connect_db.php');
include_once(__DIR__ . '/util/google_drive_util.php');

// ตั้งค่าโฟลเดอร์ Log
$logDir = __DIR__ . '/logs';
$logPath = $logDir . '/cron_gdrive.log';
if (!file_exists($logDir)) { 
    mkdir($logDir, 0777, true); 
}

echo "--- Starting Google Drive Sync: " . date('Y-m-d H:i:s') . " ---\n";

// 1. ค้นหารูปภาพที่ยังไม่ได้ Sync
// ค้นหาเฉพาะรายการที่ message_type เป็น image, มีชื่อไฟล์ และ is_google_drive_synced ยังเป็น 0
// จำกัดครั้งละ 10 รูปเพื่อไม่ให้ PHP ทำงานหนักเกินไปในหนึ่งรอบ
$sql = "SELECT id, photo_path FROM ims_line_webhook_messages 
        WHERE message_type = 'image' 
        AND photo_path IS NOT NULL 
        AND photo_path != ''
        AND is_google_drive_synced = 0 
        ORDER BY id ASC LIMIT 10";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) === 0) {
        echo "No pending images to sync.\n";
        exit;
    }

    // ดึงค่าคอนฟิก Google Drive
    $googleConfig = include(__DIR__ . '/config/google_drive_config.php');

    foreach ($rows as $row) {
        $id = $row['id'];
        $fileName = $row['photo_path'];
        $filePath = __DIR__ . '/uploads/visitor/' . $fileName;

        if (file_exists($filePath)) {
            try {
                echo "Processing ID $id: $fileName ... ";
                
                // เริ่มอัพโหลด
                uploadToGoogleDrive($filePath, $fileName, $googleConfig['visitor_folder_id'], $googleConfig);
                
                // 2. อัพเดตสถานะในฐานข้อมูลเมื่อ Sync สำเร็จ
                $updateSql = "UPDATE ims_line_webhook_messages SET is_google_drive_synced = 1 WHERE id = :id";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->execute([':id' => $id]);
                
                echo "SUCCESS\n";
                file_put_contents($logPath, date('Y-m-d H:i:s') . " | SUCCESS | ID: $id | File: $fileName\n", FILE_APPEND);
            } catch (Exception $e) {
                echo "FAILED: " . $e->getMessage() . "\n";
                file_put_contents($logPath, date('Y-m-d H:i:s') . " | FAILED  | ID: $id | File: $fileName | Error: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        } else {
            echo "ID $id: File not found ($fileName) - Skipping\n";
            // มาร์คสถานะเป็น 9 (Error - File missing) เพื่อไม่ให้ดึงมาซ้ำอีก
            $conn->prepare("UPDATE ims_line_webhook_messages SET is_google_drive_synced = 9 WHERE id = :id")->execute([':id' => $id]);
            file_put_contents($logPath, date('Y-m-d H:i:s') . " | MISSING | ID: $id | File: $fileName\n", FILE_APPEND);
        }
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    file_put_contents($logPath, date('Y-m-d H:i:s') . " | DB ERROR | " . $e->getMessage() . "\n", FILE_APPEND);
}

echo "--- Sync Task Finished: " . date('Y-m-d H:i:s') . " ---\n";
?>
