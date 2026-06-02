<?php
// ตั้งค่า LINE Messaging API
// ใช้ Channel access token (long-lived) ที่ได้รับมา
define('LINE_ACCESS_TOKEN', 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=');
//define('LINE_ACCESS_TOKEN', 'IpR8udFWN6A9z5H+ZmMHSWnkM49C4+eJWmmaXlqwH01rYSkwHlPZMSN5cNekLldYqeMP2Vj0Ez3ZEbpXeSZyylPEa2sYD8bEIb0gDo/iaOVCtMFb0UE2Mz87K0zpiqkhfRNn9Icy/6PMhSfPgcLwAgdB04t89/1O/w1cDnyilFU=');

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

    // Handle Multi-Image Upload from Screen
    if (isset($_FILES['image_upload']) && !empty($_FILES['image_upload']['name'][0])) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $files = $_FILES['image_upload'];
        $count = count($files['name']);
        
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] == UPLOAD_ERR_OK) {
                $file_name = $files['name'][$i];
                $tmp_name = $files['tmp_name'][$i];
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                $allowed_ext = ['jpg', 'jpeg', 'png'];
                if (in_array($ext, $allowed_ext)) {
                    $new_name = uniqid('line_img_') . '.' . $ext;
                    $target_file = $upload_dir . $new_name;
                    
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        // Construct Public URL
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                        $host = $_SERVER['HTTP_HOST'];
                        $request_uri = $_SERVER['REQUEST_URI'];
                        $current_dir = rtrim(dirname($request_uri), '/\\');
                        $public_url = $protocol . $host . $current_dir . '/' . $target_file;
                        
                        $images[] = $public_url;
                    }
                }
            }
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
            if (!empty($images)) {
                $message_status .= "<div class='alert success' style='margin-top:-10px; font-size:12px; font-weight:normal;'>รูปภาพที่ส่ง: <br>" . implode("<br>", $images) . "</div>";
            }
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
        input[type="text"], input[type="file"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        textarea { height: 80px; resize: vertical; }
        .image-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        button { background-color: #06C755; color: white; border: none; padding: 15px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; font-weight: bold; margin-top: 10px; }
        button:hover { background-color: #05b04b; }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; word-break: break-all; }
        .hint { font-size: 12px; color: #666; margin-top: 3px; }
        .upload-section { background-color: #f9f9f9; padding: 15px; border-radius: 4px; border: 1px dashed #06C755; margin-bottom: 20px; }
        .preview-container { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .preview-item { width: 80px; height: 80px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; position: relative; }
        .preview-item img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>

<div class="container">
    <h2>LINE OA Multi-Message Sender</h2>

    <?php echo $message_status; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="target_id">Target ID (User ID / Group ID / Room ID):</label>
            <input type="text" id="target_id" name="target_id" placeholder="ตัวอย่าง: C123456... (เว้นว่างไว้หากต้องการ Broadcast ทุกคน)">
            <p class="hint">* หากต้องการส่งเข้ากลุ่ม ต้องใส่ Group ID (ขึ้นต้นด้วย C)</p>
        </div>

        <div class="form-group">
            <label for="message">ข้อความตัวอักษร:</label>
            <textarea id="message" name="message" placeholder="พิมพ์ข้อความที่ต้องการส่ง..."></textarea>
        </div>

        <div class="upload-section">
            <label for="image_upload">อัปโหลดรูปภาพจากเครื่อง (เลือกได้หลายรูป):</label>
            <input type="file" id="image_upload" name="image_upload[]" accept="image/*" multiple onchange="previewImages(this)">
            <div id="image_preview" class="preview-container"></div>
            <p class="hint">* เลือกได้หลายรูปพร้อมกัน (รองรับ .jpg, .png)</p>
        </div>

        <label>หรือระบุ URL รูปภาพ (สูงสุด 4 รูป):</label>
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

<script>
    function previewImages(input) {
        var preview = document.getElementById('image_preview');
        preview.innerHTML = '';
        if (input.files) {
            Array.from(input.files).forEach(file => {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var div = document.createElement('div');
                    div.className = 'preview-item';
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    div.appendChild(img);
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    }
</script>

</body>
</html>
