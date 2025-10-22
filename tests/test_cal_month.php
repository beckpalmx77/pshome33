<?php
// รับค่าจากฟอร์ม

//$period_month_start = $_POST['period_month_start'];
//$period_month_to = $_POST['period_month_to'];
//$period_year = $_POST['period_year'];

$period_month_start = 7;
$period_month_to = 7;
$period_year = 2025;

// ตรวจสอบเงื่อนไข: ถ้าเลือกเดือนมกราคมถึงธันวาคม (1 ถึง 12)
if ($period_month_start == 1 && $period_month_to == 12) {
    // กำหนดค่า payment_type เป็น 12 ทันที
    $payment_type = 12;
} else {
    // ถ้าไม่ใช่กรณี 1-12 ให้คำนวณจำนวนเดือนปกติ
    // ตรวจสอบเพื่อรองรับกรณีข้ามปี (ถ้ามี)
    if ($period_month_to >= $period_month_start) {
        $payment_type = $period_month_to - $period_month_start + 1;
    } else {
        // กรณีข้ามปี (เช่น เริ่ม ธ.ค. -> สิ้นสุด ม.ค.)
        $payment_type = (12 - $period_month_start) + $period_month_to + 1;
    }
}

// ตัวอย่างการแสดงผลค่า payment_type
echo "จำนวนเดือนที่ต้องชำระคือ: " . $payment_type;

