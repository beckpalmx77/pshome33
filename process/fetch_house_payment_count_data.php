<?php
// process/fetch_house_payment_count_data.php
require_once '../config/connect_db.php';

$year = isset($_POST['year']) ? $_POST['year'] : date('Y');

// SQL ดึงข้อมูลสรุปจำนวนบ้านและจำนวนเงินที่จ่ายรายซอยแยกตามเดือน
$sql = "SELECT 
            b.alley,
            SUM(CASE WHEN a.amount_period_month_1 > 0 THEN 1 ELSE 0 END) AS count_month_1,
            SUM(COALESCE(a.amount_period_month_1, 0)) AS sum_month_1,
            SUM(CASE WHEN a.amount_period_month_2 > 0 THEN 1 ELSE 0 END) AS count_month_2,
            SUM(COALESCE(a.amount_period_month_2, 0)) AS sum_month_2,
            SUM(CASE WHEN a.amount_period_month_3 > 0 THEN 1 ELSE 0 END) AS count_month_3,
            SUM(COALESCE(a.amount_period_month_3, 0)) AS sum_month_3,
            SUM(CASE WHEN a.amount_period_month_4 > 0 THEN 1 ELSE 0 END) AS count_month_4,
            SUM(COALESCE(a.amount_period_month_4, 0)) AS sum_month_4,
            SUM(CASE WHEN a.amount_period_month_5 > 0 THEN 1 ELSE 0 END) AS count_month_5,
            SUM(COALESCE(a.amount_period_month_5, 0)) AS sum_month_5,
            SUM(CASE WHEN a.amount_period_month_6 > 0 THEN 1 ELSE 0 END) AS count_month_6,
            SUM(COALESCE(a.amount_period_month_6, 0)) AS sum_month_6,
            SUM(CASE WHEN a.amount_period_month_7 > 0 THEN 1 ELSE 0 END) AS count_month_7,
            SUM(COALESCE(a.amount_period_month_7, 0)) AS sum_month_7,
            SUM(CASE WHEN a.amount_period_month_8 > 0 THEN 1 ELSE 0 END) AS count_month_8,
            SUM(COALESCE(a.amount_period_month_8, 0)) AS sum_month_8,
            SUM(CASE WHEN a.amount_period_month_9 > 0 THEN 1 ELSE 0 END) AS count_month_9,
            SUM(COALESCE(a.amount_period_month_9, 0)) AS sum_month_9,
            SUM(CASE WHEN a.amount_period_month_10 > 0 THEN 1 ELSE 0 END) AS count_month_10,
            SUM(COALESCE(a.amount_period_month_10, 0)) AS sum_month_10,
            SUM(CASE WHEN a.amount_period_month_11 > 0 THEN 1 ELSE 0 END) AS count_month_11,
            SUM(COALESCE(a.amount_period_month_11, 0)) AS sum_month_11,
            SUM(CASE WHEN a.amount_period_month_12 > 0 THEN 1 ELSE 0 END) AS count_month_12,
            SUM(COALESCE(a.amount_period_month_12, 0)) AS sum_month_12,
            SUM(COALESCE(a.total, 0)) AS total_amount,
            COUNT(DISTINCT b.house_number) AS total_houses
        FROM ims_house_master b
        LEFT JOIN ims_house_payment_split_monthly_summary a 
            ON a.house_number = b.house_number AND a.period_year = :year
        GROUP BY b.alley
        ORDER BY CAST(b.alley AS UNSIGNED) ASC";

$stmt = $conn->prepare($sql);
$stmt->execute([':year' => $year]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($data as $row) {
    // ป้องกันค่า NULL และจัดข้อความแสดงผลซอย
    $row['alley'] = !empty($row['alley']) ? 'ซอย ' . $row['alley'] : 'ซอย -';
    $result[] = $row;
}

echo json_encode(['data' => $result]);
?>
