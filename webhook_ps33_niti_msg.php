<?php
// คำเตือน: อย่าลืมใช้ Token อันใหม่ที่กด Reissue มานะครับ!
$channelAccessToken = 'ใส่_TOKEN_ที่กด_REISSUE_มาใหม่ที่นี่';
$group1_id = 'Cd6b5e1dfc01ac62b37a7f84e9a951ae2';
$group2_id = 'Ca579b4e8daae57c0f07c3508696074ae';
$baseUrl = 'https://ps33home.com/uploads';

$content = file_get_contents('php://input');
$events = json_decode($content, true);

if (isset($events['events']) && is_array($events['events'])) {
    foreach ($events['events'] as $event) {

        if ($event['type'] === 'message') {

            $sourceGroupId = isset($event['source']['groupId']) ? $event['source']['groupId'] : '';
            $messageType = $event['message']['type'];

            if ($sourceGroupId === $group1_id) {

                $messagesToSend = [];

                // ==========================================
                // 1. กรณีข้อความตัวอักษร (Text Flex Message)
                // ==========================================
                if ($messageType === 'text') {
                    $textMessage = $event['message']['text'];

                    $messagesToSend[] = [
                        'type' => 'flex',
                        'altText' => 'มีข้อความใหม่จากกลุ่ม 1', // ข้อความแจ้งเตือนที่ขึ้นหน้า Lock Screen
                        'contents' => [
                            'type' => 'bubble',
                            'body' => [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => '💬 ข้อความจากกลุ่ม 1',
                                        'weight' => 'bold',
                                        'color' => '#1DB446', // สีเขียว LINE
                                        'size' => 'sm'
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $textMessage,
                                        'wrap' => true, // สั่งให้ตัดขึ้นบรรทัดใหม่ถ้ายาวเกิน
                                        'margin' => 'md' // ระยะห่างด้านบน
                                    ]
                                ]
                            ]
                        ]
                    ];
                }

                // ==========================================
                // 2. กรณีรูปภาพ (Image Flex Message)
                // ==========================================
                elseif ($messageType === 'image') {
                    $messageId = $event['message']['id'];
                    $imageBinary = getMessageContent($messageId, $channelAccessToken);

                    $fileName = $messageId . '.jpg';
                    $savePath = __DIR__ . '/visitor/' . $fileName;
                    file_put_contents($savePath, $imageBinary);

                    $imageUrl = $baseUrl . '/visitor/' . $fileName;

                    $messagesToSend[] = [
                        'type' => 'flex',
                        'altText' => 'มีรูปภาพใหม่จากกลุ่ม 1',
                        'contents' => [
                            'type' => 'bubble',
                            'hero' => [
                                'type' => 'image',
                                'url' => $imageUrl,
                                'size' => 'full',
                                'aspectRatio' => '1:1',
                                'aspectMode' => 'fit', // fit = แสดงรูปเต็มใบไม่ถูกครอป, cover = ครอปรูปให้เต็มกรอบ
                                'action' => [
                                    'type' => 'uri',
                                    'uri' => $imageUrl // คลิกที่รูปแล้วเปิดดูรูปขยายใหญ่ได้
                                ]
                            ],
                            'body' => [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => '📷 รูปภาพจากกลุ่ม 1',
                                        'weight' => 'bold',
                                        'color' => '#1DB446',
                                        'size' => 'sm',
                                        'align' => 'center'
                                    ]
                                ]
                            ]
                        ]
                    ];
                }

                // ส่งข้อความไปยังกลุ่ม 2
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