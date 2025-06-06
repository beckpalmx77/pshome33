<?php
require "../../config/connect_db.php";
header("Access-Control-Allow-Origin: *");

$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';

if (isset($_POST['user_id'], $_POST['latitude'], $_POST['longitude'], $_POST['place_name'], $_POST['check_type']) && isset($_FILES['photo'])) {
    $userId = $_POST['user_id'];
    $displayName = $_POST['display_name'] ?? 'Unknown';
    $place_name = $_POST['place_name'];
    $check_type = $_POST['check_type'];
    $lat = $_POST['latitude'];
    $lon = $_POST['longitude'];
    $timestamp = date('Y-m-d H:i:s');

    // ตรวจสอบว่ามี check-in/out ซ้ำภายใน 5 นาทีหรือไม่
    $fiveMinAgo = date('Y-m-d H:i:s', strtotime('-5 minutes', strtotime($timestamp)));
    $stmt = $conn->prepare("SELECT COUNT(*) FROM checkins WHERE user_id = ? AND check_type = ? AND checkin_time BETWEEN ? AND ?");
    $stmt->execute([$userId, $check_type, $fiveMinAgo, $timestamp]);
    if ($stmt->fetchColumn() > 0) {
        echo "⚠️ ไม่สามารถ{$check_type} ได้ซ้ำภายใน 5 นาที";
        exit;
    }

    // ตรวจสอบว่า check_type เดิมซ้ำกับรายการล่าสุดหรือไม่
    $stmt = $conn->prepare("SELECT check_type FROM checkins WHERE user_id = ? ORDER BY checkin_time DESC LIMIT 1");
    $stmt->execute([$userId]);
    $lastType = $stmt->fetchColumn();
    if ($lastType && $lastType === $check_type) {
        echo "⚠️ ไม่สามารถ{$check_type} ซ้ำได้ กรุณาสลับเป็น " . ($check_type === 'IN' ? 'OUT' : 'IN');
        exit;
    }

    $uploadDir = __DIR__ . "/uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $photoNames = [];
    foreach ($_FILES["photo"]["tmp_name"] as $index => $tmpName) {
        $originalName = pathinfo($_FILES["photo"]["name"][$index], PATHINFO_FILENAME);
        $newFileName = uniqid("checkin_") . "_" . $originalName . ".jpg";
        $newFilePath = $uploadDir . $newFileName;

        $token_checkin = uniqid("ps33_", true);

        $imageInfo = getimagesize($tmpName);
        if ($imageInfo === false) continue;
        $mime = $imageInfo['mime'];

        switch ($mime) {
            case 'image/jpeg': $image = imagecreatefromjpeg($tmpName); break;
            case 'image/png': $image = imagecreatefrompng($tmpName); break;
            case 'image/webp': $image = imagecreatefromwebp($tmpName); break;
            case 'image/gif': $image = imagecreatefromgif($tmpName); break;
            default: continue 2;
        }

        if ($image && imagejpeg($image, $newFilePath, 90)) {
            $photoNames[] = $newFileName;
            imagedestroy($image);
        }
    }

    if (!empty($photoNames)) {
        $photoPaths = implode(",", $photoNames);
        $stmt = $conn->prepare("INSERT INTO checkins (user_id, display_name, place_name, latitude, longitude, checkin_time, photo_path, check_type, token_checkin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $displayName, $place_name, $lat, $lon, $timestamp, $photoPaths, $check_type, $token_checkin]);

        $actionText = ($check_type === 'IN') ? "เช็คอิน" : "เช็คเอาท์";

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

        file_put_contents("line_push_log.txt", "[$timestamp] HTTP CODE: $httpCode\nResult: $result\nError: $error\n\n", FILE_APPEND);

        if ($httpCode === 200) {
            echo "✅ $actionText สำเร็จและส่ง LINE สำเร็จแล้ว";
        } else {
            echo "❌ $actionText สำเร็จ แต่ส่ง LINE ไม่สำเร็จ: $result";
        }
    } else {
        http_response_code(500);
        echo "❌ อัปโหลดรูปไม่สำเร็จ";
    }
} else {
    http_response_code(400);
    echo "❌ ข้อมูลไม่ครบถ้วน";
}
?>
