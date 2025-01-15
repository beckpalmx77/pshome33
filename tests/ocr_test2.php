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

    // กำหนดตัวแปรสำหรับเก็บข้อมูล
    $payerName = null;
    $amount = null;

    // ค้นหาชื่อคนโอนและยอดเงินจากแต่ละบรรทัด
    foreach ($lines as $line) {
        // ค้นหาบรรทัดที่มีคำว่า "นาง", "นางสาว", "นาย" สำหรับชื่อคนโอน
        if (preg_match('/(นาง|นางสาว|นาย)\s+([A-Za-zก-ฮ]+)/', $line, $matches)) {
            $payerName = $matches[2]; // ชื่อคนโอน
        }

        // ค้นหาบรรทัดที่มีคำว่า "จำนวนเงิน" หรือ "ยอดเงิน"
        //if (preg_match('/(จำนวนเงิน|ยอดเงิน|โอนเงินสําเร็จ)\s*([\d,]+)/', $line, $matches)) {
        if (preg_match('/(โอนเงินสําเร็จ)\s+([A-Za-zก-ฮ]+)/', $line, $matches)) {
            $amount = preg_replace('/,/', '', $matches[2]); // ตัดตัวคั่นเลข, ออก
        }
    }

    // แสดงข้อมูลที่ดึงได้
    if ($payerName !== null) {
        echo "ตัวแปร 'payerName' : " . htmlspecialchars($payerName) . "<br>";
    } else {
        echo "ไม่พบชื่อคนโอนในข้อความที่อ่านได้<br>";
    }

    if ($amount !== null) {
        echo "ตัวแปร 'amount' : " . htmlspecialchars($amount) . "<br>";
    } else {
        echo "ไม่พบยอดเงินในข้อความที่อ่านได้<br>";
    }

} catch (Exception $e) {
    // แสดงข้อผิดพลาด
    echo "Error: " . $e->getMessage();
}
