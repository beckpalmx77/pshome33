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

/**
 * ป้องกันการ Upload ซ้ำกรณี Cronjob รันซ้อนกัน (Race Condition)
 * เราจะใช้สถานะดังนี้:
 * 0 = ยังไม่ได้ส่ง (Pending)
 * 1 = ส่งสำเร็จแล้ว (Synced)
 * 2 = กำลังประมวลผล (Processing)
 * 9 = เกิดข้อผิดพลาด/ไม่พบไฟล์ (Error)
 */

try {
    // 1. จองคิว: เปลี่ยนสถานะจาก 0 เป็น 2 สำหรับ 10 แถวแรก
    // เราใช้ Batch ID (เวลาปัจจุบัน) เพื่อระบุเฉพาะแถวที่โปรเซสนี้จองไว้
    $batchId = time() . rand(100, 999);
    $lockSql = "UPDATE ims_line_webhook_messages 
                SET is_google_drive_synced = 2, remark = :batchId
                WHERE is_google_drive_synced = 0 
                AND message_type = 'image'
                AND photo_path IS NOT NULL 
                AND photo_path != ''
                ORDER BY id ASC LIMIT 10";
    
    // หมายเหตุ: หากตารางไม่มีคอลัมน์ remark ให้รัน: ALTER TABLE ims_line_webhook_messages ADD remark VARCHAR(100);
    // หรือถ้าไม่ต้องการใช้ remark ให้ใช้แค่การจองสถานะ 2 ก็ได้ครับ
    $lockStmt = $conn->prepare($lockSql);
    $lockStmt->execute([':batchId' => $batchId]);

    // 2. ดึงรายการที่เราจองไว้ (สถานะ = 2 และ remark = batchId ของเรา)
    $sql = "SELECT id, photo_path FROM ims_line_webhook_messages 
            WHERE is_google_drive_synced = 2 AND remark = :batchId";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':batchId' => $batchId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) === 0) {
        echo "No pending images to sync in this batch.\n";
        exit;
    }

    $googleConfig = include(__DIR__ . '/config/google_drive_config.php');

    foreach ($rows as $row) {
        $id = $row['id'];
        $fileName = $row['photo_path'];
        $filePath = __DIR__ . '/uploads/visitor/' . $fileName;

        if (file_exists($filePath)) {
            try {
                echo "Processing ID $id: $fileName ... ";
                uploadToGoogleDrive($filePath, $fileName, $googleConfig['visitor_folder_id'], $googleConfig);
                
                // 3. อัพเดตสถานะเป็น 1 (Synced) เมื่อสำเร็จ
                $updateSql = "UPDATE ims_line_webhook_messages SET is_google_drive_synced = 1, remark = 'Synced at " . date('H:i:s') . "' WHERE id = :id";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->execute([':id' => $id]);
                
                echo "SUCCESS\n";
                file_put_contents($logPath, date('Y-m-d H:i:s') . " | SUCCESS | ID: $id | File: $fileName\n", FILE_APPEND);
            } catch (Exception $e) {
                echo "FAILED: " . $e->getMessage() . "\n";
                // หากพลาด ให้ตีกลับไปเป็น 0 เพื่อให้รอบหน้าลองใหม่ หรือเป็น 9 ถ้า Error ถาวร
                $conn->prepare("UPDATE ims_line_webhook_messages SET is_google_drive_synced = 0, remark = :err WHERE id = :id")
                     ->execute([':id' => $id, ':err' => substr($e->getMessage(), 0, 99)]);
                file_put_contents($logPath, date('Y-m-d H:i:s') . " | FAILED  | ID: $id | File: $fileName | Error: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        } else {
            echo "ID $id: File not found ($fileName) - Marking Error\n";
            $conn->prepare("UPDATE ims_line_webhook_messages SET is_google_drive_synced = 9, remark = 'File not found' WHERE id = :id")->execute([':id' => $id]);
            file_put_contents($logPath, date('Y-m-d H:i:s') . " | MISSING | ID: $id | File: $fileName\n", FILE_APPEND);
        }
    }

} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
    file_put_contents($logPath, date('Y-m-d H:i:s') . " | SYSTEM ERROR | " . $e->getMessage() . "\n", FILE_APPEND);
}

// --- Cleanup Old Images (Older than 20 days) ---
echo "--- Starting Cleanup Task (Older than 20 days) ---\n";
try {
    $cleanupSql = "SELECT id, photo_path FROM ims_line_webhook_messages 
                   WHERE created_at < DATE_SUB(NOW(), INTERVAL 20 DAY) 
                   AND message_type = 'image' 
                   AND photo_path IS NOT NULL 
                   AND photo_path != ''";
    $cleanupStmt = $conn->prepare($cleanupSql);
    $cleanupStmt->execute();
    $cleanupRows = $cleanupStmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($cleanupRows) > 0) {
        $googleConfig = include(__DIR__ . '/config/google_drive_config.php');
        $folderId = $googleConfig['visitor_folder_id'];

        foreach ($cleanupRows as $row) {
            $id = $row['id'];
            $fileName = $row['photo_path'];
            $filePath = __DIR__ . '/uploads/visitor/' . $fileName;

            echo "Cleaning up ID $id: $fileName ... ";

            // 1. Delete Local File
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // 2. Delete from Google Drive
            deleteFromGoogleDriveByName($fileName, $folderId, $googleConfig);

            // 3. Delete Record from DB
            $delSql = "DELETE FROM ims_line_webhook_messages WHERE id = :id";
            $delStmt = $conn->prepare($delSql);
            $delStmt->execute([':id' => $id]);

            echo "DELETED\n";
            file_put_contents($logPath, date('Y-m-d H:i:s') . " | CLEANUP | ID: $id | File: $fileName deleted globally.\n", FILE_APPEND);
        }
    } else {
        echo "No old images to clean up.\n";
    }
} catch (Exception $e) {
    echo "Cleanup Error: " . $e->getMessage() . "\n";
    file_put_contents($logPath, date('Y-m-d H:i:s') . " | CLEANUP ERROR | " . $e->getMessage() . "\n", FILE_APPEND);
}

echo "--- Sync Task Finished: " . date('Y-m-d H:i:s') . " ---\n";
?>
