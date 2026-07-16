<?php
/**
 * LINE Messaging API Webhook
 * Channel ID: 2007422545
 * Channel Secret: 84f938d804782c41e9ed3f9a74e950d3
 */

include('config/connect_db.php');

$channelSecret = 'f744f42771904636947c03d0262b9123';
$channelAccessToken = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

$content = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? '';

$hash = hash_hmac('sha256', $content, $channelSecret, true);
$base64Signature = base64_encode($hash);

if ($signature !== $base64Signature) {
    http_response_code(400);
    exit("Invalid signature");
}

$events = json_decode($content, true);

if (isset($events['events']) && is_array($events['events'])) {
    foreach ($events['events'] as $event) {
        $type = $event['type'];
        $userId = $event['source']['userId'] ?? 'Unknown';
        $replyToken = $event['replyToken'] ?? null;

        // Log locally
        $logPath = __DIR__ . '/webhook_log.txt';
        $logData = "เวลา: " . date('Y-m-d H:i:s') . " | User ID: " . $userId . " | Event Type: " . $type . "\n";
        file_put_contents($logPath, $logData, FILE_APPEND);

        if ($type === 'message') {
            $messageId = $event['message']['id'];
            $messageType = $event['message']['type'];

            // Prevent duplicate message processing
            try {
                $sql_lock = "INSERT INTO ims_line_webhook_messages (line_message_id, status) VALUES (:mid, 'P')";
                $stmt_lock = $conn->prepare($sql_lock);
                $stmt_lock->execute([':mid' => $messageId]);
            } catch (Exception $e) {
                continue; // Skip if already exists
            }

            $displayName = 'ผู้ใช้งาน LINE';
            $profile = getUserProfile($userId, $channelAccessToken);
            if (isset($profile['displayName'])) {
                $displayName = $profile['displayName'];
            }

            $db_text = '';
            $db_photo = '';

            if ($messageType === 'text') {
                $db_text = $event['message']['text'];
            } elseif ($messageType === 'image') {
                $db_text = 'ส่งรูปภาพ';
                $imageBinary = getMessageContent($messageId, $channelAccessToken);
                $fileName = 'line_msg_' . $messageId . '.jpg';
                $savePath = __DIR__ . '/line_oa/checkin/uploads/' . $fileName; // Save to standard LINE uploads
                if (!file_exists(__DIR__ . '/line_oa/checkin/uploads/')) {
                    mkdir(__DIR__ . '/line_oa/checkin/uploads/', 0777, true);
                }
                file_put_contents($savePath, $imageBinary);
                $db_photo = $fileName;
            }

            // Look up registered resident details
            $house_number = '';
            $first_name = '';
            $last_name = '';
            $phone = '';
            try {
                $sql_h = "SELECT house_number, f_name, l_name, line_phone FROM ims_house_line_user WHERE line_user_id = ?";
                $stmt_h = $conn->prepare($sql_h);
                $stmt_h->execute([$userId]);
                $h_row = $stmt_h->fetch(PDO::FETCH_ASSOC);
                if ($h_row) {
                    $house_number = $h_row['house_number'];
                    $first_name = $h_row['f_name'];
                    $last_name = $h_row['l_name'];
                    $phone = $h_row['line_phone'];
                }
            } catch (Exception $e) {}

            // Save details
            $sql_update = "UPDATE ims_line_webhook_messages SET 
                           line_user_id = :uid, line_display_name = :name, 
                           first_name = :fname, last_name = :lname, house_number = :hnum, phone = :phone,
                           message_type = :mtype, message_text = :mtext, 
                           photo_path = :photo, group_id = 'PRIVATE', status = 'N' 
                           WHERE line_message_id = :mid";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([
                ':uid' => $userId, ':name' => $displayName, 
                ':fname' => $first_name, ':lname' => $last_name, ':hnum' => $house_number, ':phone' => $phone,
                ':mtype' => $messageType, ':mtext' => $db_text, ':photo' => $db_photo, ':mid' => $messageId
            ]);
        }
    }
}

http_response_code(200);
echo "OK";

function getUserProfile($userId, $token) {
    $ch = curl_init("https://api.line.me/v2/bot/profile/$userId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    $res = curl_exec($ch); curl_close($ch);
    return json_decode($res, true);
}

function getMessageContent($mid, $token) {
    $ch = curl_init("https://api-data.line.me/v2/bot/message/$mid/content");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    $res = curl_exec($ch); curl_close($ch);
    return $res;
}
?>
