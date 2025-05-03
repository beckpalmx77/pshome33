<?php
include('../config/connect_db.php');

date_default_timezone_set('Asia/Bangkok');

//$myCheckValue = $_POST["myCheckValue"];
$month = $_POST["month"];
$year = $_POST["year"];

$filename = "expense" . "-" . $month . "-" . $year . "_" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

$select_query_daily = "  SELECT * FROM v_ims_expenses ";

/*
if ($myCheckValue === 'Y') {
    $select_where_daily = " WHERE exp_year = " . $year;
} else {
    $select_where_daily = " WHERE exp_month = " . $month . " AND exp_year = " . $year;
}
*/

$select_where_daily = " WHERE exp_month = " . $month . " AND exp_year = " . $year;

$select_group_order = " ORDER BY exp_month,exp_month ";


$String_Sql = $select_query_daily . $select_where_daily . $select_group_order;

//$my_file = fopen("D-sac_str_return.txt", "w") or die("Unable to open file!");
//fwrite($my_file,$String_Sql);
//fclose($my_file);

$data = "วันที่ทำรายการ,เดือน,ปี,รายละเอียด,ประเภท,จำนวน,หน่วยนับ,จำนวนเงิน(บาท),สถานะการอนุมัติ\n";

$query = $conn->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() >= 1) {
    foreach ($results as $result) {

        $approve_status_desc =  $result->approve_status==="Y"? "อนุมัติ":"รอการอนุมัติ";

        $data .= " " . $result->expense_date . ",";
        $data .= " " . $result->month_name . ",";
        $data .= " " . $result->exp_year . ",";
        $data .= " " . $result->description . ",";
        $data .= " " . $result->category_name . ",";
        $data .= " " . $result->qty . ",";
        $data .= " " . $result->unit_name . ",";
        $data .= " " . $result->amount . ",";
        $data .= " " . $approve_status_desc . "\n";

        //$data .= str_replace(",", "^", $row['WL_CODE']) . "\n";
    }

}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();
