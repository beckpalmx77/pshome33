<?php
include('../config/connect_db.php');

date_default_timezone_set('Asia/Bangkok');

//$myCheckValue = $_POST["myCheckValue"];
$month = $_POST["month"];
$year = $_POST["year"];

$filename = "receive" . "-" . $month . "-" . $year . "_" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

$select_query_daily = "  SELECT * FROM v_ims_house_payment ";

/*
if ($myCheckValue === 'Y') {
    $select_where_daily = " WHERE period_year = " . $year;
} else {
    $select_where_daily = " WHERE period_month_start = " . $month . " AND period_year = " . $year;
}
*/

$select_where_daily = " WHERE payment_month = " . $month . " AND period_year = " . $year;

$select_group_order = " ORDER BY period_year,period_month_start ";


$String_Sql = $select_query_daily . $select_where_daily . $select_group_order;

/*
$my_file = fopen("D-sac_str_return.txt", "w") or die("Unable to open file!");
fwrite($my_file,$String_Sql);
fclose($my_file);
*/

$data = "วันที่ทำรายการ,งวด-เดือน,งวดปี,บ้านเลขที่,ผู้ชำระเงิน,จำนวนเงิน(บาท),วันที่ทำรายการ,สถานะชำระ\n";

$query = $conn->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() >= 1) {
    foreach ($results as $result) {

        $payment_status_desc =  $result->payment_status==="Y"? "ยืนยัน":"รอการยืนยัน";

        $data .= " " . $result->payment_date . ",";
        $data .= " " . $result->month_name_start . " - " . $result->month_name_to . ",";
        $data .= " " . $result->period_year . ",";
        $data .= " " . $result->house_number . ",";
        $data .= " " . $result->detail . ",";
        $data .= " " . $result->amount . ",";
        $data .= " " . $result->created_at . ",";
        $data .= " " . $payment_status_desc . "\n";

        //$data .= str_replace(",", "^", $row['WL_CODE']) . "\n";
    }

}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();
