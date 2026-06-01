<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../util/google_drive_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $target_group = $_POST['target_group'];
    $message_text = $_POST['message_text'];
    $channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';
    
    $imageUrl = "";
    $photo_path = "";

    // 1. Handle Image Upload (if any)
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $upload_dir = '../uploads/visitor/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES['image_file']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $file_path)) {
            $imageUrl = 'https://ps33home.com/uploads/visitor/' . $file_name;
            $photo_path = $file_name;

            // --- Google Drive Upload Section ---
            try {
                $googleConfig = include('../config/google_drive_config.php');
                if (!empty($googleConfig['visitor_folder_id'])) {
                    uploadToGoogleDrive($file_path, $file_name, $googleConfig['visitor_folder_id'], $googleConfig);
                }
            } catch (Exception $e) {
                error_log("Google Drive Message Upload Error: " . $e->getMessage());
            }
            // ------------------------------------
        }
    }

    // 2. Prepare Flex Message
    $contents = [];
    
    // Add Hero (Image) if exists
    if ($imageUrl !== "") {
        $contents['hero'] = [
            "type" => "image",
            "url" => $imageUrl,
            "size" => "full",
            "aspectRatio" => "20:13",
            "aspectMode" => "cover",
            "action" => [
                "type" => "uri",
                "uri" => $imageUrl
            ]
        ];
    }

    // Add Body (Text)
    $contents['body'] = [
        "type" => "box",
        "layout" => "vertical",
        "contents" => [
            [
                "type" => "text",
                "text" => "📢 ประกาศข่าวสาร",
                "weight" => "bold",
                "color" => "#1DB446",
                "size" => "sm"
            ],
            [
                "type" => "text",
                "text" => $message_text,
                "wrap" => true,
                "margin" => "md",
                "size" => "md",
                "color" => "#333333"
            ]
        ]
    ];

    $flex_message = [
        "type" => "flex",
        "altText" => "📢 ข่าวสารจากนิติบุคคล",
        "contents" => array_merge(["type" => "bubble"], $contents)
    ];

    // 3. Send to LINE
    $response = pushMessage($target_group, [$flex_message], $channelAccessToken);
    
    if ($response) {
        echo 1;
    } else {
        echo "Failed to send LINE message";
    }
}

function pushMessage($to_id, $messages_array, $token) {
    $url = 'https://api.line.me/v2/bot/message/push';
    $data = [
        'to' => $to_id,
        'messages' => $messages_array
    ];
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
    curl_close($ch);

    return ($httpCode == 200);
}
