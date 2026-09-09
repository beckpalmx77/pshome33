<?php
// รับข้อมูล raw payload จาก LINE
$content = file_get_contents('php://input');
$events = json_decode($content, true);

$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';

// ตรวจสอบว่ามี events ส่งมาหรือไม่
if (!empty($events['events'])) {
    foreach ($events['events'] as $event) {
        // เช็คว่า event เกิดขึ้นใน group หรือไม่
        if (isset($event['source']['type']) && $event['source']['type'] === 'group') {
            $groupId = $event['source']['groupId'];

            // ดึงชื่อกลุ่มผ่าน Group Summary API
            $groupSummary = getGroupSummary($groupId, $channelAccessToken);
            $groupName = !empty($groupSummary['groupName']) ? $groupSummary['groupName'] : 'ไม่ทราบชื่อกลุ่ม';

            // 1. บันทึกชื่อกลุ่มและ groupId ลงไฟล์ txt
            $logText = "Group Name: " . $groupName . " | Group ID: " . $groupId . PHP_EOL;
            file_put_contents('webhook_group_id.txt', $logText, FILE_APPEND);

            // 2. ให้บอทตอบกลับทันทีเมื่อมีคนพิมพ์คำว่า "get-id" หรือเมื่อบอทเพิ่งเข้ากลุ่ม (join)
            if ($event['type'] === 'join' || ($event['type'] === 'message' && isset($event['message']['text']) && $event['message']['text'] === 'get-id')) {
                $replyToken = $event['replyToken'];

                $replyMessage = "ชื่อกลุ่ม: " . $groupName . "\nGroup ID: " . $groupId;

                $messageData = [
                    'replyToken' => $replyToken,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $replyMessage
                        ]
                    ]
                ];

                $ch = curl_init('https://api.line.me/v2/bot/message/reply');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
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

// ฟังก์ชันสำหรับเรียกดู Group Summary (ชื่อกลุ่ม, รูปโปรไฟล์กลุ่ม)
function getGroupSummary($groupId, $token) {
    $ch = curl_init("https://api.line.me/v2/bot/group/{$groupId}/summary");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$token}"
    ]);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200) ? json_decode($res, true) : null;
}