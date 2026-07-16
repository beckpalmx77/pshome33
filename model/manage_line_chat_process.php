<?php
session_start();
error_reporting(0);
include('../config/connect_db.php');

$channelAccessToken = 'IpR8udFWN6A9z5H+ZmMHSWnkM49C4+eJWmmaXlqwH01rYSkwHlPZMSN5cNekLldYqeMP2Vj0Ez3ZEbpXeSZyylPEa2sYD8bEIb0gDo/iaOVCtMFb0UE2Mz87K0zpiqkhfRNn9Icy/6PMhSfPgcLwAgdB04t89/1O/w1cDnyilFU=';

$response = ['status' => 'error', 'message' => 'Invalid action'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'GET_USERS') {
        try {
            $sql = "SELECT m1.line_user_id, m1.line_display_name, m1.first_name, m1.last_name, m1.house_number, m1.created_at, m1.message_text,
                           (SELECT COUNT(*) FROM ims_line_webhook_messages WHERE line_user_id = m1.line_user_id AND group_id != 'OUTGOING' AND status = 'N') AS unread_count
                    FROM ims_line_webhook_messages m1
                    INNER JOIN (
                        SELECT line_user_id, MAX(id) as max_id 
                        FROM ims_line_webhook_messages 
                        WHERE line_user_id IS NOT NULL AND line_user_id != '' 
                        GROUP BY line_user_id
                    ) m2 ON m1.id = m2.max_id
                    ORDER BY m1.created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
            exit();
        } catch (Exception $e) {
            echo json_encode([]);
            exit();
        }
    }

    if ($action === 'GET_CHAT_HISTORY') {
        $line_user_id = $_POST['line_user_id'] ?? '';
        if (empty($line_user_id)) {
            echo json_encode([]);
            exit();
        }
        try {
            // Mark incoming messages as read (status = 'Y') when loaded
            $sql_read = "UPDATE ims_line_webhook_messages SET status = 'Y' WHERE line_user_id = :uid AND group_id != 'OUTGOING' AND status = 'N'";
            $stmt_read = $conn->prepare($sql_read);
            $stmt_read->execute([':uid' => $line_user_id]);

            $sql = "SELECT * FROM ims_line_webhook_messages 
                    WHERE line_user_id = :uid 
                    ORDER BY id ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':uid' => $line_user_id]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($history);
            exit();
        } catch (Exception $e) {
            echo json_encode([]);
            exit();
        }
    }

    if ($action === 'SEND_REPLY') {
        $line_user_id = $_POST['line_user_id'] ?? '';
        $message_text = trim($_POST['message_text'] ?? '');

        if (empty($line_user_id) || empty($message_text)) {
            echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
            exit();
        }

        // Call LINE API to push message
        $url = 'https://api.line.me/v2/bot/message/push';
        $data = [
            'to' => $line_user_id,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message_text
                ]
            ]
        ];
        $post = json_encode($data);
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $channelAccessToken
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            // Save reply to database
            $msg_id = 'reply_' . uniqid('', true);
            $timestamp = date('Y-m-d H:i:s');
            try {
                $sql_ins = "INSERT INTO ims_line_webhook_messages (line_message_id, line_user_id, line_display_name, message_type, message_text, group_id, status, created_at) 
                            VALUES (?, ?, ?, 'text', ?, 'OUTGOING', 'Y', ?)";
                $stmt_ins = $conn->prepare($sql_ins);
                $stmt_ins->execute([$msg_id, $line_user_id, 'นิติบุคคล (Admin)', $message_text, $timestamp]);

                $response = ['status' => 'success', 'message' => 'ส่งข้อความเรียบร้อยแล้ว'];
            } catch (Exception $e) {
                $response = ['status' => 'success', 'message' => 'ส่งข้อความสำเร็จ แต่ไม่สามารถบันทึกประวัติได้: ' . $e->getMessage()];
            }
        } else {
            $response = ['status' => 'error', 'message' => 'ส่งข้อความไม่สำเร็จ LINE API Error (Code: ' . $httpCode . '): ' . $result];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
