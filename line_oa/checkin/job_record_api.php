<?php
require "../../config/connect_db.php";
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Bangkok");

// --- 1. การตั้งค่า Log และ SQL สำหรับ Job Record (แยกจาก Check-in) ---
$logDir = __DIR__ . "/logs";
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// ตรวจสอบสิทธิ์การเขียนโฟลเดอร์
if (!is_writable($logDir)) {
    chmod($logDir, 0777);
}

$jobLogFile = $logDir . "/job_debug_log.txt";
$jobSqlFile = $logDir . "/job_insert_queries.sql";

/**
 * ฟังก์ชันบันทึกการทำงานลงไฟล์ .txt
 */
function writeJobLog($message) {
    global $jobLogFile;
    $logEntry = "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL;
    file_put_contents($jobLogFile, $logEntry, FILE_APPEND);
}

/**
 * ฟังก์ชันบันทึกคำสั่ง INSERT SQL ลงไฟล์ .sql พร้อมค่าจริง
 */
function writeJobSql($sql, $params, $displayName) {
    global $jobSqlFile;
    $query = $sql;
    foreach ($params as $param) {
        if (is_null($param)) {
            $value = "NULL";
        } elseif (is_numeric($param)) {
            $value = $param;
        } else {
            $value = "'" . addslashes((string)$param) . "'";
        }
        // แทนที่เครื่องหมาย ? ด้วยค่าจริงทีละตัว
        $query = preg_replace('/\?/', $value, $query, 1);
    }

    $entry = "-- Job Record by " . $displayName . " at " . date("Y-m-d H:i:s") . PHP_EOL;
    $entry .= $query . ";" . PHP_EOL . PHP_EOL;

    if (file_put_contents($jobSqlFile, $entry, FILE_APPEND) === false) {
        writeJobLog("❌ Error: ไม่สามารถเขียนไฟล์ SQL Log ได้ (Check Permission)");
    }
}

// --- 2. การตั้งค่า LINE Configuration ---
$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';
$group_ps33_niti = 'Ca579b4e8daae57c0f07c3508696074ae';

writeJobLog("🔧 >>> เริ่มต้นประมวลผล Job Record <<<");

// --- 3. ตรวจสอบข้อมูลนำเข้า ---
if (isset($_POST['user_id'], $_POST['remark']) && isset($_FILES['photo'])) {
    $userId = $_POST['user_id'];
    $displayName = $_POST['display_name'] ?? 'Unknown';
    $place_name = $_POST['place_name'] ?? '-';
    $check_type = $_POST['check_type'] ?? 'JOB';
    $remark = $_POST['remark'];
    $lat = $_POST['latitude'] ?? null;
    $lon = $_POST['longitude'] ?? null;
    $timestamp = date('Y-m-d H:i:s');

    // ดึงข้อมูล emp_id
    try {
        $sql_emp = "SELECT emp_id FROM ims_employee_line_user WHERE line_user_id = :line_user_id LIMIT 1";
        $stmt_emp = $conn->prepare($sql_emp);
        $stmt_emp->execute([':line_user_id' => $userId]);
        $emp_id = $stmt_emp->fetchColumn() ?: null;
    } catch (PDOException $e) {
        writeJobLog("❌ Error fetch emp_id: " . $e->getMessage());
        $emp_id = null;
    }

    // ตรวจสอบข้อมูลซ้ำ
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM jobrecord WHERE user_id = ? AND checkin_time = ?");
    $stmt_check->execute([$userId, $timestamp]);
    if ($stmt_check->fetchColumn() > 0) {
        writeJobLog("⚠️ สกัดกั้น: ข้อมูลซ้ำสำหรับ $displayName ที่เวลา $timestamp");
        echo "❌ คุณได้ส่งข้อมูลนี้แล้ว";
        exit;
    }

    // --- 4. จัดการรูปภาพ ---
    $uploadDir = __DIR__ . "/uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $photoNames = [];
    foreach ($_FILES["photo"]["tmp_name"] as $index => $tmpName) {
        if (empty($tmpName)) continue;

        $newFileName = "job_" . uniqid() . ".jpg";
        $newFilePath = $uploadDir . $newFileName;

        $imageInfo = getimagesize($tmpName);
        if ($imageInfo === false) continue;

        $mime = $imageInfo['mime'];
        switch ($mime) {
            case 'image/jpeg': $image = imagecreatefromjpeg($tmpName); break;
            case 'image/png': $image = imagecreatefrompng($tmpName); break;
            case 'image/webp': $image = imagecreatefromwebp($tmpName); break;
            default: $image = null; break;
        }

        if ($image && imagejpeg($image, $newFilePath, 85)) {
            $photoNames[] = $newFileName;
            imagedestroy($image);
        } elseif (move_uploaded_file($tmpName, $newFilePath)) {
            $photoNames[] = $newFileName;
        }
    }

    if (!empty($photoNames)) {
        $photoPaths = implode(",", $photoNames);
        $token_checkin = uniqid("ps33_job_", true);

        // --- 5. บันทึกฐานข้อมูล (Retry Logic) ---
        $sql_ins = "INSERT INTO jobrecord (user_id, display_name, place_name, latitude, longitude, checkin_time, photo_path, check_type, token_checkin, remark, emp_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$userId, $displayName, $place_name, $lat, $lon, $timestamp, $photoPaths, $check_type, $token_checkin, $remark, $emp_id];

        $isSaved = false;
        $retryLimit = 5;
        $retryCount = 0;

        while (!$isSaved && $retryCount < $retryLimit) {
            try {
                $stmt = $conn->prepare($sql_ins);
                $result = $stmt->execute($params);

                if ($result) {
                    writeJobSql($sql_ins, $params, $displayName);
                    writeJobLog("✅ บันทึก Job Successful (User: $displayName) รอบที่ " . ($retryCount + 1));
                    $isSaved = true;
                }
            } catch (PDOException $e) {
                $retryCount++;
                writeJobLog("❌ Retry $retryCount Fail: " . $e->getMessage());
                usleep(500000); // พัก 0.5 วินาที
            }
        }

        if ($isSaved) {
            // --- 6. ส่ง LINE Notification ---
            $actionText = "บันทึกรายงาน";

            // 6.1 ข้อความ Text
            $textMessage = [
                'type' => 'text',
                'text' => "✅ {$actionText} สำเร็จโดยคุณ {$displayName}\nรายละเอียด: {$remark}\nเวลา: {$timestamp}"
            ];

            // 6.2 ข้อความ Flex (Carousel รูปภาพ)
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

            // ส่งให้ทั้ง User และกลุ่ม
            $recipients = [$userId, $group_ps33_niti];
            foreach ($recipients as $to) {
                $ch = curl_init('https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['to' => $to, 'messages' => [$textMessage, $flexMessage]]));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $channelAccessToken
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                writeJobLog("📱 LINE Push to $to (Status: $httpCode)");
            }

            echo "✅ $actionText สำเร็จและส่ง LINE แล้ว";
        } else {
            writeJobLog("❌ ไม่สามารถบันทึกฐานข้อมูลได้หลังจากพยายามครบ $retryLimit ครั้ง");
            echo "❌ บันทึกข้อมูลล้มเหลว";
        }
    } else {
        writeJobLog("❌ อัปโหลดรูปภาพไม่สำเร็จหรือไม่มีไฟล์รูป");
        echo "❌ อัปโหลดรูปไม่สำเร็จ";
    }
} else {
    writeJobLog("⚠️ ข้อมูล POST ไม่ครบถ้วน");
    echo "❌ ข้อมูลไม่ครบถ้วน";
}