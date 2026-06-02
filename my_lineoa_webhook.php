<?php
/**
 * LINE Messaging API Webhook
 * Channel ID: 2007422545
 * Channel Secret: 84f938d804782c41e9ed3f9a74e950d3
 */

// 1. ตั้งค่าพื้นฐาน
$channelSecret = '84f938d804782c41e9ed3f9a74e950d3';
// หมายเหตุ: สำหรับการตอบกลับ (Reply) หรือส่งข้อความ (Push) จำเป็นต้องใช้ Channel Access Token
// ซึ่งสามารถคัดลอกได้จาก LINE Developers Console ในแถบ Messaging API
$channelAccessToken = 'IpR8udFWN6A9z5H+ZmMHSWnkM49C4+eJWmmaXlqwH01rYSkwHlPZMSN5cNekLldYqeMP2Vj0Ez3ZEbpXeSZyylPEa2sYD8bEIb0gDo/iaOVCtMFb0UE2Mz87K0zpiqkhfRNn9Icy/6PMhSfPgcLwAgdB04t89/1O/w1cDnyilFU=';

// 2. รับข้อมูล Webhook Payload จาก LINE
$content = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? '';

// 3. ตรวจสอบความถูกต้องของ Signature (Security Best Practice)
$hash = hash_hmac('sha256', $content, $channelSecret, true);
$base64Signature = base64_encode($hash);

if ($signature !== $base64Signature) {
    http_response_code(400);
    exit("Invalid signature");
}

$events = json_decode($content, true);

// 4. ประมวลผลแต่ละ Event
if (isset($events['events']) && is_array($events['events'])) {
    foreach ($events['events'] as $event) {
        
        $type = $event['type'];
        $userId = $event['source']['userId'] ?? 'Unknown';
        $replyToken = $event['replyToken'] ?? null;

        // บันทึก Log ข้อมูลเบื้องต้น (ตามรูปแบบของโปรเจกต์)
        $logPath = __DIR__ . '/webhook_log.txt';
        $logData = "เวลา: " . date('Y-m-d H:i:s') . "\n";
        $logData .= "User ID: " . $userId . "\n";
        $logData .= "Event Type: " . $type . "\n";
        
        if ($type === 'message') {
            $messageType = $event['message']['type'];
            $logData .= "Message Type: " . $messageType . "\n";
            
            if ($messageType === 'text') {
                $text = $event['message']['text'];
                $logData .= "Text: " . $text . "\n";

                // ตัวอย่างการตอบกลับข้อความแบบง่าย
                /*
                if ($replyToken) {
                    replyMessage($replyToken, "คุณส่งข้อความว่า: " . $text, $channelAccessToken);
                }
                */
            }
        }
        
        $logData .= "---------------------------\n";
        file_put_contents($logPath, $logData, FILE_APPEND);
    }
}

// 5. ตอบกลับ LINE Server ว่าได้รับข้อมูลแล้ว
http_response_code(200);
echo "OK";

/**
 * ฟังก์ชันสำหรับตอบกลับข้อความ
 */
function replyMessage($replyToken, $text, $token) {
    if ($token === 'YOUR_CHANNEL_ACCESS_TOKEN') return;

    $url = 'https://api.line.me/v2/bot/message/reply';
    $data = [
        'replyToken' => $replyToken,
        'messages' => [
            [
                'type' => 'text',
                'text' => $text
            ]
        ]
    ];
    $post = json_encode($data);
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}
