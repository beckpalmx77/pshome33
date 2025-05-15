<?php
// ตั้งค่าเบื้องต้น
$accessToken = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU='; // ใส่ Channel Access Token จริง
$userId = 'Ubacff2b5c0b4f7352c801bfa99d2b34c'; // ใส่ LINE User ID จริง

// ตัวอย่าง Flex Message แสดงโควต้า
$flexMessage = [
    'type' => 'flex',
    'altText' => 'โควต้าการใช้งานของคุณ',
    'contents' => [
        'type' => 'bubble',
        'hero' => [
            'type' => 'image',
            'url' => 'https://ps33.themediathai.com/line_oa/booking.jpg', // เปลี่ยนเป็นลิงก์รูปจริง
            'size' => 'full',
            'aspectRatio' => '20:13',
            'aspectMode' => 'cover'
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'contents' => [
                [
                    'type' => 'text',
                    'text' => '📊 โควต้าการใช้งาน',
                    'weight' => 'bold',
                    'size' => 'xl',
                    'margin' => 'md'
                ],
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'contents' => [
                        ['type' => 'text', 'text' => 'ใช้ไป', 'flex' => 1],
                        ['type' => 'text', 'text' => '2 ครั้ง', 'flex' => 1, 'align' => 'end']
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'contents' => [
                        ['type' => 'text', 'text' => 'คงเหลือ', 'flex' => 1],
                        ['type' => 'text', 'text' => '1 ครั้ง', 'flex' => 1, 'align' => 'end']
                    ]
                ]
            ]
        ]
    ]
];

// ตัวอย่างข้อความธรรมดาเมื่อหมดโควต้า
$textMessage = [
    'type' => 'text',
    'text' => "❌ ขณะนี้คุณใช้โควต้าครบแล้ว (3/3 ครั้ง)\nกรุณารอรีเซ็ตสิทธิ์ในวันจันทร์หน้า 🙏"
];

// รวมข้อความที่ต้องการส่ง (เลือกส่ง flex, text หรือทั้งสอง)
$messageData = [$flexMessage, $textMessage];

$postData = [
    'to' => $userId,
    'messages' => $messageData
];

// ส่งข้อความผ่าน Messaging API
$ch = curl_init('https://api.line.me/v2/bot/message/push');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

$result = curl_exec($ch);
curl_close($ch);

echo $result;
?>
