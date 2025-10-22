<?php
// เริ่มต้น session เพื่อส่งข้อมูลกลับไปยัง index.php
session_start();

// กำหนด directory ที่จะเก็บไฟล์อัปโหลด
// **สำคัญ:** สร้างโฟลเดอร์ 'uploads' ใน directory เดียวกันกับไฟล์ PHP นี้!
$target_dir = "uploads/";

// ตรวจสอบว่าโฟลเดอร์ uploads มีอยู่หรือไม่ ถ้าไม่มีให้สร้าง
if (!file_exists($target_dir)) {
    // กำหนดสิทธิ์ 0777 สำหรับการทดสอบเท่านั้น! ใน Production ควรใช้ 0755 หรือ 0775
    mkdir($target_dir, 0777, true);
}

// ตรวจสอบว่ามีการส่งไฟล์มาหรือไม่
if (!isset($_FILES["imageFile"]) || $_FILES["imageFile"]["error"] == UPLOAD_ERR_NO_FILE) {
    header("Location: index.php?status=no_file");
    exit();
}

// รับข้อมูลไฟล์ที่อัปโหลด
$file_name = $_FILES["imageFile"]["name"];
$tmp_name = $_FILES["imageFile"]["tmp_name"];
$file_size = $_FILES["imageFile"]["size"];
$file_error = $_FILES["imageFile"]["error"];

$uploadOk = 1; // ตั้งค่าสถานะการอัปโหลด (1 = OK, 0 = Error)

// ดึงนามสกุลไฟล์
$imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// สร้างชื่อไฟล์ใหม่ที่ไม่ซ้ำกันเพื่อความปลอดภัยและป้องกันการทับซ้อน
$new_file_name = uniqid('uploaded_img_') . '.' . $imageFileType; // เช่น uploaded_img_60b7c7b2a6c8e.jpg
$target_file = $target_dir . $new_file_name;

// --- การตรวจสอบความปลอดภัยของไฟล์ ---

// 1. ตรวจสอบว่าไฟล์เป็นรูปภาพจริงหรือไม่
// getimagesize() จะคืนค่า false ถ้าไม่ใช่ไฟล์รูปภาพที่ถูกต้อง
$check = getimagesize($tmp_name);
if($check === false) {
    $uploadOk = 0;
    header("Location: index.php?status=upload_error"); // หรือ status=not_an_image
    exit();
}

// 2. ตรวจสอบขนาดไฟล์ (สูงสุด 5MB)
// 5000000 bytes = 5MB
if ($file_size > 5000000) {
    $uploadOk = 0;
    header("Location: index.php?status=file_too_large");
    exit();
}

// 3. อนุญาตเฉพาะบางประเภทไฟล์เท่านั้น
$allowed_types = array("jpg", "jpeg", "png", "gif");
if(!in_array($imageFileType, $allowed_types)) {
    $uploadOk = 0;
    header("Location: index.php?status=invalid_file_type");
    exit();
}

// 4. ตรวจสอบข้อผิดพลาดจากการอัปโหลดของ PHP
if ($file_error !== UPLOAD_ERR_OK) {
    // สามารถจัดการข้อผิดพลาดตามรหัสได้ละเอียดกว่านี้หากต้องการ
    // เช่น UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE, UPLOAD_ERR_PARTIAL
    $uploadOk = 0;
    header("Location: index.php?status=upload_error");
    exit();
}


// --- ถ้าไม่มีข้อผิดพลาด ให้ดำเนินการอัปโหลดและ OCR ---
if ($uploadOk == 0) {
    // สถานะ error ถูก redirect ไปแล้ว
} else {
    // พยายามย้ายไฟล์ที่อัปโหลดจากตำแหน่งชั่วคราวไปยังตำแหน่งเป้าหมาย
    if (move_uploaded_file($tmp_name, $target_file)) {

        // --- ส่วนการเรียกใช้ Tesseract OCR ---
        // กำหนดชื่อไฟล์สำหรับผลลัพธ์ OCR (จะถูกสร้างในโฟลเดอร์ 'uploads')
        $output_file_base = $target_dir . 'ocr_result_' . uniqid(); // เช่น uploads/ocr_result_60b7c7b2a6c8f

        // กำหนดภาษาที่ต้องการให้ Tesseract อ่าน
        // 'tha' สำหรับภาษาไทย, 'eng' สำหรับภาษาอังกฤษ, 'tha+eng' สำหรับทั้งสองภาษา
        // **สำคัญ:** ต้องติดตั้ง Data Language Pack สำหรับภาษานี้ใน Tesseract แล้ว!
        $language = 'tha+eng';

        // กำหนดพาธของ Tesseract executable
        // ถ้า tesseract.exe (Windows) หรือ tesseract (Linux/macOS) อยู่ใน PATH ของระบบ
        // ก็สามารถใช้ 'tesseract' ได้เลย
        // มิฉะนั้น ให้ระบุพาธเต็ม เช่น:
        // สำหรับ Windows: $tesseract_cmd = '"C:\\Program Files\\Tesseract-OCR\\tesseract.exe"';
        // สำหรับ Linux/macOS: $tesseract_cmd = '/usr/bin/tesseract'; // หรือ /usr/local/bin/tesseract
        $tesseract_cmd = 'tesseract';

        // สร้างคำสั่ง Tesseract เต็มรูปแบบ
        // ใช้ double quotes ครอบพาธไฟล์อินพุตและเอาต์พุต เผื่อมีช่องว่างในชื่อ
        $command = "$tesseract_cmd \"$target_file\" \"$output_file_base\" -l $language";

        // รันคำสั่ง Tesseract ผ่าน PHP's exec()
        // exec(command, [output_array], [return_value]);
        // $output_array จะเก็บทุกบรรทัดของ standard output จากคำสั่ง
        // $return_var จะเก็บรหัสสถานะการออก (0 คือสำเร็จ)
        $output_array = [];
        $return_var = 0;
        exec($command, $output_array, $return_var);

        $ocr_text = '';
        if ($return_var === 0) {
            // Tesseract ทำงานสำเร็จ, อ่านไฟล์ผลลัพธ์ (.txt)
            $ocr_result_file = $output_file_base . '.txt';
            if (file_exists($ocr_result_file)) {
                $ocr_text = file_get_contents($ocr_result_file);
                // **แนะนำ:** ลบไฟล์ OCR ชั่วคราวออกหลังจากอ่านเสร็จ เพื่อไม่ให้เปลืองพื้นที่
                unlink($ocr_result_file);
            } else {
                $ocr_text = "Error: OCR output file was not generated or not found.";
            }
        } else {
            // Tesseract ทำงานล้มเหลว
            $ocr_text = "Error running Tesseract. Return code: {$return_var}. Command: \"{$command}\". Output: " . implode("\n", $output_array);
        }

        // เก็บข้อความ OCR และชื่อไฟล์รูปภาพที่อัปโหลดไว้ใน Session
        // เพื่อให้ index.php สามารถดึงไปแสดงผลได้
        $_SESSION['ocr_result'] = $ocr_text;
        $_SESSION['uploaded_image'] = $new_file_name;

        // **ตัวเลือก:** ลบไฟล์รูปภาพที่อัปโหลดออกหลังจากการ OCR หากไม่ต้องการเก็บรูปภาพต้นฉบับ
        // unlink($target_file);

        // Redirect กลับไปที่ index.php พร้อมสถานะสำเร็จของการ OCR
        header("Location: index.php?status=success_ocr");
        exit();

    } else {
        // เกิดข้อผิดพลาดในการย้ายไฟล์
        header("Location: index.php?status=upload_error");
        exit();
    }
}
?>