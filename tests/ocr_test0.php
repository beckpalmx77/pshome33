<?php
require '../vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

try {
    // ระบุ path ของภาพที่ต้องการอ่าน
    $imagePath = 'slip.jpg';

    // ตรวจสอบว่าไฟล์ภาพมีอยู่หรือไม่
    if (!file_exists($imagePath)) {
        throw new Exception("File not found: " . $imagePath);
    }

    // ใช้ Tesseract OCR อ่านข้อความจากภาพ
    $ocr = new TesseractOCR($imagePath);

    // ระบุ path ของ Tesseract OCR executable
    $ocr->executable('C:\\Program Files\\Tesseract-OCR\\tesseract.exe');

    // ระบุภาษาไทย (ต้องติดตั้ง tesseract-ocr-tha ก่อน)
    $ocr->lang('tha');

    // ดึงข้อความจากภาพ
    $text = $ocr->run();

    // แสดงผลข้อความที่ดึงได้
    echo "Extracted Text from Image: <br>";
    echo nl2br(htmlspecialchars($text)) . "<br>";

    // แยกข้อความเป็นบรรทัด
    $lines = explode("\n", $text);

    // กำหนดตัวแปรสำหรับเก็บยอดเงิน
    $amount = null;

    // ค้นหายอดเงินจากแต่ละบรรทัด
    foreach ($lines as $line) {
        // ค้นหาบรรทัดที่มีคำว่า "ยอดเงิน" หรือรูปแบบตัวเลขที่คล้ายยอดเงิน
        if (strpos($line, 'โอนเงินสําเร็จ') !== false || preg_match('/\d{1,3}(?:,\d{3})*(?:\.\d{1,2})?/', $line, $matches)) {
            if (isset($matches[0])) {
                // ลบเครื่องหมายจุลภาค
                $amount = str_replace(',', '', $matches[0]);
                break; // หยุดเมื่อพบยอดเงิน
            }
        }
    }

    // แสดงชื่อและค่าของตัวแปร
    if ($amount !== null) {
        // ใช้ get_defined_vars() เพื่อดึงชื่อและค่าของตัวแปร
        $variables = get_defined_vars();
        foreach ($variables as $name => $value) {
            if ($value == $amount) {
                echo "ตัวแปร \$".$name.": " . htmlspecialchars($value) . " บาท<br>";
            }
        }
    } else {
        echo "ไม่พบยอดเงินในข้อความที่อ่านได้<br>";
    }

} catch (Exception $e) {
    // แสดงข้อผิดพลาด
    echo "Error: " . $e->getMessage();
}

