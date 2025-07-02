<?php
session_start();
include('../config/connect_db.php');

date_default_timezone_set('Asia/Bangkok');

$month = isset($_POST["month"]) ? (int)$_POST["month"] : 0;
$year = isset($_POST["year"]) ? (int)$_POST["year"] : 0;

if ($year <= 0 || $month <= 0 || $month > 12) {
    echo "<script>alert('กรุณาเลือกเดือนและปีให้ถูกต้อง'); window.history.back();</script>";
    exit();
}

$month_names_th = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

$selected_months_text = $month_names_th[$month];
$report_period_text = 'งวดเดือน ' . $selected_months_text . ' ปี ' . $year;

$sql = "
SELECT
    doc_id,
    payment_date,
    house_number,
    alley,
    detail,
    period_month_start,    
    period_month_to,
    month_name_start,    
    month_name_to,    
    period_year,
    payment_method,
    amount,
    common_fee,
    CASE
        WHEN period_month_to = period_month_start THEN amount
        WHEN period_month_to > period_month_start THEN ROUND(amount / (period_month_to - period_month_start + 1), 2)
        ELSE 0 
    END AS amount_for_month,
    payment_status_desc
FROM
    v_ims_house_payment
WHERE
    period_year = :year AND :month_selected BETWEEN period_month_start AND period_month_to 
ORDER BY
    CAST(alley AS UNSIGNED), house_number; 
";

try {
    $query = $conn->prepare($sql);
    $query->bindParam(':year', $year, PDO::PARAM_INT);
    $query->bindParam(':month_selected', $month, PDO::PARAM_INT);
    $query->execute();
    $payment_data = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

if (empty($payment_data)) {
    echo "<script>alert('ไม่พบข้อมูลรายงานค่าส่วนกลางในช่วงเดือน " . $selected_months_text . " ปี " . $year . " โปรดตรวจสอบเงื่อนไข'); window.history.back();</script>";
    exit();
}

// --- กำหนด Headers สำหรับการดาวน์โหลดไฟล์ Excel (CSV) ---
$filename = "payment_report_" . $month . "_" . $year . "_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// เพิ่ม UTF-8 BOM สำหรับ Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// กำหนดหัวข้อคอลัมน์สำหรับ CSV
$csv_headers = [
    "วันที่ชำระ",
    "เดือน",
    "ปี",
    "บ้านเลขที่",
    "รายละเอียด",
    "ค่าส่วนกลาง",
    "จำนวนเงิน",
    "วิธีการชำระ"
];
fputcsv($output, $csv_headers);

$grand_total_amount = 0;

foreach ($payment_data as $row) {
    $grand_total_amount += (float)($row['amount_for_month'] ?? 0);

    $csv_row = [
        $row['payment_date'] ?? '',
        $selected_months_text ?? '',
        $row['period_year'] ?? '',
        $row['house_number'] ?? '',
        $row['detail'] ?? '',
        number_format($row['common_fee'] ?? 0, 2),
        number_format($row['amount_for_month'] ?? 0, 2),
        $row['payment_method'] ?? ''
    ];
    fputcsv($output, $csv_row);
}

// เพิ่มแถวรวมยอด
fputcsv($output, [
    '', '', '', '', '',
    'รวมยอดการชำระทั้งสิ้น:',
    number_format($grand_total_amount, 2),
    ''
]);

fclose($output);
exit;