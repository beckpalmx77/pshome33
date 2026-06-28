<?php

include('../config/connect_db.php');
date_default_timezone_set('Asia/Bangkok');

// รับค่าจาก POST
$year = isset($_POST["year"]) ? trim($_POST["year"]) : '';
$months = isset($_POST["months"]) ? $_POST["months"] : [];

// ตรวจสอบข้อมูลปี
if ($year == '') {
    exit("กรุณาเลือกปีให้ถูกต้อง");
}

// ตรวจสอบว่า $months เป็น array และไม่ว่าง
$monthList = [];
if (is_array($months) && count($months) > 0) {
    $monthList = array_filter($months); // ลบค่าว่าง
}

// ตั้งชื่อไฟล์
$monthText = count($monthList) > 0 ? implode('-', $monthList) : 'all-months';
$filename = "expenses-" . $monthText . "-" . $year . "_" . date('Ymd_His') . ".csv";

// Header สำหรับดาวน์โหลด
header('Content-Type: text/csv; charset=TIS-620');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// สร้าง SQL
$sql = "SELECT * FROM v_ims_expenses WHERE exp_year = :year";

$params = [':year' => $year];

// เงื่อนไขเดือน (ถ้ามี)
if (count($monthList) > 0) {
    $placeholders = [];
    foreach ($monthList as $index => $month) {
        $ph = ':month' . $index;
        $placeholders[] = $ph;
        $params[$ph] = $month;
    }
    $sql .= " AND exp_month IN (" . implode(',', $placeholders) . ")";
}

$sql .= " ORDER BY id";

// เตรียม query
$query = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $query->bindValue($key, $value, PDO::PARAM_STR);
}
$query->execute();

// หัวตาราง CSV
$header = [
    "จ่ายให้ (ผู้ขาย-ผู้รับเหมา)",
    "วันที่ใช้จ่าย",
    "เลขที่เอกสาร",
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

// เขียน CSV
$output = fopen('php://output', 'w');
fputcsv($output, array_map(
    fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', is_null($item) ? '' : strval($item)),
    $header
));

// เขียนข้อมูลแต่ละแถว
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $expense_date_formatted = '';
    if (!empty($row['expense_date'])) {
        $date_obj = DateTime::createFromFormat('Y-m-d', $row['expense_date']);
        if (!$date_obj) {
            $date_obj = DateTime::createFromFormat('d-m-Y', $row['expense_date']);
        }
        if ($date_obj) {
            $expense_date_formatted = $date_obj->format('d/m/Y');
        } else {
            $expense_date_formatted = $row['expense_date'];
        }
    }

    $line = [
        $row['receipt_name'],
        $expense_date_formatted,
        $row['doc_id'],
        $row['exp_month'],
        $row['exp_year'],
        $row['inv'],
        $row['category_name'],
        $row['description'],
        $row['qty'],
        $row['unit_name'],
        $row['amount'],
        $row['remark'],
        $row['approve_status'] === "Y" ? "อนุมัติแล้ว" : "รออนุมัติ",
        $row['created_at'],
        $row['payment_method']
    ];

    fputcsv($output, array_map(
        fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', is_null($item) ? '' : strval($item)),
        $line
    ));
}

fclose($output);
exit;
