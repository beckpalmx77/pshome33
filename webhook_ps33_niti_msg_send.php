<?php
include('config/connect_db.php');
// คำเตือน: ใช้ Token อันใหม่ที่กด Reissue มานะครับ
$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';
$group1_id = 'Cd6b5e1dfc01ac62b37a7f84e9a951ae2';
$group2_id = 'Ca579b4e8daae57c0f07c3508696074ae';
$baseUrl = 'https://ps33home.com/uploads/visitor';

$content = file_get_contents('php://input');
$events = json_decode($content, true);

if (isset($events['events']) && is_array($events['events'])) {
    foreach ($events['events'] as $event) {

        if ($event['type'] === 'message') {

            // ดึงค่า Group ID และ User ID ออกมา
            $sourceGroupId = isset($event['source']['groupId']) ? $event['source']['groupId'] : '';
            $sourceUserId = isset($event['source']['userId']) ? $event['source']['userId'] : 'ไม่พบ_User_ID_(ยังไม่ได้แอดบอท)';
            $messageType = $event['message']['type'];

            // ==========================================
            // ส่วนที่เพิ่มใหม่: บันทึกข้อมูลลงไฟล์
            // ==========================================
            $logPath = __DIR__ . '/webhook_ps33_niti_log_id.txt';
            $logData = "เวลา: " . date('Y-m-d H:i:s') . "\n";
            $logData .= "Group ID: " . $sourceGroupId . "\n";
            $logData .= "User ID: " . $sourceUserId . "\n";
            $logData .= "---------------------------\n";
            file_put_contents($logPath, $logData, FILE_APPEND); // FILE_APPEND คือการเขียนต่อท้ายไฟล์ไปเรื่อยๆ

            // ตรวจสอบว่าส่งมาจาก "กลุ่ม 1" หรือ "กลุ่ม 2"
            if ($sourceGroupId === $group1_id || $sourceGroupId === $group2_id) {

                $messagesToSend = [];
                $message_text = '';
                $photo_path = '';
                
                // ดึงข้อมูล Profile จาก LINE API
                $profile = getMemberProfile($sourceGroupId, $sourceUserId, $channelAccessToken);
                $display_name = isset($profile['displayName']) ? $profile['displayName'] : '';
                
                $f_name = '-';
                $l_name = '-';
                $house_number = '-';
                $phone = '-';

                // ==========================================
                // 1. กรณีข้อความตัวอักษร
                // ==========================================
                if ($messageType === 'text') {
                    $textMessage = $event['message']['text'];
                    $message_text = $textMessage;

                    // เตรียม Flex Message (เฉพาะกรณีส่งมาจาก กลุ่ม 1 เพื่อส่งไป กลุ่ม 2)
                    if ($sourceGroupId === $group1_id) {
                        $messagesToSend[] = [
                            'type' => 'flex',
                            'altText' => '💬 ',
                            'contents' => [
                                'type' => 'bubble',
                                'body' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        [
                                            'type' => 'text',
                                            'text' => '💬 ',
                                            'weight' => 'bold',
                                            'color' => '#1DB446',
                                            'size' => 'sm'
                                        ],
                                        [
                                            'type' => 'text',
                                            'text' => $textMessage,
                                            'wrap' => true,
                                            'margin' => 'md'
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    }
                }

                // ==========================================
                // 2. กรณีรูปภาพ
                // ==========================================
                elseif ($messageType === 'image') {
                    $messageId = $event['message']['id'];
                    $imageBinary = getMessageContent($messageId, $channelAccessToken);

                    $fileName = $messageId . '.jpg';
                    $savePath = __DIR__ . '/uploads/visitor/' . $fileName;
                    
                    if (!file_exists(__DIR__ . '/uploads/visitor/')) {
                        mkdir(__DIR__ . '/uploads/visitor/', 0777, true);
                    }
                    
                    file_put_contents($savePath, $imageBinary);

                    // --- Google Drive Upload Section ---
                    try {
                        include_once('util/google_drive_util.php');
                        $googleConfig = include('config/google_drive_config.php');
                        if (!empty($googleConfig['visitor_folder_id'])) {
                            uploadToGoogleDrive($savePath, $fileName, $googleConfig['visitor_folder_id'], $googleConfig);
                        }
                    } catch (Exception $e) {
                        error_log("Google Drive Visitor Upload Error: " . $e->getMessage());
                    }
                    // ------------------------------------

                    $imageUrl = $baseUrl . '/' . $fileName;
                    $message_text = 'รูปภาพ';
                    $photo_path = $fileName;

                    // เตรียม Flex Message (เฉพาะกรณีส่งมาจาก กลุ่ม 1 เพื่อส่งไป กลุ่ม 2)
                    if ($sourceGroupId === $group1_id) {
                        $messagesToSend[] = [
                            'type' => 'flex',
                            'altText' => '📷 ',
                            'contents' => [
                                'type' => 'bubble',
                                'hero' => [
                                    'type' => 'image',
                                    'url' => $imageUrl,
                                    'size' => 'full',
                                    'aspectRatio' => '1:1',
                                    'aspectMode' => 'fit',
                                    'action' => [
                                        'type' => 'uri',
                                        'uri' => $imageUrl
                                    ]
                                ],
                                'body' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        [
                                            'type' => 'text',
                                            'text' => '📷  ',
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
                }

                // บันทึกข้อมูลลงตาราง ims_line_webhook_messages (ทั้ง 2 กลุ่ม)
                if ($message_text !== '' || $photo_path !== '') {
                    $sql_insert = "INSERT INTO ims_line_webhook_messages 
                                   (line_user_id, line_display_name, first_name, last_name, house_number, phone, message_type, message_text, photo_path, group_id, status) 
                                   VALUES (:line_user_id, :line_display_name, :first_name, :last_name, :house_number, :phone, :message_type, :message_text, :photo_path, :group_id, 'N')";
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->bindParam(':line_user_id', $sourceUserId);
                    $stmt_insert->bindParam(':line_display_name', $display_name);
                    $stmt_insert->bindParam(':first_name', $f_name);
                    $stmt_insert->bindParam(':last_name', $l_name);
                    $stmt_insert->bindParam(':house_number', $house_number);
                    $stmt_insert->bindParam(':phone', $phone);
                    $stmt_insert->bindParam(':message_type', $messageType);
                    $stmt_insert->bindParam(':message_text', $message_text);
                    $stmt_insert->bindParam(':photo_path', $photo_path);
                    $stmt_insert->bindParam(':group_id', $sourceGroupId);
                    $stmt_insert->execute();
                }

                // ส่งข้อความจากกลุ่ม 1 ไปยังกลุ่ม 2
                if ($sourceGroupId === $group1_id && count($messagesToSend) > 0) {
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

function getMemberProfile($groupId, $userId, $token) {
    $url = "https://api.line.me/v2/bot/group/" . $groupId . "/member/" . $userId;
    $headers = ['Authorization: Bearer ' . $token];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}
?>