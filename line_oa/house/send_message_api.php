<?php
require "../../config/connect_db.php";
header("Access-Control-Allow-Origin: *");

$channelAccessToken = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';
$logFileAll = "line_message_all_log.txt";

function sanitizeFileName($filename) {
    return preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $filename);
}

function sendLineMessage($accessToken, $userId, $messages) {
    $ch = curl_init('https://api.line.me/v2/bot/message/push');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'to' => $userId,
        'messages' => $messages
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . 'Bearer ' . $accessToken
    ]);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return ['httpCode' => $httpCode, 'result' => $result, 'error' => $error];
}

if (isset($_POST['user_id'], $_POST['remark'])) {
    $userId = $_POST['user_id'];
    $displayName = $_POST['display_name'] ?? '';
    $place_name = $_POST['place_name'] ?? '';
    $remark = $_POST['remark'] ?? '';
    $check_type = $_POST['check_type'] ?? 'IN';
    $latitude = '0';
    $longitude = '0';
    $timestamp = date('Y-m-d H:i:s');
    $token_checkin = uniqid("ps33_", true);
    $photoNames = [];

    try {
        // 🔍 ดึงข้อมูลผู้ใช้งาน
        $sql_get_data = "SELECT house_number, f_name, l_name FROM ims_house_line_user WHERE line_user_id = ?";
        $stmt = $conn->prepare($sql_get_data);
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            http_response_code(404);
            echo "❌ ไม่พบข้อมูลผู้ใช้ในระบบ";
            exit;
        }

        $f_name = $row['f_name'];
        $l_name = $row['l_name'];
        $house_number = $row['house_number'];

        // 📷 อัปโหลดภาพ
        if (isset($_FILES['photo']) && is_array($_FILES['photo']['tmp_name'])) {
            $uploadDir = "uploads/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES["photo"]["tmp_name"] as $index => $tmpName) {
                if (!is_uploaded_file($tmpName)) continue;

                $originalName = pathinfo($_FILES["photo"]["name"][$index], PATHINFO_FILENAME);
                $originalName = sanitizeFileName($originalName);
                $newFileName = uniqid("checkin_") . "_" . $originalName . ".jpg";
                $newFilePath = $uploadDir . $newFileName;

                $imageInfo = getimagesize($tmpName);
                if (!$imageInfo) continue;

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
                        continue 2;
                }

                if ($image && imagejpeg($image, $newFilePath, 90)) {
                    $photoNames[] = $newFileName;
                    imagedestroy($image);
                }
            }
        }

        $photoPaths = implode(",", $photoNames);

        // ⛳ บันทึกข้อมูลลงฐานข้อมูล
        $stmt = $conn->prepare("
            INSERT INTO afront_contact (
                user_id, display_name, place_name, checkin_time,
                photo_path, check_type, token_checkin, remark,
                f_name, l_name, house_number, latitude, longitude
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $userId, $displayName, $place_name, $timestamp,
            $photoPaths, $check_type, $token_checkin, $remark,
            $f_name, $l_name, $house_number, $latitude, $longitude
        ]);

        if (!$success) {
            http_response_code(500);
            echo "❌ ไม่สามารถบันทึกข้อมูลลงฐานข้อมูลได้";
            exit;
        }

        // 🔧 Flex Message สำหรับรูปภาพ
        $flexMessages = [];
        foreach (array_slice($photoNames, 0, 10) as $photo) {
            $imageUrl = "https://ps33.themediathai.com/line_oa/house/uploads/" . $photo;
            $flexMessages[] = [
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
                            "text" => "👤 ผู้ส่ง: {$displayName}",
                            "weight" => "bold",
                            "size" => "md",
                            "wrap" => true
                        ],
                        [
                            "type" => "text",
                            "text" => "📩 {$remark}",
                            "wrap" => true
                        ],
                        [
                            "type" => "text",
                            "text" => "🕓 เวลา: {$timestamp}",
                            "size" => "sm",
                            "color" => "#888888"
                        ]
                    ]
                ]
            ];
        }

        // ✅ ส่งข้อความกลับไปยังผู้ที่ส่งเท่านั้น
        $messages = [];
        if (count($flexMessages) > 0) {
            $messages = [
                [
                    "type" => "text",
                    "text" => "👤 ผู้ส่ง: {$displayName}\n📩 ข้อความ: {$remark}\n🕓 เวลา: {$timestamp}"
                ],
                [
                    "type" => "flex",
                    "altText" => "บันทึกข้อมูล check-in",
                    "contents" => [
                        "type" => "carousel",
                        "contents" => $flexMessages
                    ]
                ]
            ];
        } else {
            $messages = [
                [
                    "type" => "text",
                    "text" => "👤 ผู้ส่ง: {$displayName}\n📩 ข้อความ: {$remark}\n🕓 เวลา: {$timestamp}"
                ]
            ];
        }

        $sendResult = sendLineMessage($channelAccessToken, $userId, $messages);
        $flatRemark = str_replace(["\n", "\r"], " ", $remark);
        file_put_contents($logFileAll, $userId . " - " . $flatRemark . "\n", FILE_APPEND);

        echo "✅ บันทึกสำเร็จและส่ง LINE แล้ว";

    } catch (Exception $e) {
        http_response_code(500);
        echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
    }
} else {
    http_response_code(400);
    echo "❌ ข้อมูลไม่ครบถ้วน";
}
?>
