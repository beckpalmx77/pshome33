<?php

function Calculate_Time($dateString1, $dateString2)
{

// Create DateTime objects from the strings
    $dateTime1 = new DateTime($dateString1);
    $dateTime2 = new DateTime($dateString2);

// Calculate the difference between the two DateTime objects
    $interval = $dateTime1->diff($dateTime2);

// Output the difference
    //echo "\n\r Difference: " . $interval->format('%d days, %h hours, %i minutes, %s seconds');

    $total_time = $interval->format('%d:%h:%i');

    $date_leave_start = "29-06-2023";
    $datetime_leave_start_cal = substr($date_leave_start,6) . "-" . substr($date_leave_start,3,2) . "-" . substr($date_leave_start,0,2);
    //echo "\n\r time = " . $datetime_leave_start_cal . " | " . $total_time;
    echo "\n\r time diff = " . $total_time;

    // return $total_time;

}