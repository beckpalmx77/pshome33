<?php
include('../config/connect_db.php');
include('../util/month_util.php');
date_default_timezone_set('Asia/Bangkok');

// รับค่าจาก POST
$year = isset($_POST["year"]) ? trim($_POST["year"]) : '';
$months = isset($_POST["months"]) ? $_POST["months"] : [];

// ตรวจสอบข้อมูลปี
if ($year == '') {
    exit("กรุณาเลือกปีให้ถูกต้อง");
}

// ตรวจสอบว่า $months เป็น array และไม่ว่าง
$monthList = [];
if (is_array($months) && count($months) > 0) {
    // ถ้ามีการเลือก "all" ให้ใช้ทุกเดือน
    if (in_array('all', $months)) {
        $monthList = range(1, 12);
    } else {
        $monthList = array_filter($months, function($v) { return $v !== 'all'; });
    }
}

if (empty($monthList)) {
    exit("กรุณาเลือกเดือน");
}

$monthQueryList = implode(',', array_map('intval', $monthList));

// Aggregating Data (Same logic as process file)
// Inflow
$sql_house = "SELECT MONTH(payment_date) as month, SUM(amount) as total FROM ims_house_payment 
              WHERE payment_status = 'Y' AND YEAR(payment_date) = :year AND MONTH(payment_date) IN ($monthQueryList) GROUP BY MONTH(payment_date)";
$stmt_house = $conn->prepare($sql_house);
$stmt_house->execute(['year' => $year]);
$house_data = $stmt_house->fetchAll(PDO::FETCH_KEY_PAIR);

$sql_rec = "SELECT CAST(rec_month AS UNSIGNED) as month, SUM(amount) as total FROM ims_reciepts 
            WHERE approve_status = 'Y' AND rec_year = :year AND CAST(rec_month AS UNSIGNED) IN ($monthQueryList) GROUP BY rec_month";
$stmt_rec = $conn->prepare($sql_rec);
$stmt_rec->execute(['year' => $year]);
$rec_data = $stmt_rec->fetchAll(PDO::FETCH_KEY_PAIR);

$sql_petty_in = "SELECT MONTH(doc_date) as month, SUM(amount) as total FROM ims_petty_cash 
                 WHERE status = 'Y' AND transaction_type = '+' AND YEAR(doc_date) = :year AND MONTH(doc_date) IN ($monthQueryList) GROUP BY MONTH(doc_date)";
$stmt_petty_in = $conn->prepare($sql_petty_in);
$stmt_petty_in->execute(['year' => $year]);
$petty_in_data = $stmt_petty_in->fetchAll(PDO::FETCH_KEY_PAIR);

// Outflow
$sql_exp = "SELECT CAST(exp_month AS UNSIGNED) as month, SUM(amount) as total FROM ims_expenses 
            WHERE approve_status = 'Y' AND exp_year = :year AND CAST(exp_month AS UNSIGNED) IN ($monthQueryList) GROUP BY exp_month";
$stmt_exp = $conn->prepare($sql_exp);
$stmt_exp->execute(['year' => $year]);
$exp_data = $stmt_exp->fetchAll(PDO::FETCH_KEY_PAIR);

$sql_petty_out = "SELECT MONTH(doc_date) as month, SUM(amount) as total FROM ims_petty_cash 
                  WHERE status = 'Y' AND transaction_type = '-' AND YEAR(doc_date) = :year AND MONTH(doc_date) IN ($monthQueryList) GROUP BY MONTH(doc_date)";
$stmt_petty_out = $conn->prepare($sql_petty_out);
$stmt_petty_out->execute(['year' => $year]);
$petty_out_data = $stmt_petty_out->fetchAll(PDO::FETCH_KEY_PAIR);

// CSV Preparation
$filename = "cash-flow-report-" . $year . "_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=TIS-620');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
$header = ["เดือน/ปี", "รายรับ (Inflow)", "รายจ่าย (Outflow)", "คงเหลือ (Net Cash Flow)"];
fputcsv($output, array_map(fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', $item), $header));

$totalInflow = 0;
$totalOutflow = 0;
$totalNet = 0;

foreach ($monthList as $m) {
    $m = (int)$m;
    $in = ($house_data[$m] ?? 0) + ($rec_data[$m] ?? 0) + ($petty_in_data[$m] ?? 0);
    $out = ($exp_data[$m] ?? 0) + ($petty_out_data[$m] ?? 0);
    $net = $in - $out;

    $totalInflow += $in;
    $totalOutflow += $out;
    $totalNet += $net;

    $line = [
        $month_arr[$m] . " " . $year,
        number_format($in, 2),
        number_format($out, 2),
        number_format($net, 2)
    ];
    fputcsv($output, array_map(fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', $item), $line));
}

// Total Row
$footer = ["รวมทั้งสิ้น", number_format($totalInflow, 2), number_format($totalOutflow, 2), number_format($totalNet, 2)];
fputcsv($output, array_map(fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', $item), $footer));

fclose($output);
exit;
