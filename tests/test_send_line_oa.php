<?php
// ตั้งค่า LINE Messaging API
// ใช้ Channel access token (long-lived) ที่ได้รับมา
define('LINE_ACCESS_TOKEN', 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=');

$message_status = "";

// ตรวจสอบเมื่อมีการกดส่ง Form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $text_message = trim($_POST['message'] ?? '');
    $target_id = trim($_POST['target_id'] ?? ''); // User ID, Group ID, or Room ID
    
    $images = [];
    for ($i = 1; $i <= 4; $i++) {
        $img_url = trim($_POST["image_url_$i"] ?? '');
        if (!empty($img_url)) {
            $images[] = $img_url;
        }
    }

    // เตรียม Messages array (ส่งได้สูงสุด 5 ข้อความใน 1 Request)
    $messages = [];
    
    // 1. ใส่ข้อความตัวอักษร (ถ้ามี)
    if (!empty($text_message)) {
        $messages[] = [
            'type' => 'text',
            'text' => $text_message
        ];
    }

    // 2. ใส่รูปภาพ (ถ้ามี)
    foreach ($images as $url) {
        if (count($messages) < 5) {
            $messages[] = [
                'type' => 'image',
                'originalContentUrl' => $url,
                'previewImageUrl' => $url
            ];
        }
    }

    if (empty($messages)) {
        $message_status = "<div class='alert danger'>กรุณากรอกข้อความหรือใส่ URL รูปภาพอย่างน้อย 1 อย่าง</div>";
    } else {
        // เลือกว่าจะส่งแบบ Push (ระบุ ID) หรือ Broadcast (ส่งทุกคน)
        $endpoint = 'https://api.line.me/v2/bot/message/broadcast';
        $data = ['messages' => $messages];

        if (!empty($target_id)) {
            $endpoint = 'https://api.line.me/v2/bot/message/push';
            $data['to'] = $target_id;
        }

        $post_data = json_encode($data);

        // ตั้งค่า cURL เพื่อคุยกับ LINE API
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . LINE_ACCESS_TOKEN
        ]);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // ตรวจสอบผลลัพธ์
        if ($http_code == 200) {
            $type_sent = !empty($target_id) ? "Push ($target_id)" : "Broadcast";
            $message_status = "<div class='alert success'>ส่งข้อความแบบ $type_sent สำเร็จแล้ว!</div>";
        } else {
            $message_status = "<div class='alert danger'>เกิดข้อผิดพลาด (โค้ด: $http_code): " . htmlspecialchars($result) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LINE OA Multi-Message Sender</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 700px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin: 0 auto; }
        h2 { color: #06C755; margin-top: 0; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        input[type="text"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        textarea { height: 80px; resize: vertical; }
        .image-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        button { background-color: #06C755; color: white; border: none; padding: 15px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; font-weight: bold; margin-top: 10px; }
        button:hover { background-color: #05b04b; }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; word-break: break-all; }
        .hint { font-size: 12px; color: #666; margin-top: 3px; }
    </style>
</head>
<body>

<div class="container">
    <h2>LINE OA Multi-Message Sender</h2>

    <?php echo $message_status; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="target_id">Target ID (User ID / Group ID / Room ID):</label>
            <input type="text" id="target_id" name="target_id" placeholder="ตัวอย่าง: C123456... (เว้นว่างไว้หากต้องการ Broadcast ทุกคน)">
            <p class="hint">* หากต้องการส่งเข้ากลุ่ม ต้องใส่ Group ID (ขึ้นต้นด้วย C)</p>
        </div>

        <div class="form-group">
            <label for="message">ข้อความตัวอักษร:</label>
            <textarea id="message" name="message" placeholder="พิมพ์ข้อความที่ต้องการส่ง..."></textarea>
        </div>

        <label>URL รูปภาพ (สูงสุด 4 รูป):</label>
        <div class="image-inputs">
            <div class="form-group">
                <input type="text" name="image_url_1" placeholder="https://example.com/image1.jpg">
            </div>
            <div class="form-group">
                <input type="text" name="image_url_2" placeholder="https://example.com/image2.jpg">
            </div>
            <div class="form-group">
                <input type="text" name="image_url_3" placeholder="https://example.com/image3.jpg">
            </div>
            <div class="form-group">
                <input type="text" name="image_url_4" placeholder="https://example.com/image4.jpg">
            </div>
        </div>
        
        <p class="hint">หมายเหตุ: รูปภาพต้องเป็น URL ที่ LINE สามารถเข้าถึงได้ (HTTPS) และนามสกุล .jpg หรือ .png</p>

        <button type="submit">ส่งข้อความและรูปภาพ</button>
    </form>
</div>

</body>
</html>
