<?php
// กำหนด Content Type เป็น JSON
header('Content-Type: application/json');

// --- Configuration and Utilities ---
// !!! ใช้ไฟล์เชื่อมต่อฐานข้อมูล PDO ที่มีอยู่แล้วของคุณ !!!
include('../config/connect_db.php');

// ตรวจสอบการเชื่อมต่อ
if (!isset($conn) || !($conn instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['error' => 'การเชื่อมต่อฐานข้อมูลล้มเหลว']);
    exit();
}

date_default_timezone_set('Asia/Bangkok');

// --- Input Handling ---
$year = isset($_REQUEST["year"]) ? (int)$_REQUEST["year"] : 0;

if ($year <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณาระบุปีที่ต้องการสรุป']);
    exit();
}

$month_names_th = [
    1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
    5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
    9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
];

// ------------------------------------------------------------------
// --- ส่วนที่ 1: ดึงข้อมูลและคำนวณยอดแบบกระจายเดือน (SELECT) ---
// ------------------------------------------------------------------
$sql_select = "
SELECT 
    m.month_id AS month_index,
    COALESCE(SUM(
        -- สูตรคำนวณ: ปัดเศษแต่ละรายก่อนนำมารวม (เพื่อให้ตรงกับเอกสารรายงาน PDF/Excel)
        CASE
            WHEN p.period_month_to = p.period_month_start THEN p.amount
            WHEN p.period_month_to > p.period_month_start THEN ROUND(p.amount / (p.period_month_to - p.period_month_start + 1), 2)
            ELSE 0
        END
    ), 0) AS total_amount
FROM 
    (
        -- สร้างตารางจำลองเดือน 1 ถึง 12 เพื่อให้ได้ข้อมูลครบทุกเดือน
        SELECT 1 AS month_id UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 
        UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
    ) AS m
LEFT JOIN 
    ims_house_payment p 
ON 
    p.period_year = :year 
    AND p.payment_status = 'Y'
    -- เงื่อนไขสำคัญ: เช็คว่าเดือน (m) อยู่ในช่วงที่จ่ายเงิน (Start ถึง To)
    AND m.month_id BETWEEN p.period_month_start AND p.period_month_to
GROUP BY 
    m.month_id
ORDER BY 
    m.month_id ASC;
";

try {
    // ดึงข้อมูลมาพักไว้ในตัวแปร PHP
    $query = $conn->prepare($sql_select);
    $query->execute([':year' => $year]);
    $summary_data = $query->fetchAll(PDO::FETCH_ASSOC);

    // สร้าง Map ข้อมูล: Key = month_index, Value = total_amount
    $data_map = array_column($summary_data, 'total_amount', 'month_index');

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการคำนวณข้อมูล: ' . $e->getMessage()]);
    exit();
}

// ------------------------------------------------------------------
// --- ส่วนที่ 2: บันทึกข้อมูลลง Table (INSERT / UPDATE) ---
// ------------------------------------------------------------------

// SQL: ถ้ามี (year, month) ซ้ำ ให้ Update ยอดเงินและเวลา
$sql_save = "
    INSERT INTO ims_house_payment_monthly_summary 
    (report_year, report_month, total_amount, updated_at) 
    VALUES (:yr, :mn, :amt, NOW())
    ON DUPLICATE KEY UPDATE 
        total_amount = :amt_update, 
        updated_at = NOW()
";

$chart_data = [];

try {
    // เริ่ม Transaction เพื่อความสมบูรณ์ของข้อมูล (บันทึกครบ 12 เดือน หรือไม่บันทึกเลย)
    $conn->beginTransaction();

    $stmt_save = $conn->prepare($sql_save);

    for ($i = 1; $i <= 12; $i++) {
        // ดึงยอดจาก Map (ถ้าไม่มีให้เป็น 0) และปัดทศนิยม 2 ตำแหน่ง
        $amount = isset($data_map[$i]) ? round((float)$data_map[$i], 2) : 0.00;

        // Execute SQL บันทึกลงฐานข้อมูล
        $stmt_save->execute([
            ':yr' => $year,
            ':mn' => $i,
            ':amt' => $amount,       // ค่าสำหรับ Insert
            ':amt_update' => $amount // ค่าสำหรับ Update (กรณีข้อมูลซ้ำ)
        ]);

        // เก็บข้อมูลใส่ Array เพื่อส่งกลับไปแสดงผล JSON
        $chart_data[] = [
            'month' => $i,
            'month_name' => $month_names_th[$i] . ' ' . ($year + 543),
            'total_amount' => $amount
        ];
    }

    // ยืนยันการบันทึก (Commit)
    $conn->commit();

} catch (PDOException $e) {
    // ถ้า Error ให้ยกเลิกทั้งหมด (Rollback)
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()]);
    exit();
}

// ------------------------------------------------------------------
// --- ส่งออกข้อมูล JSON ---
// ------------------------------------------------------------------
echo json_encode([
    'status' => 'success',
    'year' => $year,
    'report_title' => 'สรุปยอดรวมการชำระค่าส่วนกลางรายเดือน ปี พ.ศ. ' . ($year + 543),
    'message' => 'คำนวณและบันทึกข้อมูลเรียบร้อยแล้ว',
    'data' => $chart_data
]);
exit;