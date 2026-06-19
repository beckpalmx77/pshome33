<?php
include('../config/connect_db.php');
date_default_timezone_set('Asia/Bangkok');

// รับค่าจาก POST
$months = isset($_POST["months"]) ? $_POST["months"] : [];
$year = isset($_POST["year"]) ? (int)$_POST["year"] : 0;
$soi = isset($_POST["soi"]) ? trim($_POST["soi"]) : '';
$house_no = isset($_POST["house_no"]) ? trim($_POST["house_no"]) : '';

// ตรวจสอบค่าปี ถ้าไม่ถูกต้องจบเลย
if ($year <= 0) {
    exit("กรุณาเลือกปีให้ถูกต้อง");
}

// ตรวจสอบเดือน ถ้าไม่มีค่า หรือไม่ใช่ array ให้ตั้งค่าเป็น all
if (!is_array($months) || count($months) == 0) {
    $months = ['all'];
}

// กำหนดชื่อไฟล์ export
$month_label = (in_array('all', $months)) ? 'allmonths' : implode('-', $months);
$filename = "receive-" . $month_label . "-" . $year . "_" . date('Ymd_His') . ".csv";

// ตั้งค่า header สำหรับส่งไฟล์ CSV
header('Content-Type: text/csv; charset=TIS-620');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// สร้าง SQL base query และเงื่อนไข
$sql = "SELECT 
            doc_id,
            payment_date,
            month_name_start,
            month_name_to,
            period_year,
            house_number,
            detail,
            amount,
            created_at,
            payment_status,
            alley,
            period_month_start,
            period_month_to
        FROM v_ims_house_payment
        WHERE period_year = :year";
$params = [':year' => $year];

// กรองเดือนเมื่อไม่เลือก all
if (!in_array('all', $months)) {
    // แปลง array string เป็น int และหาค่าต่ำสุดและสูงสุดของเดือนที่เลือก
    $months_int = array_map('intval', $months);
    $min_month = min($months_int);
    $max_month = max($months_int);

    // กรองช่วงเดือน (เดือนในงวดเริ่มต้นและสิ้นสุดต้องครอบคลุมช่วงนี้)
    $sql .= " AND period_month_start <= :max_month AND period_month_to >= :min_month";
    $params[':max_month'] = $max_month;
    $params[':min_month'] = $min_month;
}

// กรอง soi หากมี
if ($soi !== '') {
    $sql .= " AND alley LIKE :soi";
    $params[':soi'] = "%$soi%";
}

// กรอง house_no หากมี
if ($house_no !== '') {
    $sql .= " AND house_number LIKE :house_no";
    $params[':house_no'] = "%$house_no%";
}

$sql .= " ORDER BY period_year, STR_TO_DATE(payment_date, '%d-%m-%Y'), created_at";

// *** Debug โค้ด ***
//file_put_contents('debug_sql.txt', $sql . "\n\n" . print_r($params, true));

// เตรียม query
$query = $conn->prepare($sql);

// bind parameters
foreach ($params as $key => $value) {
    if (is_int($value)) {
        $query->bindValue($key, $value, PDO::PARAM_INT);
    } else {
        $query->bindValue($key, $value, PDO::PARAM_STR);
    }
}

// ตรวจสอบ error ก่อน execute
if (!$query) {
    $errorInfo = $conn->errorInfo();
    //file_put_contents('debug_sql_error.txt', "Prepare statement error:\n" . print_r($errorInfo, true));
    exit("Prepare statement error");
}

$execResult = $query->execute();

if (!$execResult) {
    $errorInfo = $query->errorInfo();
    //file_put_contents('debug_sql_error.txt', "Execute error:\n" . print_r($errorInfo, true));
    exit("Execute query error");
}

// กำหนดหัวข้อ CSV
$header = [
    "วันที่ชำระ",
    "เลขที่เอกสาร",
    "เดือนเริ่ม",
    "เดือนสิ้นสุด",
    "ปี",
    "บ้านเลขที่",
    "รายละเอียด",
    "จำนวนเงิน",
    "เวลาที่บันทึก",
    "สถานะ",
    "ซอย"
];

// สร้างไฟล์ CSV (output)
$output = fopen('php://output', 'w');

// เขียน header แปลงเป็น TIS-620
fputcsv($output, array_map(function ($item) {
    return iconv('UTF-8', 'TIS-620//IGNORE', $item);
}, $header));

// เขียนข้อมูลทีละแถว
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $line = [
        $row['payment_date'],
        $row['doc_id'],
        $row['month_name_start'],
        $row['month_name_to'],
        $row['period_year'],
        $row['house_number'],
        $row['detail'],
        $row['amount'],
        $row['created_at'],
        $row['payment_status']==="Y"?"ยืนยันการชำระ":"ยังไม่ยืนยันการชำระ",
        $row['alley'],
    ];

    // แปลงข้อมูลเป็น TIS-620 ก่อนเขียน
    $line_tis = array_map(function ($item) {
        return iconv('UTF-8', 'TIS-620//IGNORE', $item);
    }, $line);

    fputcsv($output, $line_tis);
}

fclose($output);
exit;
