<?php
require "../../config/connect_db.php";
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Bangkok");

// ตั้งค่า Path สำหรับโฟลเดอร์ logs
$logDir = __DIR__ . "/logs/";
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

$logFile = $logDir . "debug_checkin_log.txt";
$sqlLogFile = $logDir . "debug_insert_queries.sql";

function writeLog($message) {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] $message\n", FILE_APPEND);
}

// แก้ไขฟังก์ชันให้รับ $displayName เพิ่มเพื่อนำไปเขียนหัว Log
function writeSqlToLog($sql, $params, $displayName) {
    global $sqlLogFile;
    $query = $sql;
    foreach ($params as $param) {
        $value = is_null($param) ? "NULL" : "'" . addslashes($param) . "'";
        $query = preg_replace('/\?/', $value, $query, 1);
    }
    // เพิ่ม "by [ชื่อผู้ใช้งาน]" ต่อท้ายวันที่
    $entry = "-- Generated at " . date("Y-m-d H:i:s") . " by " . $displayName . "\n" . $query . ";\n\n";
    file_put_contents($sqlLogFile, $entry, FILE_APPEND);
}

writeLog("🔧 เริ่มต้นประมวลผล check-in");

if (isset($_POST['user_id'], $_POST['place_name'], $_POST['check_type'])) {
    $userId = $_POST['user_id'];
    $displayName = $_POST['display_name'] ?? 'Unknown';
    $place_name = $_POST['place_name'];
    $check_type = $_POST['check_type'];
    $lat = $_POST['latitude'] ?? null;
    $lon = $_POST['longitude'] ?? null;
    $line_profile_url = $_POST['line_profile_url'] ?? null;
    $timestamp = date('Y-m-d H:i:s');

    // อัปเดต Profile LINE
    if (!empty($userId) && !empty($line_profile_url)) {
        try {
            $sql_up = "UPDATE ims_employee_line_user SET line_picture_profile = :url WHERE line_user_id = :id";
            $stmt_up = $conn->prepare($sql_up);
            $stmt_up->execute([':url' => $line_profile_url, ':id' => $userId]);
        } catch (PDOException $e) {
            writeLog("❌ Profile Update Error: " . $e->getMessage());
        }
    }

    // ตรวจสอบเช็คซ้ำ 1 นาที
    $oneMinAgo = date('Y-m-d H:i:s', strtotime('-1 minutes'));
    $stmt = $conn->prepare("SELECT COUNT(*) FROM checkins WHERE user_id = ? AND check_type = ? AND checkin_time > ?");
    $stmt->execute([$userId, $check_type, $oneMinAgo]);
    if ($stmt->fetchColumn() > 0) {
        writeLog("⚠️ ข้อมูลซ้ำใน 1 นาที สำหรับ user: $displayName");
        echo "บันทึกไม่สำเร็จ"; exit;
    }

    // จัดการรูปภาพ
    $uploadDir = __DIR__ . "/uploads/";
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
    $photoNames = [];
    if (isset($_FILES['photo'])) {
        $files = $_FILES['photo'];
        $count = is_array($files['tmp_name']) ? count($files['tmp_name']) : 1;
        for ($i = 0; $i < $count; $i++) {
            $tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            if (!empty($tmp)) {
                $fName = uniqid("img_") . ".jpg";
                if (move_uploaded_file($tmp, $uploadDir . $fName)) $photoNames[] = $fName;
            }
        }
    }
    $photoPaths = !empty($photoNames) ? implode(",", $photoNames) : null;
    $token = uniqid("ps33_", true);

    // --- Retry Logic: วนลูปจนกว่าจะลง Database สำเร็จ ---
    $sql_ins = "INSERT INTO checkins (user_id, display_name, place_name, latitude, longitude, checkin_time, photo_path, check_type, token_checkin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $params = [$userId, $displayName, $place_name, $lat, $lon, $timestamp, $photoPaths, $check_type, $token];

    $isSaved = false;
    $retryCount = 0;

    while (!$isSaved && $retryCount < 10) {
        try {
            $stmt = $conn->prepare($sql_ins);
            $stmt->execute($params);

            // ส่งค่า $displayName เข้าไปด้วยเพื่อให้บันทึกลงไฟล์ SQL
            writeSqlToLog($sql_ins, $params, $displayName);

            writeLog("✅ บันทึกสำเร็จในครั้งที่ " . ($retryCount + 1) . " (User: $displayName)");
            $isSaved = true;
        } catch (PDOException $e) {
            $retryCount++;
            writeLog("❌ ลองใหม่ครั้งที่ $retryCount สำหรับ $displayName: " . $e->getMessage());
            usleep(500000); // รอ 0.5 วินาที
        }
    }

    echo $isSaved ? "บันทึกสำเร็จ" : "บันทึกไม่สำเร็จ";

} else {
    echo "ข้อมูลไม่ครบ";
}