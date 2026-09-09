<?php
// รับข้อมูล raw payload จาก LINE
$content = file_get_contents('php://input');
$events = json_decode($content, true);

// ตรวจสอบว่ามี events ส่งมาหรือไม่
if (!empty($events['events'])) {
    foreach ($events['events'] as $event) {
        // เช็คว่า event เกิดขึ้นใน group หรือไม่
        if (isset($event['source']['type']) && $event['source']['type'] === 'group') {
            $groupId = $event['source']['groupId'];

            // 1. บันทึก groupId ลงไฟล์ txt เพื่อดูค่า
            file_put_contents('webhook_group_id.txt', "Group ID: " . $groupId . PHP_EOL, FILE_APPEND);

            // 2. (ทางเลือก) ให้บอทตอบกลับ groupId ทันทีเมื่อมีคนพิมพ์คำว่า "get-id" หรือเมื่อบอทเพิ่งเข้ากลุ่ม
            if ($event['type'] === 'join' || ($event['type'] === 'message' && $event['message']['text'] === 'get-id')) {
                $replyToken = $event['replyToken'];
                $channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';

                $messageData = [
                    'replyToken' => $replyToken,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => "Group ID ของกลุ่มนี้คือ:\n" . $groupId
                        ]
                    ]
                ];

                $ch = curl_init('https://api.line.me/v2/bot/message/reply');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json; charsets=UTF-8',
                    'Authorization: Bearer ' . $channelAccessToken
                ]);
                curl_exec($ch);
                curl_close($ch);
            }
        }
    }
}

// ส่งสถานะ 200 กลับไปให้ LINE server ทราบว่ารับข้อมูลแล้ว
http_response_code(200);
echo "OK";