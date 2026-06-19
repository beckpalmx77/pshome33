<?php

include('../config/connect_db.php');
date_default_timezone_set('Asia/Bangkok');

// รับค่าจาก POST
$start_date_str = isset($_POST["start_date"]) ? trim($_POST["start_date"]) : '';
$end_date_str = isset($_POST["end_date"]) ? trim($_POST["end_date"]) : '';

// ตรวจสอบข้อมูลวันที่
if ($start_date_str == '' || $end_date_str == '') {
    exit("กรุณาเลือก 'เริ่มต้นวันที่' และ 'ถึงวันที่' ให้ถูกต้อง");
}

// แปลงวันที่สำหรับชื่อไฟล์ (เพื่อให้ชื่อไฟล์อ่านง่าย)
$start_date_for_filename = DateTime::createFromFormat('d-m-Y', $start_date_str)->format('Y-m-d');
$end_date_for_filename = DateTime::createFromFormat('d-m-Y', $end_date_str)->format('Y-m-d');

// ตั้งชื่อไฟล์
$filename = "expenses-" . $start_date_for_filename . "_to_" . $end_date_for_filename . "_" . date('Ymd_His') . ".csv";

// Header สำหรับดาวน์โหลด
header('Content-Type: text/csv; charset=TIS-620');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// สร้าง SQL
// ใช้ STR_TO_DATE เพื่อแปลง expense_date จาก 'DD-MM-YYYY' ใน DB ให้เป็น DATE type สำหรับการเปรียบเทียบ
// และแปลง :start_date, :end_date ที่ส่งมา (ในรูปแบบ 'DD-MM-YYYY') ให้เป็น DATE type เช่นกัน
$sql = "SELECT * FROM v_ims_expenses 
        WHERE CASE 
            WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
            WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
            ELSE NULL 
        END BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') AND STR_TO_DATE(:end_date, '%d-%m-%Y')
        ORDER BY CASE 
            WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
            WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
            ELSE NULL 
        END ASC"; // เรียงตามวันที่ที่ถูกต้อง

$params = [
    ':start_date' => $start_date_str, // ส่งวันที่ในรูปแบบ DD-MM-YYYY
    ':end_date' => $end_date_str    // ส่งวันที่ในรูปแบบ DD-MM-YYYY
];

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