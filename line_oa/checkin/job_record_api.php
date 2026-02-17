<?php
require "../../config/connect_db.php";
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Bangkok");

// --- การตั้งค่า Log แยกไฟล์สำหรับ Job Record ---
$logDir = __DIR__ . "/logs/";
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}
$jobLogFile = $logDir . "job_debug_log.txt";
$jobSqlFile = $logDir . "job_insert_queries.sql";

function writeJobLog($message) {
    global $jobLogFile;
    file_put_contents($jobLogFile, "[" . date("Y-m-d H:i:s") . "] $message\n", FILE_APPEND);
}

function writeJobSql($sql, $params, $displayName) {
    global $jobSqlFile;
    $query = $sql;
    foreach ($params as $param) {
        $value = is_null($param) ? "NULL" : "'" . addslashes($param) . "'";
        $query = preg_replace('/\?/', $value, $query, 1);
    }
    $entry = "-- Job Record by " . $displayName . " at " . date("Y-m-d H:i:s") . "\n" . $query . ";\n\n";
    file_put_contents($jobSqlFile, $entry, FILE_APPEND);
}

// --- LINE Configuration ---
$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';
$group_ps33_niti = 'Ca579b4e8daae57c0f07c3508696074ae';

writeJobLog("🚀 เริ่มต้นบันทึก Job Record");

if (isset($_POST['user_id'], $_POST['remark']) && isset($_FILES['photo'])) {
    $userId = $_POST['user_id'];
    $displayName = $_POST['display_name'] ?? 'Unknown';
    $place_name = $_POST['place_name'] ?? '-';
    $check_type = $_POST['check_type'] ?? 'JOB';
    $remark = $_POST['remark'];
    $lat = $_POST['latitude'] ?? null;
    $lon = $_POST['longitude'] ?? null;
    $timestamp = date('Y-m-d H:i:s');

    // 1. ตรวจสอบข้อมูลพนักงาน (ดึง emp_id)
    try {
        $sql_emp = "SELECT emp_id FROM ims_employee_line_user WHERE line_user_id = :line_user_id LIMIT 1";
        $stmt_emp = $conn->prepare($sql_emp);
        $stmt_emp->execute([':line_user_id' => $userId]);
        $emp_id = $stmt_emp->fetchColumn() ?: null;
    } catch (PDOException $e) {
        writeJobLog("❌ Error fetch emp_id: " . $e->getMessage());
    }

    // 2. ตรวจสอบข้อมูลซ้ำ
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM jobrecord WHERE user_id = ? AND checkin_time = ?");
    $stmt_check->execute([$userId, $timestamp]);
    if ($stmt_check->fetchColumn() > 0) {
        writeJobLog("⚠️ ข้อมูลซ้ำสำหรับ $displayName");
        echo "❌ คุณได้ส่งข้อมูลนี้แล้ว";
        exit;
    }

    // 3. จัดการรูปภาพ
    $uploadDir = __DIR__ . "/uploads/";
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

    $photoNames = [];
    foreach ($_FILES["photo"]["tmp_name"] as $index => $tmpName) {
        if (empty($tmpName)) continue;
        $originalName = pathinfo($_FILES["photo"]["name"][$index], PATHINFO_FILENAME);
        $newFileName = "job_" . uniqid() . ".jpg";

        if (move_uploaded_file($tmpName, $uploadDir . $newFileName)) {
            $photoNames[] = $newFileName;
        }
    }

    if (!empty($photoNames)) {
        $photoPaths = implode(",", $photoNames);
        $token_checkin = uniqid("ps33_job_", true);

        // --- 4. บันทึกฐานข้อมูล (Retry Logic) ---
        $sql_ins = "INSERT INTO jobrecord (user_id, display_name, place_name, latitude, longitude, checkin_time, photo_path, check_type, token_checkin, remark, emp_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$userId, $displayName, $place_name, $lat, $lon, $timestamp, $photoPaths, $check_type, $token_checkin, $remark, $emp_id];

        $isSaved = false;
        $retryCount = 0;
        while (!$isSaved && $retryCount < 5) {
            try {
                $stmt = $conn->prepare($sql_ins);
                $stmt->execute($params);
                writeJobSql($sql_ins, $params, $displayName);
                writeJobLog("✅ บันทึก Job Successful (User: $displayName)");
                $isSaved = true;
            } catch (PDOException $e) {
                $retryCount++;
                writeJobLog("❌ Retry $retryCount: " . $e->getMessage());
                usleep(500000);
            }
        }

        if ($isSaved) {
            // --- 5. ส่ง LINE Notification (Text + Flex) ---
            $actionText = "บันทึกรายงาน";
            $textMessage = [
                'type' => 'text',
                'text' => "✅ {$actionText} สำเร็จโดยคุณ {$displayName}\nรายละเอียด: {$remark}\nเวลา: {$timestamp}"
            ];

            $flexContents = [];
            foreach (array_slice($photoNames, 0, 10) as $photo) {
                $imageUrl = "https://ps33home.com/line_oa/checkin/uploads/" . $photo;
                $flexContents[] = [
                    "type" => "bubble",
                    "hero" => ["type" => "image", "url" => $imageUrl, "size" => "full", "aspectRatio" => "1:1", "aspectMode" => "cover"],
                    "body" => [
                        "type" => "box", "layout" => "vertical",
                        "contents" => [
                            ["type" => "text", "text" => "รายละเอียด : " . $remark, "weight" => "bold", "size" => "md", "wrap" => true],
                            ["type" => "text", "text" => "เวลา: $timestamp", "size" => "sm", "color" => "#888888"]
                        ]
                    ]
                ];
            }

            $flexMessage = [
                'type' => 'flex',
                'altText' => "📸 รูปภาพจากการ{$actionText}",
                'contents' => ['type' => 'carousel', 'contents' => $flexContents]
            ];

            // วนลูปส่งให้ผู้ใช้และกลุ่มนิติ
            foreach ([$userId, $group_ps33_niti] as $recipient) {
                $ch = curl_init('https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['to' => $recipient, 'messages' => [$textMessage, $flexMessage]]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $channelAccessToken]);
                $res = curl_exec($ch);
                curl_close($ch);
                writeJobLog("📱 LINE Sent to: $recipient");
            }

            echo "✅ $actionText สำเร็จและส่ง LINE แล้ว";
        } else {
            echo "❌ บันทึกฐานข้อมูลล้มเหลว";
        }
    } else {
        echo "❌ อัปโหลดรูปไม่สำเร็จ";
    }
} else {
    echo "❌ ข้อมูลไม่ครบถ้วน";
}