<?php
require "../../config/connect_db.php";
header("Access-Control-Allow-Origin: *");

$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';

if (isset($_POST['user_id'], $_POST['latitude'], $_POST['longitude'], $_POST['remark']) && isset($_FILES['photo'])) {
    $userId = $_POST['user_id'];
    $displayName = $_POST['display_name'] ?? '';
    $place_name = $_POST['place_name'] ?? '';
    $check_type = $_POST['check_type'] ?? 'IN';
    $remark = $_POST['remark'];
    $lat = $_POST['latitude'];
    $lon = $_POST['longitude'];
    $timestamp = date('Y-m-d H:i:s');
    $token_checkin = uniqid("sac_", true);

    $uploadDir = __DIR__ . "/uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $photoNames = [];
    foreach ($_FILES["photo"]["tmp_name"] as $index => $tmpName) {
        $originalName = pathinfo($_FILES["photo"]["name"][$index], PATHINFO_FILENAME);
        $newFileName = uniqid("checkin_") . "_" . $originalName . ".jpg";
        $newFilePath = $uploadDir . $newFileName;

        $imageInfo = getimagesize($tmpName);
        $mime = $imageInfo['mime'] ?? '';

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

    if (!empty($photoNames)) {
        $photoPaths = implode(",", $photoNames);

        $stmt = $conn->prepare("
            INSERT INTO jobrecord (
                user_id, emp_id, display_name, place_name, latitude, longitude,
                checkin_time, photo_path, check_type, token_checkin, remark
            ) VALUES (?, '', ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId, $displayName, $place_name,
            $lat, $lon, $timestamp, $photoPaths,
            $check_type, $token_checkin, $remark
        ]);

        $actionText = ($check_type === 'IN') ? "เช็คอิน" : "เช็คเอาท์";

        // สร้าง Flex Message
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
                            "text" => "📍 สถานที่ : " . $place_name,
                            "weight" => "bold",
                            "size" => "md"
                        ],
                        [
                            "type" => "text",
                            "text" => "🕒 เวลา: $timestamp",
                            "size" => "sm",
                            "color" => "#888888"
                        ],
                        [
                            "type" => "text",
                            "text" => "📝 รายละเอียด: " . $remark,
                            "wrap" => true,
                            "size" => "sm"
                        ]
                    ]
                ]
            ];
        }

        $messageData = [
            'to' => $userId,
            'messages' => [
                [
                    'type' => 'flex',
                    'altText' => "📸 รูปภาพจากการ{$actionText}",
                    'contents' => [
                        'type' => 'carousel',
                        'contents' => $flexContents
                    ]
                ]
            ]
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
            echo "✅ บันทึก สำเร็จและส่ง LINE สำเร็จแล้ว";
        } else {
            echo "❌ บันทึก สำเร็จ แต่ส่ง LINE ไม่สำเร็จ: $result";
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
