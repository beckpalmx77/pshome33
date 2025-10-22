<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัปโหลดรูปภาพและ OCR</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 800px; margin: auto; padding: 25px; border: 1px solid #e0e0e0; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); background-color: #fff; }
        h2 { text-align: center; color: #007bff; margin-bottom: 25px; }
        form { margin-bottom: 30px; padding: 20px; border: 1px solid #e9ecef; border-radius: 8px; background-color: #f8f9fa; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="file"] { border: 1px solid #ced4da; padding: 10px; border-radius: 5px; width: calc(100% - 22px); margin-bottom: 15px; background-color: #e9ecef; }
        input[type="submit"] { background-color: #28a745; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease; }
        input[type="submit"]:hover { background-color: #218838; }
        .message { margin-top: 20px; padding: 15px; border-radius: 8px; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .ocr-result { margin-top: 25px; padding: 20px; border: 1px solid #a2d9ce; background-color: #e0f7fa; border-radius: 8px; }
        .ocr-result h3 { margin-top: 0; color: #00796b; border-bottom: 1px solid #00796b; padding-bottom: 10px; margin-bottom: 15px; }
        .ocr-text { white-space: pre-wrap; word-wrap: break-word; background-color: #ffffff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; max-height: 350px; overflow-y: auto; line-height: 1.6; font-size: 1.1em; }
        .uploaded-image-preview { text-align: center; margin-top: 25px; padding: 20px; border: 1px solid #cce5ff; background-color: #eaf6ff; border-radius: 8px; }
        .uploaded-image-preview h3 { margin-top: 0; color: #007bff; border-bottom: 1px solid #007bff; padding-bottom: 10px; margin-bottom: 15px; }
        .uploaded-image-preview img { max-width: 100%; height: auto; border: 3px solid #007bff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container">
    <h2>อัปโหลดรูปภาพและอ่านข้อความด้วย OCR</h2>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <label for="imageFile">เลือกรูปภาพ:</label>
        <input type="file" name="imageFile" id="imageFile" accept="image/*">
        <input type="submit" value="อัปโหลดและประมวลผล OCR">
    </form>

    <?php
    // เริ่มต้น session เพื่อเข้าถึงข้อมูลที่ส่งมาจาก upload.php
    session_start();

    $message = '';
    $class = '';
    $ocr_result = '';
    $uploaded_image_path = '';

    // ตรวจสอบสถานะที่ส่งกลับมาจาก upload.php
    if (isset($_GET['status'])) {
        $status = $_GET['status'];

        if ($status === 'success_ocr') {
            $message = 'อัปโหลดรูปภาพและประมวลผล OCR สำเร็จ!';
            $class = 'success';
            // ดึงข้อมูล OCR และชื่อรูปภาพจาก Session
            if (isset($_SESSION['ocr_result'])) {
                $ocr_result = $_SESSION['ocr_result'];
                unset($_SESSION['ocr_result']); // ลบข้อมูลใน session หลังจากใช้งาน
            }
            if (isset($_SESSION['uploaded_image'])) {
                $uploaded_image_path = 'uploads/' . $_SESSION['uploaded_image'];
                unset($_SESSION['uploaded_image']); // ลบข้อมูลใน session หลังจากใช้งาน
            }
        } elseif ($status === 'invalid_file_type') {
            $message = 'ข้อผิดพลาด: ไม่อนุญาตไฟล์ประเภทนี้ (รองรับเฉพาะ JPG, JPEG, PNG, GIF)!';
            $class = 'error';
        } elseif ($status === 'file_too_large') {
            $message = 'ข้อผิดพลาด: ขนาดไฟล์ใหญ่เกินไป (สูงสุด 5MB)!';
            $class = 'error';
        } elseif ($status === 'upload_error') {
            $message = 'ข้อผิดพลาดในการอัปโหลดไฟล์ หรือไม่ใช่รูปภาพ! (โปรดตรวจสอบ PHP Error Log หากยังพบปัญหา)';
            $class = 'error';
        } elseif ($status === 'no_file') {
            $message = 'กรุณาเลือกไฟล์ที่จะอัปโหลด!';
            $class = 'error';
        }
    }

    // แสดงข้อความสถานะ
    if ($message) {
        echo "<div class='message {$class}'>{$message}</div>";
    }

    // แสดงผลลัพธ์ OCR
    if (!empty($ocr_result)) {
        echo "<div class='ocr-result'>";
        echo "<h3>ผลลัพธ์ OCR:</h3>";
        // ใช้ htmlspecialchars เพื่อป้องกัน XSS และ nl2br เพื่อแสดงบรรทัดใหม่
        echo "<div class='ocr-text'>" . htmlspecialchars($ocr_result) . "</div>";
        echo "</div>";
    }

    // แสดงรูปภาพที่อัปโหลด
    if (!empty($uploaded_image_path) && file_exists($uploaded_image_path)) {
        echo "<div class='uploaded-image-preview'>";
        echo "<h3>รูปภาพที่อัปโหลด:</h3>";
        echo "<img src='" . htmlspecialchars($uploaded_image_path) . "' alt='Uploaded Image'>";
        echo "</div>";
    }
    ?>
</div>
</body>
</html>