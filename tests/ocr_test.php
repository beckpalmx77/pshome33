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

    // กำหนดตัวแปรสำหรับเก็บชื่อคนโอนและยอดเงิน
    $payerName = null;
    $paymentAmount = null;

    // ค้นหาชื่อคนโอนจากแต่ละบรรทัด
    foreach ($lines as $line) {
        // ค้นหาบรรทัดที่มีคำว่า "นาง", "นางสาว", หรือ "นาย" เพื่อหาชื่อคนโอน
        if (preg_match('/(นาง|นางสาว|นาย)\s+[A-Za-zก-ฮ]+/', $line)) {
            preg_match('/(นาง|นางสาว|นาย)\s+([A-Za-zก-ฮ]+)/', $line, $matches);
            if (isset($matches[2])) {
                $payerName = $matches[2];
            }
        }

        // ค้นหายอดเงินจากแต่ละบรรทัด
        if (preg_match('/\d{1,3}(?:,\d{3})*(?:\.\d{2})?/', $line)) {
            preg_match('/\d{1,3}(?:,\d{3})*(?:\.\d{2})?/', $line, $matches);
            if (isset($matches[0])) {
                $paymentAmount = str_replace(',', '', $matches[0]); // ลบเครื่องหมาย ',' ออกจากตัวเลข
            }
        }
    }

    // แสดงค่าตัวแปร
    if ($payerName !== null) {
        echo "ตัวแปร 'payerName' : " . htmlspecialchars($payerName) . "<br>";
    } else {
        echo "ไม่พบชื่อคนโอนในข้อความที่อ่านได้<br>";
    }

    if ($paymentAmount !== null) {
        echo "ตัวแปร 'paymentAmount' : " . htmlspecialchars($paymentAmount) . "<br>";
    } else {
        echo "ไม่พบยอดเงินในข้อความที่อ่านได้<br>";
    }

} catch (Exception $e) {
    // แสดงข้อผิดพลาด
    echo "Error: " . $e->getMessage();
}
