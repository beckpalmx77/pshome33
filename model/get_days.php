<?php
if (isset($_POST['month']) && isset($_POST['year'])) {
    $month = intval($_POST['month']);
    $year = intval($_POST['year']);
    $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    echo "📆 เดือน $month ปี $year มี <strong>$days</strong> วัน";
} else {
    echo "กรุณาเลือกเดือนและปีให้ครบ";
}