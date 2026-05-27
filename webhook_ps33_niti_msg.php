<?php
// คำเตือน: อย่าลืมลบ Token เก่าใน LINE Developers และกด Reissue ใหม่นะครับ เพราะ Token นี้หลุดเข้าสู่สาธารณะแล้ว
$channelAccessToken = 'ใส่_TOKEN_ที่กด_REISSUE_มาใหม่ที่นี่';
$group1_id = 'Cd6b5e1dfc01ac62b37a7f84e9a951ae2';
$group2_id = 'Ca579b4e8daae57c0f07c3508696074ae';

// ตรวจสอบให้แน่ใจว่าไฟล์นี้อยู่ในโฟลเดอร์เดียวกันกับโฟลเดอร์ visitor นะครับ
$baseUrl = 'https://ps33home.com/uploads';

// 1. รับข้อมูล Webhook
$content = file_get_contents('php://input');
$events = json_decode($content, true);

if (isset($events['events']) && is_array($events['events'])) {
    foreach ($events['events'] as $event) {

        if ($event['type'] === 'message') {

            $sourceGroupId = isset($event['source']['groupId']) ? $event['source']['groupId'] : '';
            $messageType = $event['message']['type'];

            // 2. ตรวจสอบกลุ่มต้นทาง
            if ($sourceGroupId === $group1_id) {

                $messagesToSend = [];

                // --- กรณีข้อความตัวอักษร ---
                if ($messageType === 'text') {
                    $textMessage = $event['message']['text'];

                    $messagesToSend[] = [
                        'type' => 'text',
                        'text' => $textMessage // ส่งแค่ข้อความล้วนๆ ไม่มีคำนำหน้า
                    ];
                }

                // --- กรณีรูปภาพ ---
                elseif ($messageType === 'image') {
                    $messageId = $event['message']['id'];
                    $imageBinary = getMessageContent($messageId, $channelAccessToken);

                    // บังคับเซฟเป็น .jpg ซึ่งปลอดภัยและรองรับโดย LINE API
                    $fileName = $messageId . '.jpg';
                    $savePath = __DIR__ . '/visitor/' . $fileName;
                    file_put_contents($savePath, $imageBinary);

                    $imageUrl = $baseUrl . '/visitor/' . $fileName;

                    // ใส่แค่ image object อย่างเดียว (ลบ text object ที่ว่างเปล่าออกแล้ว)
                    $messagesToSend[] = [
                        'type' => 'image',
                        'originalContentUrl' => $imageUrl,
                        'previewImageUrl' => $imageUrl
                    ];
                }

                // 3. ส่งข้อความไปยังกลุ่ม 2
                if (count($messagesToSend) > 0) {
                    pushMessage($group2_id, $messagesToSend, $channelAccessToken);
                }
            }
        }
    }
}

http_response_code(200);
echo "OK";

// =========================================================================
// ฟังก์ชันย่อย
// =========================================================================

function getMessageContent($messageId, $token) {
    $url = "https://api-data.line.me/v2/bot/message/" . $messageId . "/content";
    $headers = ['Authorization: Bearer ' . $token];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function pushMessage($to_id, $messages_array, $token) {
    $url = 'https://api.line.me/v2/bot/message/push';
    $data = [
        'to' => $to_id,
        'messages' => $messages_array
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
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}
?>