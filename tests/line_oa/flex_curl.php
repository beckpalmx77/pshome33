<?php
// กำหนด URL ของ LINE Messaging API
$url = 'https://api.line.me/v2/bot/message/push';

// กำหนด access token ของ LINE OA ของคุณ
$accessToken = 'IpR8udFWN6A9z5H+ZmMHSWnkM49C4+eJWmmaXlqwH01rYSkwHlPZMSN5cNekLldYqeMP2Vj0Ez3ZEbpXeSZyylPEa2sYD8bEIb0gDo/iaOVCtMFb0UE2Mz87K0zpiqkhfRNn9Icy/6PMhSfPgcLwAgdB04t89/1O/w1cDnyilFU=';

// กำหนด headers
$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken,
];

// กำหนดข้อมูล payload (JSON) ที่จะส่ง
$data = [
    'to' => 'Ud1fd8997daa1c3cec0782276245591b8',
    'messages' => [
        [
            'type' => 'flex',
            'altText' => 'This is a Flex Message',
            'contents' => [
                'type' => 'bubble',
                'body' => [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello,'
                        ],
                        [
                            'type' => 'text',
                            'text' => 'World!'
                        ]
                    ]
                ]
            ]
        ]
    ]
];

// แปลง array เป็น JSON string
$postData = json_encode($data);

// เริ่มต้น cURL session
$ch = curl_init();

// กำหนด options ของ cURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); // กำหนด method เป็น POST
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); // กำหนดข้อมูลที่จะส่ง
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // กำหนด headers
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // ให้ cURL คืนค่าผลลัพธ์เป็น string

// ส่ง request และรับผลลัพธ์
$response = curl_exec($ch);

// ตรวจสอบ error
if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    // แสดงผลลัพธ์ที่ได้รับ
    echo 'Response: ' . $response;
}

// ปิด cURL session
curl_close($ch);
