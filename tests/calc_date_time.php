<!DOCTYPE html>
<html>
<body>

<?php

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');


$year = date("Y");
$month = date("m");
$date = date("d");

$last_number = '3';
$number = sprintf('%02s', $last_number);
echo $number. "<br>";

$current_date = $date . "-" . $month . "-" .$year;

for ($year=2020;$year<=2030;$year++) {

    $d = cal_days_in_month(CAL_GREGORIAN, 2, $year);

    echo "There was $d days in February " . $month . " - " . $year . "<br>";
}

$day = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$sql_find = "SELECT * FROM job_payment_month_total WHERE effect_month = '" . $month . "' AND effect_year = '" . $year . "'";

$nRows = $conn->query($sql_find)->fetchColumn();
if ($nRows <= 0) {

    $effect_start_date = "01-" . $month . "-" . $year;
    $effect_to_date = $day . "-" . $month . "-" . $year;
    echo $effect_start_date . " | " .  $effect_to_date . " | " . $current_date;

}

?>

</body>
</html>
