<?php
include('config/connect_db.php');
include_once('util/google_drive_util.php');

// คอนฟิกหลัก
$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';
$group1_id = 'Cd6b5e1dfc01ac62b37a7f84e9a951ae2';
$group2_id = 'Ca579b4e8daae57c0f07c3508696074ae';
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

            // 1. พยายามจองคิวบันทึกทันที (Atomic Insert) เพื่อป้องกัน Webhook ซ้ำ
            // หมายเหตุ: ต้องรัน SQL: ALTER TABLE ims_line_webhook_messages ADD line_message_id VARCHAR(50) UNIQUE;
            try {
                $sql_lock = "INSERT INTO ims_line_webhook_messages (line_message_id, status) VALUES (:mid, 'P')";
                $stmt_lock = $conn->prepare($sql_lock);
                $stmt_lock->execute([':mid' => $messageId]);
            } catch (Exception $e) {
                // ถ้า insert ไม่เข้า แสดงว่าเป็น webhook ซ้ำที่กำลังประมวลผลอยู่ หรือประมวลผลเสร็จแล้ว
                continue; 
            }

            if ($sourceGroupId === $group1_id || $sourceGroupId === $group2_id) {
                $profile = getMemberProfile($sourceGroupId, $sourceUserId, $channelAccessToken);
                $displayName = isset($profile['displayName']) ? $profile['displayName'] : 'ผู้ใช้งาน';
                
                $db_text = '';
                $db_photo = '';
                $messagesToSend = [];

                if ($messageType === 'text') {
                    $db_text = $event['message']['text'];
                    $messagesToSend[] = ['type' => 'text', 'text' => "💬 [$displayName]:\n$db_text"];
                } 
                elseif ($messageType === 'image') {
                    $imageBinary = getMessageContent($messageId, $channelAccessToken);
                    $fileName = $messageId . '.jpg';
                    $savePath = __DIR__ . '/uploads/visitor/' . $fileName;

                    if (!file_exists(__DIR__ . '/uploads/visitor/')) {
                        mkdir(__DIR__ . '/uploads/visitor/', 0777, true);
                    }
                    file_put_contents($savePath, $imageBinary);

                    /* ปิดชั่วคราวตามคำขอ: อัพโหลด Google Drive 
                    try {
                        $googleConfig = include('config/google_drive_config.php');
                        if (!empty($googleConfig['visitor_folder_id'])) {
                            uploadToGoogleDrive($savePath, $fileName, $googleConfig['visitor_folder_id'], $googleConfig);
                        }
                    } catch (Exception $e) {}
                    */

                    $imageUrl = $baseUrl . '/' . $fileName;
                    $db_text = 'ส่งรูปภาพ';
                    $db_photo = $fileName;

                    $messagesToSend[] = ['type' => 'text', 'text' => "📷 [$displayName] ส่งรูปภาพ:"];
                    $messagesToSend[] = [
                        'type' => 'image',
                        'originalContentUrl' => $imageUrl,
                        'previewImageUrl' => $imageUrl
                    ];
                }

                // 2. อัพเดตข้อมูลที่เหลือลงในแถวที่จองไว้
                $sql_update = "UPDATE ims_line_webhook_messages SET 
                               line_user_id = :uid, line_display_name = :name, 
                               message_type = :mtype, message_text = :mtext, 
                               photo_path = :photo, group_id = :gid, status = 'N' 
                               WHERE line_message_id = :mid";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->execute([
                    ':uid' => $sourceUserId, ':name' => $displayName, ':mtype' => $messageType,
                    ':mtext' => $db_text, ':photo' => $db_photo, ':gid' => $sourceGroupId, ':mid' => $messageId
                ]);

                // 3. ส่งต่อไปยังอีกกลุ่ม (สลับกลุ่มอัตโนมัติ)
                $targetGroup = ($sourceGroupId === $group1_id) ? $group2_id : $group1_id;
                if (count($messagesToSend) > 0) {
                    pushMessage($targetGroup, $messagesToSend, $channelAccessToken);
                }
            }
        }
    }
}
http_response_code(200);
echo "OK";

function getMessageContent($mid, $token) {
    $ch = curl_init("https://api-data.line.me/v2/bot/message/$mid/content");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    $res = curl_exec($ch); curl_close($ch); return $res;
}
function pushMessage($to, $msg, $token) {
    $ch = curl_init('https://api.line.me/v2/bot/message/push');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['to' => $to, 'messages' => $msg]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "Authorization: Bearer $token"]);
    curl_exec($ch); curl_close($ch);
}
function getMemberProfile($gid, $uid, $token) {
    $ch = curl_init("https://api.line.me/v2/bot/group/$gid/member/$uid");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    $res = curl_exec($ch); curl_close($ch); return json_decode($res, true);
}
?>
