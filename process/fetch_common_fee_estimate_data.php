<?php
// process/fetch_common_fee_estimate_data.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/connect_db.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'การเชื่อมต่อฐานข้อมูลล้มเหลว']);
    exit();
}

date_default_timezone_set('Asia/Bangkok');

// ดึงปีที่ต้องการ (หากไม่ส่งมา ให้ใช้ปีปัจจุบัน)
$year = isset($_REQUEST['year']) && (int)$_REQUEST['year'] > 0 ? (int)$_REQUEST['year'] : (int)date('Y');
$custom_target = isset($_REQUEST['custom_target']) && is_numeric($_REQUEST['custom_target']) ? floatval($_REQUEST['custom_target']) : 0;

$month_names_th = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

$month_names_short_th = [
    1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
    5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
    9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
];

try {
    // 1. ดึงเป้าหมายรวมค่าส่วนกลางต่อเดือนจากตาราง ims_house_master
    $sql_master = "SELECT 
                    SUM(COALESCE(common_fee, 0)) AS monthly_target_default,
                    COUNT(DISTINCT house_number) AS total_houses
                   FROM ims_house_master";
    $query_master = $conn->prepare($sql_master);
    $query_master->execute();
    $master_res = $query_master->fetch(PDO::FETCH_ASSOC);

    $monthly_target_default = (float)($master_res['monthly_target_default'] ?? 0);
    $total_houses = (int)($master_res['total_houses'] ?? 0);

    // กำหนดเป้าหมายต่อเดือนที่จะนำมาคำนวณ (ถ้าผู้ใช้กำหนดเอง ให้ใช้ค่าผู้ใช้ หากไม่มีใช้ค่าเริ่มต้น)
    $effective_monthly_target = ($custom_target > 0) ? $custom_target : $monthly_target_default;

    // 2. ดึงยอดจัดเก็บจริงกระจายแต่ละเดือน (ม.ค. - ธ.ค.)
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

    // Map ข้อมูลรายเดือน
    $actual_map = [];
    foreach ($actual_rows as $row) {
        $actual_map[$row['month_num']] = [
            'actual_amount' => (float)$row['actual_amount'],
            'paid_houses' => (int)$row['paid_houses']
        ];
    }

    $months_data = [];
    $total_actual_year = 0;
    $total_target_year = $effective_monthly_target * 12;

    for ($i = 1; $i <= 12; $i++) {
        $actual_amt = isset($actual_map[$i]) ? $actual_map[$i]['actual_amount'] : 0.00;
        $paid_h = isset($actual_map[$i]) ? $actual_map[$i]['paid_houses'] : 0;
        
        $diff_amt = $actual_amt - $effective_monthly_target;
        $achievement_rate = $effective_monthly_target > 0 ? ($actual_amt / $effective_monthly_target) * 100 : 0;
        
        $total_actual_year += $actual_amt;

        // สถานะการจัดเก็บ
        if ($achievement_rate >= 100) {
            $status_code = 'achieved';
            $status_text = 'บรรลุเป้าหมาย';
            $badge_class = 'badge-success';
        } elseif ($achievement_rate >= 80) {
            $status_code = 'near';
            $status_text = 'ใกล้เคียงเป้าหมาย';
            $badge_class = 'badge-warning';
        } else {
            $status_code = 'shortfall';
            $status_text = 'ต่ำกว่าเป้าหมาย';
            $badge_class = 'badge-danger';
        }

        $months_data[] = [
            'month_num' => $i,
            'month_name' => $month_names_th[$i],
            'month_name_short' => $month_names_short_th[$i],
            'month_year_th' => $month_names_short_th[$i] . ' ' . ($year + 543),
            'target_amount' => round($effective_monthly_target, 2),
            'actual_amount' => round($actual_amt, 2),
            'diff_amount' => round($diff_amt, 2),
            'diff_sign' => $diff_amt >= 0 ? '+' : '',
            'paid_houses' => $paid_h,
            'total_houses' => $total_houses,
            'achievement_rate' => round($achievement_rate, 2),
            'status_code' => $status_code,
            'status_text' => $status_text,
            'badge_class' => $badge_class
        ];
    }

    $total_diff_year = $total_actual_year - $total_target_year;
    $total_achievement_rate = $total_target_year > 0 ? ($total_actual_year / $total_target_year) * 100 : 0;

    if ($total_achievement_rate >= 100) {
        $total_status_code = 'achieved';
        $total_status_text = 'บรรลุเป้าหมาย';
        $total_badge_class = 'badge-success';
    } elseif ($total_achievement_rate >= 80) {
        $total_status_code = 'near';
        $total_status_text = 'ใกล้เคียงเป้าหมาย';
        $total_badge_class = 'badge-warning';
    } else {
        $total_status_code = 'shortfall';
        $total_status_text = 'ต่ำกว่าเป้าหมาย';
        $total_badge_class = 'badge-danger';
    }

    echo json_encode([
        'status' => 'success',
        'year' => $year,
        'year_th' => $year + 543,
        'total_houses' => $total_houses,
        'monthly_target_default' => round($monthly_target_default, 2),
        'effective_monthly_target' => round($effective_monthly_target, 2),
        'summary' => [
            'total_target_year' => round($total_target_year, 2),
            'total_actual_year' => round($total_actual_year, 2),
            'total_diff_year' => round($total_diff_year, 2),
            'total_diff_sign' => $total_diff_year >= 0 ? '+' : '',
            'total_achievement_rate' => round($total_achievement_rate, 2),
            'status_code' => $total_status_code,
            'status_text' => $total_status_text,
            'badge_class' => $total_badge_class
        ],
        'months' => $months_data
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
