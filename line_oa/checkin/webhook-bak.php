<?php
require "../../config/connect_db.php";
//SAC HR APP Message API Channel
$access_token = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU='; // ใส่ Channel Access Token ของคุณ

$content = file_get_contents('php://input');
$events = json_decode($content, true);

if (!empty($events['events'])) {
    foreach ($events['events'] as $event) {
        if ($event['type'] == 'message') {
            $userId = $event['source']['userId']; // ดึง User ID
            $userProfile = getUserProfile($userId, $access_token); // เรียก API หาโปรไฟล์

            if ($userProfile) {
                $displayName = $userProfile['displayName']; // ดึงชื่อผู้ใช้

                // บันทึกลง MySQL
                saveUser($conn, $userId, $displayName);

                // บันทึกลงไฟล์ .txt
                saveToFile($userId, $displayName);
            }
        }
    }
}

// ฟังก์ชันเรียก API หาข้อมูลโปรไฟล์
function getUserProfile($userId, $access_token) {
    $url = "https://api.line.me/v2/bot/profile/" . $userId;
    $headers = array("Authorization: Bearer " . $access_token);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

// ฟังก์ชันบันทึกลง MySQL (UPDATE ถ้ามี user_id อยู่แล้ว)
function saveUser($conn, $userId, $displayName) {
    try {
        // ตรวจสอบก่อนว่า user_id มีอยู่แล้วหรือไม่
        $check_sql = "SELECT COUNT(*) FROM  ims_check_user WHERE user_id = :user_id";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bindParam(":user_id", $userId, PDO::PARAM_STR);
        $stmt_check->execute();
        $user_exists = $stmt_check->fetchColumn();

        if ($user_exists) {
            // ถ้ามี user_id แล้ว ให้ UPDATE display_name
            $sql = "UPDATE  ims_check_user SET display_name = :display_name WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":display_name", $displayName, PDO::PARAM_STR);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            // ถ้า user_id ยังไม่มี ให้ INSERT ใหม่
            $sql = "INSERT INTO  ims_check_user (user_id, display_name) VALUES (:user_id, :display_name)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_STR);
            $stmt->bindParam(":display_name", $displayName, PDO::PARAM_STR);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
    }
}

// ฟังก์ชันบันทึกลงไฟล์ .txt
function saveToFile($userId, $displayName) {
    $file = 'user_check_app_list.txt';
    $timestamp = date("Y-m-d H:i:s"); // ดึงเวลาปัจจุบันในรูปแบบ YYYY-MM-DD HH:MM:SS
    $data = $timestamp . " | " . $userId . " - " . $displayName . "\n";
    file_put_contents($file, $data, FILE_APPEND); // เพิ่มข้อมูลลงไฟล์

    include 'hook_get_bot_value.php';

}
