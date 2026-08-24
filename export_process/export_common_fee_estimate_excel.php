<?php
// export_process/export_common_fee_estimate_excel.php
session_start();
require_once __DIR__ . '/../config/connect_db.php';

date_default_timezone_set('Asia/Bangkok');

$year = isset($_REQUEST["year"]) ? (int)$_REQUEST["year"] : (int)date('Y');
$custom_target = isset($_REQUEST["custom_target"]) && is_numeric($_REQUEST["custom_target"]) ? floatval($_REQUEST["custom_target"]) : 0;

if ($year <= 0) {
    echo "<script>alert('กรุณาระบุปีให้ถูกต้อง'); window.history.back();</script>";
    exit();
}

$month_names_th = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

try {
    // 1. ดึงเป้าหมายรวมค่าส่วนกลางต่อเดือน
    $sql_master = "SELECT 
                    SUM(COALESCE(common_fee, 0)) AS monthly_target_default,
                    COUNT(DISTINCT house_number) AS total_houses
                   FROM ims_house_master";
    $query_master = $conn->prepare($sql_master);
    $query_master->execute();
    $master_res = $query_master->fetch(PDO::FETCH_ASSOC);

    $monthly_target_default = (float)($master_res['monthly_target_default'] ?? 0);
    $total_houses = (int)($master_res['total_houses'] ?? 0);

    $effective_monthly_target = ($custom_target > 0) ? $custom_target : $monthly_target_default;

    // 2. ดึงยอดจัดเก็บจริง
    $sql_actual = "SELECT 
        m.month_id AS month_num,
        COALESCE(SUM(
            CASE
                WHEN p.period_month_to = p.period_month_start THEN p.amount
                WHEN p.period_month_to > p.period_month_start THEN ROUND(p.amount / (p.period_month_to - p.period_month_start + 1), 2)
                ELSE 0
            END
        ), 0) AS actual_amount,
        COUNT(DISTINCT p.house_number) AS paid_houses
    FROM (
        SELECT 1 AS month_id UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 
        UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
    ) AS m
    LEFT JOIN ims_house_payment p 
        ON p.period_year = :year 
       AND p.payment_status = 'Y'
       AND m.month_id BETWEEN p.period_month_start AND p.period_month_to
    GROUP BY m.month_id
    ORDER BY m.month_id ASC";

    $stmt_actual = $conn->prepare($sql_actual);
    $stmt_actual->execute([':year' => $year]);
    $actual_rows = $stmt_actual->fetchAll(PDO::FETCH_ASSOC);

    $actual_map = [];
    foreach ($actual_rows as $row) {
        $actual_map[$row['month_num']] = [
            'actual_amount' => (float)$row['actual_amount'],
            'paid_houses' => (int)$row['paid_houses']
        ];
    }

    $filename = "common_fee_estimate_report_" . $year . "_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

    // Header Title Rows
    fputcsv($output, ["รายงานสรุปเปรียบเทียบประมาณการและยอดจัดเก็บค่าส่วนกลาง ประจำปี พ.ศ. " . ($year + 543)]);
    fputcsv($output, ["เป้าหมายจัดเก็บต่อเดือน: " . number_format($effective_monthly_target, 2) . " บาท (รวม " . $total_houses . " หลัง)"]);
    fputcsv($output, []); // Blank row

    // Table Column Headers
    $csv_headers = [
        "เดือน",
        "จำนวนบ้านที่ชำระ (หลัง)",
        "จำนวนบ้านทั้งหมด (หลัง)",
        "เป้าหมายประมาณการ (บาท)",
        "ยอดจัดเก็บจริง (บาท)",
        "ผลต่าง ยอดจริง-เป้าหมาย (+/- บาท)",
        "อัตราการจัดเก็บ (%)",
        "สถานะการจัดเก็บ"
    ];
    fputcsv($output, $csv_headers);

    $total_target_year = $effective_monthly_target * 12;
    $total_actual_year = 0;
    $total_paid_houses = 0;

    for ($i = 1; $i <= 12; $i++) {
        $actual_amt = isset($actual_map[$i]) ? $actual_map[$i]['actual_amount'] : 0.00;
        $paid_h = isset($actual_map[$i]) ? $actual_map[$i]['paid_houses'] : 0;
        
        $diff_amt = $actual_amt - $effective_monthly_target;
        $rate = $effective_monthly_target > 0 ? ($actual_amt / $effective_monthly_target) * 100 : 0;
        
        $total_actual_year += $actual_amt;
        $total_paid_houses += $paid_h;

        if ($rate >= 100) {
            $status = 'บรรลุเป้าหมาย';
        } elseif ($rate >= 80) {
            $status = 'ใกล้เคียงเป้าหมาย';
        } else {
            $status = 'ต่ำกว่าเป้าหมาย';
        }

        $diff_str = ($diff_amt >= 0 ? '+' : '') . number_format($diff_amt, 2);

        $csv_row = [
            $month_names_th[$i],
            $paid_h,
            $total_houses,
            number_format($effective_monthly_target, 2),
            number_format($actual_amt, 2),
            $diff_str,
            number_format($rate, 2) . '%',
            $status
        ];
        fputcsv($output, $csv_row);
    }

    // Summary Total Row
    $total_diff_year = $total_actual_year - $total_target_year;
    $total_rate = $total_target_year > 0 ? ($total_actual_year / $total_target_year) * 100 : 0;
    if ($total_rate >= 100) {
        $total_status = 'บรรลุเป้าหมาย';
    } elseif ($total_rate >= 80) {
        $total_status = 'ใกล้เคียงเป้าหมาย';
    } else {
        $total_status = 'ต่ำกว่าเป้าหมาย';
    }
    $total_diff_str = ($total_diff_year >= 0 ? '+' : '') . number_format($total_diff_year, 2);

    fputcsv($output, []);
    fputcsv($output, [
        'สรุปรวมทั้งปี',
        '-',
        $total_houses,
        number_format($total_target_year, 2),
        number_format($total_actual_year, 2),
        $total_diff_str,
        number_format($total_rate, 2) . '%',
        $total_status
    ]);

    fclose($output);
    exit;

} catch (Exception $e) {
    echo "เกิดข้อผิดพลาดในการส่งออก Excel: " . $e->getMessage();
    exit();
}
?>
