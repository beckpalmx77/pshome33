<?php

// รับข้อมูลดิบจาก LINE Webhook
$json_data = file_get_contents('php://input');

// แปลงข้อมูล JSON เป็น Array ของ PHP
$data = json_decode($json_data, true);

// ตรวจสอบว่ามีข้อมูลเหตุการณ์ (events) อยู่ใน JSON หรือไม่
if (isset($data['events'])) {
    foreach ($data['events'] as $event) {
        // ดึงค่า User ID
        $user_id = $event['source']['userId'];

        // ตรวจสอบชนิดของ Event
        $event_type = $event['type'];

        // ตัวอย่างการบันทึก User ID ลงในไฟล์
        // คุณสามารถเปลี่ยนไปบันทึกในฐานข้อมูล (MySQL, etc.) แทนได้
        $log_file = 'user_ids.txt';
        $log_entry = "User ID: {$user_id} | Event Type: {$event_type} | Timestamp: " . date('Y-m-d H:i:s') . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);

        // แสดงผลใน Terminal หรือ Log
        echo "Received User ID: {$user_id}\n";

        // (ไม่บังคับ) หากต้องการตอบกลับข้อความผู้ใช้
        if ($event_type === 'message') {
            $reply_token = $event['replyToken'];
            $reply_message = "สวัสดีครับ! ขอบคุณที่ส่งข้อความมา ผมได้รับ User ID ของคุณแล้ว: {$user_id}";
            reply_message($reply_token, $reply_message);
        }
    }
}

// ฟังก์ชันสำหรับตอบกลับข้อความ (ต้องใช้ Channel Access Token)
function reply_message($reply_token, $message) {
    // แทนที่ด้วย Channel Access Token ของคุณ
    $channel_access_token = 'your_channel_access_token';

    $url = 'https://api.line.me/v2/bot/message/reply';
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $channel_access_token,
    ];

    $body = json_encode([
        'replyToken' => $reply_token,
        'messages' => [
            [
                'type' => 'text',
                'text' => $message,
            ],
        ],
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}