<?php
$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';
$group1_id = 'Cd6b5e1dfc01ac62b37a7f84e9a951ae2';
$group2_id = 'Ca579b4e8daae57c0f07c3508696074ae';
$baseUrl = 'https://ps33home.com/webhook_bot_folder'; // URL หลักที่เก็บไฟล์นี้ (สำหรับรูปภาพ)

// 1. รับข้อมูล Webhook
$content = file_get_contents('php://input');
$events = json_decode($content, true);

// แก้ไขบรรทัดนี้เพื่อป้องกัน Error 500
if (isset($events['events']) && is_array($events['events'])) {
    foreach ($events['events'] as $event) {

        if ($event['type'] === 'message') {

            // ดึงแค่ Group ID ออกมา
            $sourceGroupId = isset($event['source']['groupId']) ? $event['source']['groupId'] : '';
            $messageType = $event['message']['type'];

            // 2. ตรวจสอบแค่ว่า ส่งมาจาก "กลุ่ม 1" ใช่หรือไม่
            if ($sourceGroupId === $group1_id) {

                $messagesToSend = [];

                // --- กรณีข้อความตัวอักษร ---
                if ($messageType === 'text') {
                    $textMessage = $event['message']['text'];
                    $formattedMessage = "💬 ข้อความจากกลุ่ม 1:\n" . $textMessage;

                    $messagesToSend[] = [
                        'type' => 'text',
                        'text' => $formattedMessage
                    ];
                }

                // --- กรณีรูปภาพ ---
                elseif ($messageType === 'image') {
                    $messageId = $event['message']['id'];
                    $imageBinary = getMessageContent($messageId, $channelAccessToken);

                    $fileName = $messageId . '.jpg';
                    $savePath = __DIR__ . '/uploads/' . $fileName;
                    file_put_contents($savePath, $imageBinary);

                    $imageUrl = $baseUrl . '/uploads/' . $fileName;

                    $messagesToSend[] = [
                        'type' => 'text',
                        'text' => "📷 มีรูปภาพส่งมาจากกลุ่ม 1:"
                    ];
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