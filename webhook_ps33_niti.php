<?php
// 1. รับข้อมูล Webhook Payload จาก LINE
$content = file_get_contents('php://input');
$events = json_decode($content, true);

// 2. ตรวจสอบว่ามีข้อมูลส่งมาหรือไม่
if (!is_null($events['events'])) {
    foreach ($events['events'] as $event) {

        // ดึงค่าต่างๆ ออกมา
        $type = $event['type'];
        $sourceType = isset($event['source']['type']) ? $event['source']['type'] : 'unknown';
        $userId = isset($event['source']['userId']) ? $event['source']['userId'] : 'ไม่พบ User ID';
        $groupId = isset($event['source']['groupId']) ? $event['source']['groupId'] : 'ไม่พบ Group ID';

        // 3. จัดรูปแบบข้อความที่จะบันทึกลงไฟล์
        $log_text = "เวลา: " . date('Y-m-d H:i:s') . "\n";
        $log_text .= "ประเภท Event: " . $type . "\n";
        $log_text .= "มาจาก (Source): " . $sourceType . "\n";
        $log_text .= "--- ไอดีที่คุณต้องการ ---\n";
        $log_text .= "User ID: " . $userId . "\n";
        $log_text .= "Group ID: " . $groupId . "\n";
        $log_text .= "-----------------------\n\n";

        // 4. บันทึกข้อมูลลงไฟล์ชื่อ log_id.txt
        // (ตรวจสอบให้แน่ใจว่าโฟลเดอร์นี้อนุญาตให้เขียนไฟล์ได้ / Permission 777)
        file_put_contents(__DIR__ . '/webhook_ps33_niti_log_id.txt', $log_text, FILE_APPEND);
    }
}

// ตอบกลับ LINE Server
http_response_code(200);
echo "OK";
?>