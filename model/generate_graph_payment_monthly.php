<?php
// กำหนด Content Type เป็น JSON
header('Content-Type: application/json');

// --- Configuration and Utilities ---

// !!! ใช้ไฟล์เชื่อมต่อฐานข้อมูล PDO ที่มีอยู่แล้วของคุณ !!!
// ไฟล์นี้ควรสร้างตัวแปร $conn ที่เป็น Object ของ PDO
include('../config/connect_db.php');

// ตรวจสอบว่าตัวแปร $conn ถูกสร้างขึ้นจริงหรือไม่
if (!isset($conn) || !($conn instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['error' => 'การเชื่อมต่อฐานข้อมูลล้มเหลว: ไม่พบตัวแปร $conn ที่เป็น Object ของ PDO']);
    exit();
}

date_default_timezone_set('Asia/Bangkok');

// --- Input Handling ---
// รับค่าปีจาก Request (POST หรือ GET)
$year = isset($_REQUEST["year"]) ? (int)$_REQUEST["year"] : 0;

// ตรวจสอบค่าปี
if ($year <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'กรุณาระบุปีที่ต้องการสรุป']);
    exit();
}

// Define month names (ใช้ชื่อย่อเพื่อให้แสดงบนกราฟได้ง่าย)
$month_names_th = [
    1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
    5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
    9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
];

// ------------------------------------------------------------------
// --- SQL Query Construction สำหรับสรุปยอดรวมรายเดือน ---
// ------------------------------------------------------------------
$sql = "
SELECT
    period_month_to AS month_index,
    SUM(
        CASE
            -- ถ้าจ่ายเดือนเดียว ยอดคือ amount
            WHEN period_month_to = period_month_start THEN amount
            -- ถ้าจ่ายหลายเดือน หารเฉลี่ยต่อเดือน
            WHEN period_month_to > period_month_start THEN ROUND(amount / (period_month_to - period_month_start + 1), 2)
            ELSE 0 
        END
    ) AS total_amount
FROM
    v_ims_house_payment
WHERE
    period_year = :year
GROUP BY
    period_month_to
ORDER BY
    period_month_to ASC;
";


try {
    $query = $conn->prepare($sql);
    $query->execute([':year' => $year]);
    $summary_data = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()]);
    exit();
}

// ------------------------------------------------------------------
// --- การจัดเตรียมข้อมูลสำหรับกราฟ (เพื่อให้มีครบ 12 เดือนแม้ว่ายอดจะเป็น 0) ---
// ------------------------------------------------------------------
$chart_data = [];
// สร้างแผนที่จากผลลัพธ์: Key = month_index, Value = total_amount
$data_map = array_column($summary_data, 'total_amount', 'month_index');

for ($i = 1; $i <= 12; $i++) {
    // ดึงยอดรวมจากแผนที่ ถ้าไม่มีให้ใช้ 0.00
    $amount = isset($data_map[$i]) ? (float)$data_map[$i] : 0.00;

    // จัดรูปแบบข้อมูลสำหรับ Frontend
    $chart_data[] = [
        'month' => $i,
        'month_name' => $month_names_th[$i] . ' ' . $year,
        'total_amount' => $amount
    ];
}

// ------------------------------------------------------------------
// --- ส่งออกข้อมูลในรูปแบบ JSON ---
// ------------------------------------------------------------------
echo json_encode([
    'year' => $year,
    'report_title' => 'สรุปยอดรวมการชำระค่าส่วนกลางรายเดือน ปี ' . $year,
    'data' => $chart_data
]);
exit;
?>
