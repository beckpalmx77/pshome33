<?php
require "../../config/connect_db.php";
header("Access-Control-Allow-Origin: *");

$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';
$group_ps33_niti = 'Ca579b4e8daae57c0f07c3508696074ae';

if (isset($_POST['user_id'], $_POST['latitude'], $_POST['longitude'], $_POST['remark']) && isset($_FILES['photo'])) {
    $userId = $_POST['user_id'];
    $displayName = $_POST['display_name'] ?? '';
    $pictureUrl = $_POST['pictureUrl'] ?? '';
    $place_name = $_POST['place_name'] ?? '';
    $check_type = $_POST['check_type'] ?? 'IN';
    $remark = $_POST['remark'];
    $lat = $_POST['latitude'];
    $lon = $_POST['longitude'];
    $timestamp = date('Y-m-d H:i:s');
    $token_checkin = uniqid("ps33_", true);

    // 🔎 ดึงข้อมูลชื่อจาก memployee
    $sql_get_data = "SELECT f_name, l_name, nickname FROM memployee WHERE line_user_id = :userId";
    $stmt = $conn->prepare($sql_get_data);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $f_name   = $row['f_name'] ?? '';
        $l_name   = $row['l_name'] ?? '';
        $nickname = $row['nickname'] ?? '';
    } else {
        $f_name = $l_name = $nickname = '';
    }

    $full_name = trim("$f_name $l_name") . " ($nickname)";

    // 📸 อัปโหลดรูปภาพ
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $photoNames = [];
    foreach ($_FILES["photo"]["tmp_name"] as $index => $tmpName) {
        $originalName = pathinfo($_FILES["photo"]["name"][$index], PATHINFO_FILENAME);
        $newFileName = uniqid("job_") . "_" . $originalName . ".jpg";
        $newFilePath = $uploadDir . $newFileName;

        $imageInfo = getimagesize($tmpName);
        $mime = $imageInfo['mime'] ?? '';

        switch ($mime) {
            case 'image/jpeg': $image = imagecreatefromjpeg($tmpName); break;
            case 'image/png':  $image = imagecreatefrompng($tmpName);  break;
            case 'image/webp': $image = imagecreatefromwebp($tmpName); break;
            case 'image/gif':  $image = imagecreatefromgif($tmpName);  break;
            default: continue 2;
        }

        if ($image && imagejpeg($image, $newFilePath, 90)) {
            $photoNames[] = $newFileName;
            imagedestroy($image);
        }
    }

    if (!empty($photoNames)) {
        $photoPaths = implode(",", $photoNames);

        // ⏺ บันทึกลงฐานข้อมูล
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

        // 🧩 Flex Message เริ่มต้นด้วย Bubble โปรไฟล์
        $flexContents = [];

        // เพิ่ม Bubble โปรไฟล์
        $flexContents[] = [
            "type" => "bubble",
            "hero" => [
                "type" => "image",
                "url" => $pictureUrl,
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
                        "text" => "👤 $full_name",
                        "weight" => "bold",
                        "size" => "md"
                    ],
                    [
                        "type" => "text",
                        "text" => "🔷 LINE: $displayName",
                        "size" => "sm",
                        "color" => "#888888"
                    ]
                ]
            ]
        ];

        // เพิ่มภาพการเช็คอิน/เอาท์
        foreach (array_slice($photoNames, 0, 10) as $photo) {
            $imageUrl = "https://ps33home.com/line_oa/checkin/uploads/" . $photo;
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

        // รายชื่อปลายทางที่จะส่ง LINE (user และ group)
        $targetIds = [$userId, $group_ps33_niti];

        foreach ($targetIds as $target) {
            $messageData = [
                'to' => $target,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => "📌 แจ้งเตือนการรายงาน จาก $full_name\n🔷 line user: $displayName\n📍 สถานที่: $place_name\n📝 รายละเอียด: $remark\n🕒 เวลา: $timestamp"
                    ],
                    [
                        'type' => 'flex',
                        'altText' => "📸 รูปภาพจากการรายงาน ",
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

            file_put_contents("line_push_log.txt", "[$timestamp] TO $target: HTTP $httpCode\nResult: $result\nError: $error\n\n", FILE_APPEND);
        }

        echo "✅ บันทึกสำเร็จ และส่ง LINE ให้ผู้ใช้และกลุ่มแล้ว";
    } else {
        http_response_code(500);
        echo "❌ อัปโหลดรูปไม่สำเร็จ";
    }
} else {
    http_response_code(400);
    echo "❌ ข้อมูลไม่ครบถ้วน";
}
