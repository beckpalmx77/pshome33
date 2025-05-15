<?php
$access_token = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU='; // วาง token ของคุณที่นี่

// รับข้อมูลจาก LINE
$input = file_get_contents("php://input");
file_put_contents("line_webhook_log.txt", $input . PHP_EOL, FILE_APPEND); // log เก็บไว้ดู

$event = json_decode($input, true);

// ตรวจสอบว่าเป็นข้อความ
if (isset($event['events'][0]['type']) && $event['events'][0]['type'] == 'message') {
    $replyToken = $event['events'][0]['replyToken'];
    $msgText = $event['events'][0]['message']['text'];

    // เตรียมข้อความตอบกลับ
    $replyData = [
        'replyToken' => $replyToken,
        'messages' => [
            ['type' => 'text', 'text' => "คุณพิมพ์ว่า: $msgText"]
        ]
    ];

    // ส่งข้อความกลับไป
    $ch = curl_init('https://api.line.me/v2/bot/message/reply');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($replyData));
    curl_exec($ch);
    curl_close($ch);
}

http_response_code(200);
