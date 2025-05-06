<?php
require "../../config/connect_db.php";
header("Access-Control-Allow-Origin: *");

$channelAccessToken = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

if (isset($_POST['user_id'], $_POST['remark'])) {
    $userId = $_POST['user_id'];
    $displayName = $_POST['display_name'] ?? '';
    $place_name = $_POST['place_name'] ?? '';
    $remark = $_POST['remark'] ?? '';
    $check_type = $_POST['check_type'] ?? 'IN';
    $timestamp = date('Y-m-d H:i:s');
    $token_checkin = uniqid("ps33_", true);
    $photoNames = [];

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

    // อัปโหลดภาพ
    if (isset($_FILES['photo'])) {
        $uploadDir = __DIR__ . "/uploads/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES["photo"]["tmp_name"] as $index => $tmpName) {
            $originalName = pathinfo($_FILES["photo"]["name"][$index], PATHINFO_FILENAME);
            $newFileName = uniqid("checkin_") . "_" . $originalName . ".jpg";
            $newFilePath = $uploadDir . $newFileName;

            $imageInfo = getimagesize($tmpName);
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

    // ⛳ เพิ่ม latitude และ longitude ในฐานข้อมูล
    $stmt = $conn->prepare("
        INSERT INTO afront_contact 
        (user_id, display_name, place_name, checkin_time, photo_path, check_type, token_checkin, remark,f_name,l_name,house_number) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $success = $stmt->execute([
        $userId, $displayName, $place_name, $timestamp,
        $photoPaths, $check_type, $token_checkin,
        $remark,$f_name,$l_name,$house_number]);

    if (!$success) {
        http_response_code(500);
        echo "❌ ไม่สามารถบันทึกข้อมูลลงฐานข้อมูลได้";
        exit;
    }

    // ✅ ส่งข้อความ LINE
    $actionText = "บันทึกข้อมูล";
    $flexContents = [];

    foreach (array_slice($photoNames, 0, 10) as $photo) {
        $imageUrl = "https://syycp.com/api/checkin/uploads/" . $photo;
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
                        "text" => "ข้อความที่ส่ง : " . $remark,
                        "weight" => "bold",
                        "size" => "md"
                    ],
                    [
                        "type" => "text",
                        "text" => "เวลา : $timestamp",
                        "size" => "sm",
                        "color" => "#888888"
                    ]
                ]
            ]
        ];
    }

    $messageData = [
        'to' => $userId,
        'messages' => []
    ];

    if (!empty($flexContents)) {
        $messageData['messages'][] = [
            'type' => 'flex',
            'altText' => "📸 รูปภาพจากการ{$actionText}",
            'contents' => [
                'type' => 'carousel',
                'contents' => $flexContents
            ]
        ];
    } else {
        $messageData['messages'][] = [
            'type' => 'text',
            'text' => "✅ คุณได้{$actionText}เรียบร้อยแล้ว\nข้อความ: $remark\nเวลา: $timestamp"
        ];
    }

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
        echo "✅ บันทึกสำเร็จและส่ง LINE สำเร็จแล้ว";
    } else {
        echo "❌ บันทึกสำเร็จ แต่ส่ง LINE ไม่สำเร็จ: $result";
    }
} else {
    http_response_code(400);
    echo "❌ ข้อมูลไม่ครบถ้วน";
}
?>
