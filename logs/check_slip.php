<?php
// ---------------------------------------------------------
// 1. ตั้งค่าและเรียกใช้ Library
// ---------------------------------------------------------
// ตรวจสอบว่ามีโฟลเดอร์ vendor หรือไม่ (ต้องผ่านการทำ Composer install มาก่อน)
if (!file_exists('vendor/autoload.php')) {
    die("ไม่พบไฟล์ vendor/autoload.php กรุณารันคำสั่ง 'composer install' หรือ 'composer require khanamiryan/qrcode-detector-decoder' ก่อนครับ");
}

require 'vendor/autoload.php';

use Zxing\QrReader;

// ตัวแปรสำหรับเก็บผลลัพธ์เพื่อแสดงใน HTML
$message = "";
$qrResult = "";
$uploadedFile = "";
$debugImage = "";

// ---------------------------------------------------------
// 2. ฟังก์ชันเตรียมภาพ (Image Pre-processing)
// ---------------------------------------------------------
function prepareImageForQR($sourcePath, $targetPath) {
    if (!file_exists($sourcePath)) return false;

    list($width, $height, $type) = getimagesize($sourcePath);

    // โหลดภาพต้นฉบับตามประเภทไฟล์
    switch ($type) {
        case IMAGETYPE_JPEG: $source = imagecreatefromjpeg($sourcePath); break;
        case IMAGETYPE_PNG: $source = imagecreatefrompng($sourcePath); break;
        case IMAGETYPE_GIF: $source = imagecreatefromgif($sourcePath); break;
        default: return false; // ไม่รองรับไฟล์ประเภทอื่น
    }

    // --- ส่วนที่แก้ไข (Fix float/int warning) ---
    // คำนวณความสูงที่จะตัด (เอาเฉพาะ 45% ล่างสุดของภาพ)
    // ใช้ intval() เพื่อแปลงเป็นจำนวนเต็ม ป้องกัน Error ใน PHP 8.1+
    $cropHeight = intval($height * 0.45);
    $cropY = intval($height - $cropHeight);
    // ----------------------------------------

    // สร้าง Canvas เปล่าขนาดเท่าที่จะตัด
    $processed = imagecreatetruecolor($width, $cropHeight);

    // คัดลอกภาพส่วนล่างมาใส่ใน Canvas ใหม่
    imagecopy($processed, $source, 0, 0, 0, $cropY, $width, $cropHeight);

    // ปรับภาพเป็นขาว-ดำ (Grayscale) เพื่อลด noise สี
    imagefilter($processed, IMG_FILTER_GRAYSCALE);

    // ปรับความคมชัด (Contrast) เพิ่มเล็กน้อยเพื่อให้เส้น QR ชัดขึ้น
    imagefilter($processed, IMG_FILTER_CONTRAST, -10);

    // บันทึกไฟล์ภาพที่ผ่านการประมวลผลแล้ว
    imagejpeg($processed, $targetPath, 90);

    // คืนหน่วยความจำ
    imagedestroy($source);
    imagedestroy($processed);

    return true;
}

