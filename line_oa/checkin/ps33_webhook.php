<?php
// webhook.php

// รับ JSON จาก LINE
$input = file_get_contents('php://input');
$events = json_decode($input, true);

// Log หรือ Debug
file_put_contents('log.txt', $input . PHP_EOL, FILE_APPEND);

if (!empty($events['events'])) {
    foreach ($events['events'] as $event) {
        if ($event['source']['type'] === 'group') {
            $groupId = $event['source']['groupId'];

            // บันทึก groupId ลงไฟล์หรือฐานข้อมูล
            file_put_contents('group_id.txt', $groupId);

            // ส่งข้อความกลับเพื่อยืนยันว่าได้ groupId แล้ว
            $replyToken = $event['replyToken'];
            //replyMessage($replyToken, "บอทได้รับ Group ID แล้ว ✅");
        }
    }
}

function replyMessage($replyToken, $text) {
    $accessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';

    $url = 'https://api.line.me/v2/bot/message/reply';
    $data = [
        'replyToken' => $replyToken,
        'messages' => [['type' => 'text', 'text' => $text]],
    ];

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($data),
    ]);

    curl_exec($ch);
    curl_close($ch);
}

