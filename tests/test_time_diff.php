<?php
include('../config/connect_db.php');

$time_start = "18:00";
$time_to = "20:00";


$sql_time = "SELECT TIMEDIFF('". $time_to . "','" . $time_start ."') AS data ";
foreach ($conn->query($sql_time) AS $row) {
    $tim_diff = $row['data'];
    echo $tim_diff ;
}



// String representing the first date and time
$dateString1 = '2023-06-28 15:30';

// String representing the second date and time
$dateString2 = '2023-06-29 12:45';

// Create DateTime objects from the strings
$dateTime1 = new DateTime($dateString1);
$dateTime2 = new DateTime($dateString2);

// Calculate the difference between the two DateTime objects
$interval = $dateTime1->diff($dateTime2);

// Output the difference
echo "\n\r Difference: " . $interval->format('%d days, %h hours, %i minutes, %s seconds');

$date_leave_start = "29-06-2023";
$datetime_leave_start_cal = substr($date_leave_start,6) . "-" . substr($date_leave_start,3,2) . "-" . substr($date_leave_start,0,2);
echo "\n\r time = " .$datetime_leave_start_cal;