<?php
include('../config/connect_db.php');

date_default_timezone_set('Asia/Bangkok');

$month = isset($_POST["month"]) ? (int)$_POST["month"] : 0;
$year = isset($_POST["year"]) ? (int)$_POST["year"] : 0;

$filename = "receive-" . $month . "-" . $year . "_" . date('Ymd_His') . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

// สร้าง SQL Query
$select_query_daily = "SELECT * FROM v_ims_house_payment";
$select_where_daily = " WHERE $month BETWEEN period_month_start AND period_month_to AND period_year = $year";
$select_group_order = " ORDER BY period_year, STR_TO_DATE(PAYMENT_DATE, '%d-%m-%Y'), created_at  ";

$String_Sql = $select_query_daily . $select_where_daily . $select_group_order;

// log ไฟล์ SQL ที่รัน (สำหรับ debug)
/*
$my_file = fopen("D-sac_str_return.txt", "w") or die("Unable to open file!");
fwrite($my_file, $String_Sql);
fclose($my_file);
*/

// สร้าง header ของไฟล์ CSV
$data = "วันที่ทำรายการ,งวด-เดือน,งวดปี,บ้านเลขที่,ผู้ชำระเงิน,จำนวนเงิน(บาท),วันที่ทำรายการ,สถานะชำระ\n";

// ดึงข้อมูลจากฐานข้อมูล
$query = $conn->prepare($String_Sql);
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
