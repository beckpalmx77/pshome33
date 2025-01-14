<?php
$date = date("Y/m/d");
$day = date("d");
$month = date("m");
$year = date("Y");

$date_org = "25-11-2019";

$date_start = substr($date_org,6,4) . "-" . substr($date_org,3,2) . "-" . substr($date_org,0,2);
$date_to = $year . "-" . $month . "-" . $day;


echo "date_start = " . $date_start . "\n\r";
echo "date_to = " . $date_to . "\n\r";

$date1 = new DateTime($date_start);
$date2 = new DateTime($date_to);
$interval = $date1->diff($date2);


echo $interval->days . "\n\r";
echo $date  . " | " . $month . " | " . $year . "\n\r";
