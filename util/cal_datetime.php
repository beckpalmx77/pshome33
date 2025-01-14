<?php

$start_time = new DateTime('08:00');
$end_time = new DateTime('17:00');

// Calculate the difference between the two times
$interval = $start_time->diff($end_time);

// Get the duration in hours and minutes
$hours = $interval->h;
$minutes = $interval->i;

echo "Duration: " . $hours . " hours and " . $minutes . " minutes.";


