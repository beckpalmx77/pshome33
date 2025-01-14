<?php

function calc_leave($leave_start, $leave_end)
{
    $y_start = date("Y", strtotime($leave_start));
    $m_start = date("m", strtotime($leave_start));
    $d_start = date("d", strtotime($leave_start));
    $y_end = date("Y", strtotime($leave_end));
    $m_end = date("m", strtotime($leave_end));
    $d_end = date("d", strtotime($leave_end));
    $date_total = ((strtotime($y_end . "-" . $m_end . "-" . $d_end) - strtotime($y_start . "-" . $m_start . "-" . $d_start)) / (60 * 60 * 24));
    $h_start = date("H", strtotime($leave_start));
    $h_end = date("H", strtotime($leave_end));
    $h_total = $h_end - $h_start;
    if (($h_start == 8.30) && ($h_end == 17.30)) {
        $h_total = 1;
    } elseif (($h_start < 12) && ($h_end > 12)) {
        $h_total--;
        $h_total = ($h_total * 0.125);
    } elseif ($h_total < 0) {
        $date_total--;
        $h_start = 17.30 - $h_start;
        $h_end = $h_end - 8.30;
        $h_total = ($h_end + $h_start) * 0.125;
    } elseif ($h_total > 0) {
        $h_total = ($h_total * 0.125);
    }
    return $date_total + $h_total;
}

