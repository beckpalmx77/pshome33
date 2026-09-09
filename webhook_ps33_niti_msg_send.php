<?php
include('config/connect_db.php');
include_once('util/google_drive_util.php');

// ตั้งค่า Header ตอบกลับทันทีเมื่อ LINE ยิง Webhook เข้ามา
http_response_code(200);

// คอนฟิกหลัก
$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';

$group1_id = 'Cd6b5e1dfc01ac62b37a7f84e9a951ae2';
$group2_id = 'Ca579b4e8daae57c0f07c3508696074ae';
$group3_id = 'Cc7e171f9d97eda438e57c493e26b891b';

$allowedGroups = [$group1_id, $group2_id, $group3_id];
$baseUrl = 'https://ps33home.com/uploads/visitor';

$content = file_get_contents('php://input');
$events = json_decode($content, true);

if (isset($events['events']) && is_array($events['events'])) {
    foreach ($events['events'] as $event) {
        if ($event['type'] === 'message') {
            $messageId = $event['message']['id'];
            $sourceGroupId = isset($event['source']['groupId']) ? $event['source']['groupId'] : '';
            $sourceUserId = isset($event['source']['userId']) ? $event['source']['userId'] : 'Unknown';
            $messageType = $event['message']['type'];

            // ตรวจสอบว่าข้อความมาจากกลุ่มที่กำหนดไว้หรือไม่ ถ้าไม่ใช่ให้ข้ามไปทันที
            if (!in_array($sourceGroupId, $allowedGroups, true)) {
                continue;
            }

            // 1. ป้องกันการประมวลผลซ้ำ (Atomic Insert) ด้วย UNIQUE constraint ของ line_message_id
            try {
                $sql_lock = "INSERT INTO ims_line_webhook_messages (line_message_id, status) VALUES (:mid, 'P')";
                $stmt_lock = $conn->prepare($sql_lock);
                $stmt_lock->execute([':mid' => $messageId]);
            } catch (Exception $e) {
                // หากติด Duplicate Key แสดงว่า Webhook นี้กำลังทำงานหรือทำงานเสร็จไปแล้ว
                continue;
            }

            // ดึงชื่อผู้ใช้จากกลุ่ม
            $profile = getMemberProfile($sourceGroupId, $sourceUserId, $channelAccessToken);
            $displayName = (!empty($profile['displayName'])) ? $profile['displayName'] : 'ผู้ใช้งาน';

            $db_text = '';
            $db_photo = '';
            $messagesToSend = [];

            // จัดการข้อความประเภท Text
            if ($messageType === 'text') {
                $db_text = $event['message']['text'];
                $messagesToSend[] = [
                    'type' => 'text',
                    'text' => "💬 [{$displayName}]:\n{$db_text}"
                ];
            }
            // จัดการข้อความประเภท Image
            elseif ($messageType === 'image') {
                $imageBinary = getMessageContent($messageId, $channelAccessToken);

                if ($imageBinary !== false) {
                    $fileName = $messageId . '.jpg';
                    $uploadDir = __DIR__ . '/uploads/visitor/';

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    file_put_contents($uploadDir . $fileName, $imageBinary);

                    /* ปิดชั่วคราว: อัพโหลด Google Drive
                    try {
                        $googleConfig = include('config/google_drive_config.php');
                        if (!empty($googleConfig['visitor_folder_id'])) {
                            uploadToGoogleDrive($uploadDir . $fileName, $fileName, $googleConfig['visitor_folder_id'], $googleConfig);
                        }
                    } catch (Exception $e) {}
                    */

                    $imageUrl = $baseUrl . '/' . $fileName;
                    $db_text = 'ส่งรูปภาพ';
                    $db_photo = $fileName;

                    $messagesToSend[] = [
                        'type' => 'text',
                        'text' => "📷 [{$displayName}] ส่งรูปภาพ:"
                    ];
                    $messagesToSend[] = [
                        'type' => 'image',
                        'originalContentUrl' => $imageUrl,
                        'previewImageUrl' => $imageUrl
                    ];
                }
            }

            // 2. อัปเดตรายละเอียดข้อความลงตาราง
            $sql_update = "UPDATE ims_line_webhook_messages SET 
                           line_user_id = :uid, 
                           line_display_name = :name, 
                           message_type = :mtype, 
                           message_text = :mtext, 
                           photo_path = :photo, 
                           group_id = :gid, 
                           status = 'N' 
                           WHERE line_message_id = :mid";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([
                ':uid'   => $sourceUserId,
                ':name'  => $displayName,
                ':mtype' => $messageType,
                ':mtext' => $db_text,
                ':photo' => $db_photo,
                ':gid'   => $sourceGroupId,
                ':mid'   => $messageId
            ]);

            // 3. ส่งต่อข้อความไปยังกลุ่มอื่นๆ ที่ไม่ใช่กลุ่มต้นทาง
            if (!empty($messagesToSend)) {
                $targetGroups = array_diff($allowedGroups, [$sourceGroupId]);
                foreach ($targetGroups as $target) {
                    pushMessage($target, $messagesToSend, $channelAccessToken);
                }
            }
        }
    }
}

echo "OK";

// ฟังก์ชันดาวน์โหลดไบนารีรูปภาพจาก LINE Content Server
function getMessageContent($mid, $token) {
    $ch = curl_init("https://api-data.line.me/v2/bot/message/{$mid}/content");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$token}"
    ]);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200) ? $res : false;
}

// ฟังก์ชันส่งข้อความแบบ Push Message ไปยังห้องแชท/กลุ่มเป้าหมาย
function pushMessage($to, $msg, $token) {
    $ch = curl_init('https://api.line.me/v2/bot/message/push');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'to' => $to,
        'messages' => $msg
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Authorization: Bearer {$token}"
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

// ฟังก์ชันดึงข้อมูลโปรไฟล์ของสมาชิกในกลุ่ม
function getMemberProfile($gid, $uid, $token) {
    if (empty($uid) || $uid === 'Unknown') {
        return null;
    }
    $ch = curl_init("https://api.line.me/v2/bot/group/{$gid}/member/{$uid}");
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