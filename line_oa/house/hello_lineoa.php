<?php
// LINE Access Token (ของคุณ)
$accessToken = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

// รับข้อมูล JSON ที่ LINE ส่งมา
$input = file_get_contents('php://input');
file_put_contents('line_log.txt', $input . PHP_EOL, FILE_APPEND); // บันทึก log

// แปลง JSON เป็น array
$events = json_decode($input, true);

// ตรวจสอบว่ามี event จากผู้ใช้
if (!empty($events['events'])) {
    foreach ($events['events'] as $event) {

        // เช็คว่าเป็นข้อความ และเป็นข้อความตัวอักษร
        if ($event['type'] === 'message' && $event['message']['type'] === 'text') {
            $replyToken = $event['replyToken'];
            $userMessage = $event['message']['text'];

            // ข้อความตอบกลับ (สามารถปรับให้เป็น logic เงื่อนไขได้)
            $replyText = "คุณพิมพ์ว่า: " . $userMessage;

            // เตรียมข้อมูลสำหรับส่งกลับ
            $data = [
                'replyToken' => $replyToken,
                'messages' => [[
                    'type' => 'text',
                    'text' => $replyText
                ]]
            ];

            // ส่งกลับไปยัง LINE Platform
            $ch = curl_init('https://api.line.me/v2/bot/message/reply');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $result = curl_exec($ch);
            curl_close($ch);

            // บันทึก log การตอบกลับ
            file_put_contents('line_reply_log.txt', $result . PHP_EOL, FILE_APPEND);
        }
    }
}

// สำคัญ! ต้องส่ง HTTP 200 กลับไปให้ LINE
http_response_code(200);
echo "OK";
