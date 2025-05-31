<?php
include('../config/connect_db.php');
date_default_timezone_set('Asia/Bangkok');

// รับค่าแบบ string ก่อน
$month = isset($_POST["month"]) ? $_POST["month"] : '';
$year = isset($_POST["year"]) ? (int)$_POST["year"] : 0;
$soi = isset($_POST["soi"]) ? trim($_POST["soi"]) : '';
$house_no = isset($_POST["house_no"]) ? trim($_POST["house_no"]) : '';

$filename = "receive-" . $month . "-" . $year . "_" . date('Ymd_His') . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

// SQL Query base
$select_query_daily = "SELECT * FROM v_ims_house_payment";
$select_where_daily = " WHERE period_year = $year";

// กรองเดือนเฉพาะเมื่อไม่ใช่ 'all'
if ($month !== 'all') {
    $month_int = (int)$month;
    $select_where_daily .= " AND $month_int BETWEEN period_month_start AND period_month_to";
}

// เพิ่มเงื่อนไขหากมีการกรอก soi
if ($soi !== '') {
    $select_where_daily .= " AND alley LIKE :soi";
}

// เพิ่มเงื่อนไขหากมีการกรอก house_no
if ($house_no !== '') {
    $select_where_daily .= " AND house_number LIKE :house_no";
}

$select_group_order = " ORDER BY period_year, STR_TO_DATE(PAYMENT_DATE, '%d-%m-%Y'), created_at";

$String_Sql = $select_query_daily . $select_where_daily . $select_group_order;

// รวมข้อมูล POST กับ SQL query สำหรับ debug
$debug_text = "POST data:\n";
$debug_text .= "month = " . var_export($month, true) . "\n";
$debug_text .= "year = " . var_export($year, true) . "\n";
$debug_text .= "soi = " . var_export($soi, true) . "\n";
$debug_text .= "house_no = " . var_export($house_no, true) . "\n\n";

$debug_text .= "SQL Query:\n" . $String_Sql;

// เขียน debug ลงไฟล์
// file_put_contents("device_a.txt", $debug_text);

// สร้าง header ของไฟล์ CSV
$data = "วันที่ทำรายการ,งวด-เดือน,งวดปี,บ้านเลขที่,ผู้ชำระเงิน,จำนวนเงิน(บาท),วันที่ทำรายการ,สถานะชำระ\n";

// เตรียม execute
$query = $conn->prepare($String_Sql);

// binding param ถ้ามี
if ($soi !== '') {
    $query->bindValue(':soi', "%$soi%");
}
if ($house_no !== '') {
    $query->bindValue(':house_no', "%$house_no%");
}

$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() >= 1) {
    foreach ($results as $result) {
        $payment_status_desc = ($result->payment_status === "Y") ? "ยืนยัน" : "รอการยืนยัน";

        $data .= " " . $result->payment_date . ",";
        $data .= " " . $result->month_name_start . " - " . $result->month_name_to . ",";
        $data .= " " . $result->period_year . ",";
        $data .= " " . $result->house_number . ",";
        $data .= " " . $result->detail . ",";
        $data .= " " . $result->amount . ",";
        $data .= " " . $result->created_at . ",";
        $data .= " " . $payment_status_desc . "\n";
    }
}

// แปลง encoding และส่งออก
$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();
?>
