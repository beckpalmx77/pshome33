<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../util/google_drive_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $target_group = $_POST['target_group'];
    $message_text = $_POST['message_text'];
    $channelAccessToken = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';
    
    $imageUrls = [];
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];

    // 1. Handle Multiple Image Uploads
    if (isset($_FILES['image_file'])) {
        $files = $_FILES['image_file'];
        $upload_dir = '../uploads/visitor/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] == 0) {
                $file_name = time() . "_" . $i . "_" . basename($files['name'][$i]);
                $file_path = $upload_dir . $file_name;

                if (move_uploaded_file($files['tmp_name'][$i], $file_path)) {
                    $imageUrls[] = $protocol . "://" . $host . "/uploads/visitor/" . $file_name;

                    // --- Google Drive Upload ---
                    try {
                        $googleConfig = include('../config/google_drive_config.php');
                        if (!empty($googleConfig['visitor_folder_id'])) {
                            uploadToGoogleDrive($file_path, $file_name, $googleConfig['visitor_folder_id'], $googleConfig);
                        }
                    } catch (Exception $e) {
                        error_log("Google Drive Message Upload Error: " . $e->getMessage());
                    }
                }
            }
        }
    }

    // 2. Prepare Flex Message (Carousel if multiple images, Bubble if single/no image)
    $flex_message = [];

    if (count($imageUrls) > 1) {
        // Carousel of Bubbles
        $bubbles = [];
        foreach ($imageUrls as $index => $url) {
            $bubble = [
                "type" => "bubble",
                "size" => "micro",
                "hero" => [
                    "type" => "image",
                    "url" => $url,
                    "size" => "full",
                    "aspectRatio" => "1:1",
                    "aspectMode" => "cover",
                    "action" => ["type" => "uri", "uri" => $url]
                ]
            ];
            // Add text only to the first bubble to avoid redundancy, or as a header bubble
            if ($index == 0) {
                $bubble["size"] = "micro";
            }
            $bubbles[] = $bubble;
        }

        // Add a text bubble at the beginning
        array_unshift($bubbles, [
            "type" => "bubble",
            "size" => "micro",
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    ["type" => "text", "text" => "📢 ประกาศ", "weight" => "bold", "color" => "#1DB446", "size" => "sm"],
                    ["type" => "text", "text" => $message_text, "wrap" => true, "margin" => "md", "size" => "xs"]
                ]
            ]
        ]);

        $flex_message = [
            "type" => "flex",
            "altText" => "📢 ข่าวสารใหม่ (" . count($imageUrls) . " รูปภาพ)",
            "contents" => [
                "type" => "carousel",
                "contents" => $bubbles
            ]
        ];
    } else {
        // Single Bubble
        $contents = [];
        if (count($imageUrls) == 1) {
            $contents['hero'] = [
                "type" => "image",
                "url" => $imageUrls[0],
                "size" => "full",
                "aspectRatio" => "20:13",
                "aspectMode" => "cover",
                "action" => ["type" => "uri", "uri" => $imageUrls[0]]
            ];
        }
        $contents['body'] = [
            "type" => "box",
            "layout" => "vertical",
            "contents" => [
                ["type" => "text", "text" => "📢 ประกาศข่าวสาร", "weight" => "bold", "color" => "#1DB446", "size" => "sm"],
                ["type" => "text", "text" => $message_text, "wrap" => true, "margin" => "md", "size" => "md", "color" => "#333333"]
            ]
        ];
        $flex_message = [
            "type" => "flex",
            "altText" => "📢 ข่าวสารจากนิติบุคคล",
            "contents" => array_merge(["type" => "bubble"], $contents)
        ];
    }

    // 3. Send to LINE (Broadcast or Push)
    if ($target_group === 'broadcast') {
        $response = broadcastMessage([$flex_message], $channelAccessToken);
    } else {
        $response = pushMessage($target_group, [$flex_message], $channelAccessToken);
    }
    
    if ($response['success']) {
        echo 1;
    } else {
        echo "LINE Error: " . $response['message'];
    }
}

function pushMessage($to_id, $messages_array, $token) {
    $url = 'https://api.line.me/v2/bot/message/push';
    return sendLineRequest($url, ['to' => $to_id, 'messages' => $messages_array], $token);
}

function broadcastMessage($messages_array, $token) {
    $url = 'https://api.line.me/v2/bot/message/broadcast';
    return sendLineRequest($url, ['messages' => $messages_array], $token);
}

function sendLineRequest($url, $data, $token) {
    $post = json_encode($data);
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode == 200) {
        return ['success' => true];
    } else {
        $resObj = json_decode($result, true);
        $msg = isset($resObj['message']) ? $resObj['message'] : ($error ?: "Unknown error (HTTP $httpCode)");
        return ['success' => false, 'message' => $msg];
    }
}