// ---------------------------------------------------------
// 3. ส่วนประมวลผลเมื่อมีการ Upload (Main Logic)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['slip_image'])) {

    // กำหนดโฟลเดอร์ปลายทาง
    $targetDir = "uploads/";

    // สร้างโฟลเดอร์ถ้ายังไม่มี
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // ตั้งชื่อไฟล์ (ใช้เวลาปัจจุบันนำหน้าเพื่อกันชื่อซ้ำ)
    $fileName = time() . "_" . basename($_FILES["slip_image"]["name"]);
    $originalFile = $targetDir . $fileName;
    $processedFile = $targetDir . "proc_" . $fileName; // ชื่อไฟล์ภาพที่ตัดแล้ว

    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    $fileType = pathinfo($originalFile, PATHINFO_EXTENSION);

    // ตรวจสอบนามสกุลไฟล์
    if (in_array(strtolower($fileType), $allowTypes)) {
        // อัปโหลดไฟล์ต้นฉบับ
        if (move_uploaded_file($_FILES["slip_image"]["tmp_name"], $originalFile)) {
            $uploadedFile = $originalFile;

            // เรียกฟังก์ชันเตรียมภาพ
            if (prepareImageForQR($originalFile, $processedFile)) {
                $targetForScan = $processedFile;
                $debugImage = $processedFile;
            } else {
                // ถ้าแปลงภาพไม่สำเร็จ ให้ลองอ่านจากภาพต้นฉบับแทน
                $targetForScan = $originalFile;
            }

            // --- เริ่มอ่าน QR Code ---
            try {
                $qrcode = new QrReader($targetForScan);
                $text = $qrcode->text(); // อ่านข้อความ

                if (!empty($text)) {
                    $qrResult = $text;
                    $message = "อ่าน QR Code สำเร็จ!";
                } else {
                    $message = "ไม่พบ QR Code ในภาพนี้ (ภาพอาจไม่ชัดหรือ QR เล็กเกินไป)";
                }
            } catch (Exception $e) {
                $message = "เกิดข้อผิดพลาดในการอ่าน: " . $e->getMessage();
            }

        } else {
            $message = "ขออภัย เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
        }
    } else {
        $message = "อนุญาตเฉพาะไฟล์รูปภาพ (JPG, JPEG, PNG, GIF) เท่านั้น";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบตรวจสอบ QR Slip (Fix Int/Float)</title>
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #007bff; margin-bottom: 20px; }
        .btn-submit { background-color: #28a745; color: white; border: none; padding: 12px 20px; width: 100%; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .btn-submit:hover { background-color: #218838; }
        input[type="file"] { border: 2px dashed #ddd; padding: 20px; width: 100%; box-sizing: border-box; text-align: center; cursor: pointer; }

        /* ผลลัพธ์ */
        .result-section { margin-top: 30px; padding: 20px; background-color: #e9ecef; border-radius: 8px; }
        .success { border-left: 5px solid #28a745; }
        .fail { border-left: 5px solid #dc3545; }
        code { background: #fff; padding: 10px; display: block; word-break: break-all; border: 1px solid #ccc; margin-top: 10px; color: #d63384; font-weight: bold;}

        /* การแสดงรูปภาพ */
        .image-preview { display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap; }
        .img-box { flex: 1; min-width: 250px; text-align: center; border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
        .img-box img { max-width: 100%; height: auto; }
        .img-label { font-weight: bold; margin-bottom: 5px; display: block; color: #555; }
    </style>
</head>
<body>

<div class="container">
    <h2>📸 อัปโหลดสลิปเพื่อดึงโค้ดตรวจสอบ</h2>

    <form action="" method="post" enctype="multipart/form-data">
        <label for="slip_image" style="display:block; margin-bottom:10px; font-weight:bold;">เลือกไฟล์รูปภาพสลิป:</label>
        <input type="file" name="slip_image" id="slip_image" required accept="image/*">
        <button type="submit" class="btn-submit">ตรวจสอบ QR Code</button>
    </form>

    <?php if ($message): ?>
        <div class="result-section <?php echo $qrResult ? 'success' : 'fail'; ?>">
            <h3 style="margin-top:0;"><?php echo $message; ?></h3>

            <?php if ($qrResult): ?>
                <p><strong>ข้อมูลดิบ (Raw Payload) สำหรับนำไปเช็ค API:</strong></p>
                <code><?php echo htmlspecialchars($qrResult ?? ''); ?></code>
                <p style="font-size: 0.9em; color: #666; margin-top: 10px;">
                    * หมายเหตุ: นี่คือรหัสสำหรับตรวจสอบ ไม่ใช่ยอดเงินโดยตรง ท่านต้องนำรหัสนี้ไปส่งให้ Bank API หรือผู้ให้บริการตรวจสอบสลิปอีกที
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($uploadedFile): ?>
        <div class="image-preview">
            <div class="img-box">
                <span class="img-label">รูปต้นฉบับ</span>
                <img src="<?php echo $uploadedFile; ?>" alt="Original Slip">
            </div>

            <?php if ($debugImage): ?>
                <div class="img-box">
                    <span class="img-label">สิ่งที่ระบบอ่าน (Crop & BW)</span>
                    <img src="<?php echo $debugImage; ?>" alt="Processed Slip">
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>