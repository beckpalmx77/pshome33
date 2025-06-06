<?php
require "../../config/connect_db.php";
header("Access-Control-Allow-Origin: *");

date_default_timezone_set("Asia/Bangkok");

$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';

$logFile = __DIR__ . "/debug_checkin_log.txt";

function writeLog($message) {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] $message\n", FILE_APPEND);
}

writeLog("🔧 เริ่มต้นประมวลผล check-in");

// ตรวจสอบค่าที่ส่งมาครบหรือไม่ (user_id, latitude, longitude, place_name, check_type และ ไฟล์ photo)
if (
    isset($_POST['user_id'], $_POST['latitude'], $_POST['longitude'], $_POST['place_name'], $_POST['check_type'])
    && isset($_FILES['photo'])
) {
    $userId = $_POST['user_id'];
    $displayName = $_POST['display_name'] ?? 'Unknown';
    $place_name = $_POST['place_name'];
    $check_type = $_POST['check_type'];
    $lat = $_POST['latitude'];
    $lon = $_POST['longitude'];
    $timestamp = date('Y-m-d H:i:s');

    writeLog("📥 รับข้อมูลจาก client: user_id=$userId, display_name=$displayName, check_type=$check_type");

    // ตรวจสอบการเช็คซ้ำภายใน 5 นาที
    $fiveMinAgo = date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime($timestamp)));
    $stmt = $conn->prepare("SELECT COUNT(*) FROM checkins WHERE user_id = ? AND check_type = ? AND checkin_time BETWEEN ? AND ?");
    $stmt->execute([$userId, $check_type, $fiveMinAgo, $timestamp]);
    $count = $stmt->fetchColumn();
    writeLog("🔁 เช็คซ้ำภายใน 5 นาที: $count");

    if ($count > 0) {
        echo "⚠️ ไม่สามารถ{$check_type} ได้ซ้ำภายใน 5 นาที";
        exit;
    }

    // ตรวจสอบกับรายการล่าสุด เพื่อป้องกันเช็คอิน-เช็คเอาท์ซ้ำกัน
    $stmt = $conn->prepare("SELECT check_type FROM checkins WHERE user_id = ? ORDER BY checkin_time DESC LIMIT 1");
    $stmt->execute([$userId]);
    $lastType = $stmt->fetchColumn();
    writeLog("🔍 check_type ล่าสุด: $lastType");

    if ($lastType && $lastType === $check_type) {
        echo "⚠️ ไม่สามารถ{$check_type} ซ้ำได้ กรุณาสลับเป็น " . ($check_type === 'IN' ? 'OUT' : 'IN');
        exit;
    }

    // สร้างโฟลเดอร์สำหรับเก็บรูปถ้าหากยังไม่มี
    $uploadDir = __DIR__ . "/uploads/";
    if (!file_exists($uploadDir)) {
        if (mkdir($uploadDir, 0777, true)) {
            writeLog("📁 สร้างโฟลเดอร์ uploads สำเร็จ");
        } else {
            writeLog("❌ สร้างโฟลเดอร์ uploads ไม่สำเร็จ");
            http_response_code(500);
            echo "❌ เกิดข้อผิดพลาดในการสร้างโฟลเดอร์อัปโหลด";
            exit;
        }
    }

    $photoNames = [];
    $token_checkin = uniqid("ps33_", true);

    // ตรวจสอบว่า $_FILES['photo']['tmp_name'] เป็น array หรือไม่ (รองรับหลายรูป)
    $files = $_FILES['photo'];
    $fileCount = is_array($files['tmp_name']) ? count($files['tmp_name']) : 1;

    for ($i = 0; $i < $fileCount; $i++) {
        $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        $originalName = is_array($files['name']) ? pathinfo($files['name'][$i], PATHINFO_FILENAME) : pathinfo($files['name'], PATHINFO_FILENAME);

        if (!$tmpName || !is_uploaded_file($tmpName)) {
            writeLog("❌ ไฟล์รูปภาพที่ $i ไม่ถูกอัปโหลดอย่างถูกต้อง");
            continue;
        }

        $newFileName = uniqid("checkin_") . "_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName) . ".jpg";
        $newFilePath = $uploadDir . $newFileName;

        $imageInfo = getimagesize($tmpName);
        if ($imageInfo === false) {
            writeLog("❌ ไฟล์ $originalName ไม่ใช่รูปภาพ");
            continue;
        }

        $mime = $imageInfo['mime'];
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($tmpName);
                break;
            case 'image/png':
                $image = imagecreatefrompng($tmpName);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($tmpName);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($tmpName);
                break;
            default:
                writeLog("❌ ไม่รองรับ MIME: $mime");
                continue 2;
        }

        if ($image && imagejpeg($image, $newFilePath, 90)) {
            $photoNames[] = $newFileName;
            imagedestroy($image);
            writeLog("✅ บันทึกรูป: $newFileName");
        } else {
            writeLog("❌ บันทึกรูปไม่สำเร็จ: $newFileName");
        }
    }

    if (!empty($photoNames)) {
        $photoPaths = implode(",", $photoNames);
        $stmt = $conn->prepare("INSERT INTO checkins (user_id, display_name, place_name, latitude, longitude, checkin_time, photo_path, check_type, token_checkin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $displayName, $place_name, $lat, $lon, $timestamp, $photoPaths, $check_type, $token_checkin]);
        writeLog("📝 INSERT ข้อมูลเช็คอินลงฐานข้อมูลเรียบร้อย");

        $actionText = ($check_type === 'IN') ? "เช็คอิน" : "เช็คเอาท์";

        // ส่งข้อความแจ้งเตือน LINE
        $textMessage = [
            'type' => 'text',
            'text' => "✅ {$actionText} สำเร็จ\nสถานที่: {$place_name}\nเวลา: {$timestamp}"
        ];

        $flexContents = [];
        foreach (array_slice($photoNames, 0, 10) as $photo) {
            $imageUrl = "https://ps33.themediathai.com/line_oa/checkin/uploads/" . $photo;
            $flexContents[] = [
                "type" => "bubble",
                "hero" => [
                    "type" => "image",
                    "url" => $imageUrl,
                    "size" => "full",
                    "aspectRatio" => "1:1",
                    "aspectMode" => "cover"
                ],
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "contents" => [
                        [
                            "type" => "text",
                            "text" => "สถานที่ : " . $place_name,
                            "weight" => "bold",
                            "size" => "md"
                        ],
                        [
                            "type" => "text",
                            "text" => "เวลา: $timestamp",
                            "size" => "sm",
                            "color" => "#888888"
                        ]
                    ]
                ]
            ];
        }

        $flexMessage = [
            'type' => 'flex',
            'altText' => "📸 รูปภาพจากการ{$actionText}",
            'contents' => [
                'type' => 'carousel',
                'contents' => $flexContents
            ]
        ];

        $messageData = [
            'to' => $userId,
            'messages' => [$textMessage, $flexMessage]
        ];

        $ch = curl_init('https://api.line.me/v2/bot/message/push');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $channelAccessToken
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        writeLog("📤 ส่ง LINE: HTTP=$httpCode, Result=$result, Error=$error");

        if ($httpCode === 200) {
            echo "✅ $actionText สำเร็จและส่ง LINE สำเร็จแล้ว";
        } else {
            echo "❌ $actionText สำเร็จ แต่ส่ง LINE ไม่สำเร็จ: $result";
        }
    } else {
        http_response_code(500);
        writeLog("❌ ไม่มีรูปที่อัปโหลดได้");
        echo "❌ อัปโหลดรูปไม่สำเร็จ";
    }
} else {
    http_response_code(400);
    writeLog("❌ ค่าที่ส่งมาไม่ครบ: " . json_encode($_POST));
    echo "❌ ข้อมูลไม่ครบถ้วน";
}
