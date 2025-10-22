<?php
include('../config/connect_db.php');
date_default_timezone_set('Asia/Bangkok');

// รับค่าจาก POST
$year = isset($_POST["year"]) ? trim($_POST["year"]) : '';
$month = isset($_POST["month"]) ? trim($_POST["month"]) : '';

// ตรวจสอบข้อมูลปี
if ($year == '') {
    exit("กรุณาเลือกปีให้ถูกต้อง");
}

// ตั้งชื่อไฟล์
$filename = "expenses-" . $month . "-" . $year . "_" . date('Ymd_His') . ".csv";

// Header สำหรับดาวน์โหลด
header('Content-Type: text/csv; charset=TIS-620');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// สร้าง SQL
$sql = "SELECT 
            doc_id,
            receipt_name,
            expense_date,
            exp_month,
            exp_year,
            inv,
            category_id,
            description,
            qty,
            unit_id,
            amount,
            remark,
            approve_status,
            created_at,
            payment_method
        FROM ims_expenses
        WHERE 1=1";

$params = [];

if ($year !== '') {
    $sql .= " AND exp_year = :year";
    $params[':year'] = $year;
}

if ($month !== '') {
    $sql .= " AND exp_month = :month";
    $params[':month'] = $month;
}

$sql .= " ORDER BY expense_date DESC";

// เตรียม query
$query = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $query->bindValue($key, $value, PDO::PARAM_STR);
}
$query->execute();

// เขียน header ของไฟล์ CSV
$header = [
    "เลขที่เอกสาร",
    "ชื่อใบเสร็จ",
    "วันที่ใช้จ่าย",
    "เดือน",
    "ปี",
    "เลขที่ใบแจ้งหนี้",
    "หมวดหมู่",
    "รายละเอียด",
    "จำนวน",
    "หน่วย",
    "จำนวนเงิน",
    "หมายเหตุ",
    "สถานะอนุมัติ",
    "วันที่บันทึก",
    "วิธีชำระเงิน"
];

$output = fopen('php://output', 'w');
fputcsv($output, array_map(fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', $item), $header));

// เขียนข้อมูล
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $line = [
        $row['doc_id'],
        $row['receipt_name'],
        $row['expense_date'],
        $row['exp_month'],
        $row['exp_year'],
        $row['inv'],
        $row['category_id'],
        $row['description'],
        $row['qty'],
        $row['unit_id'],
        $row['amount'],
        $row['remark'],
        $row['approve_status'] === "Y" ? "อนุมัติแล้ว" : "รออนุมัติ",
        $row['created_at'],
        $row['payment_method']
    ];

    fputcsv($output, array_map(fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', $item), $line));
}

fclose($output);
exit;
