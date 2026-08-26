<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

include(__DIR__ . '/../config/connect_db.php');

$house_number = isset($_POST['house_number']) ? trim($_POST['house_number']) : '';
$period_year = isset($_POST['period_year']) ? intval($_POST['period_year']) : intval(date('Y'));
$period_month_start = isset($_POST['period_month_start']) ? intval($_POST['period_month_start']) : 0;
$period_month_to = isset($_POST['period_month_to']) ? intval($_POST['period_month_to']) : 0;

if (empty($house_number) || $period_year <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ข้อมูลบ้านเลขที่หรือปีงวดไม่ถูกต้อง',
        'has_overlap' => false,
        'months_status' => array_fill(1, 12, ['status' => 'available', 'label' => 'ว่าง'])
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$thaiMonths = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

// 1. ดึงข้อมูลการชำระเงินทั้งหมดของบ้านเลขที่นี้ในปีที่ระบุ
$sql = "SELECT id, doc_id, payment_date, period_month_start, period_month_to, period_year, amount, payment_status 
        FROM ims_house_payment 
        WHERE house_number = :house_number AND period_year = :period_year 
        ORDER BY period_month_start ASC, id ASC";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':house_number' => $house_number,
    ':period_year' => $period_year
]);
$existing_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. สร้าง Map สถานะของทั้ง 12 เดือน (1 = Jan ... 12 = Dec)
$months_status = [];
for ($m = 1; $m <= 12; $m++) {
    $months_status[$m] = [
        'month' => $m,
        'month_name' => $thaiMonths[$m],
        'status' => 'available', // available, paid, pending
        'status_text' => 'ว่าง',
        'doc_id' => '',
        'amount' => 0
    ];
}

foreach ($existing_payments as $payment) {
    $p_start = intval($payment['period_month_start']);
    $p_to = intval($payment['period_month_to']);
    $p_status = $payment['payment_status']; // 'Y' or 'N'
    $doc_id = $payment['doc_id'];
    $amt = floatval($payment['amount']);

    $status_key = ($p_status === 'Y') ? 'paid' : 'pending';
    $status_text = ($p_status === 'Y') ? 'ชำระแล้ว' : 'รอตรวจสอบ';

    for ($m = $p_start; $m <= $p_to; $m++) {
        if ($m >= 1 && $m <= 12) {
            $months_status[$m] = [
                'month' => $m,
                'month_name' => $thaiMonths[$m],
                'status' => $status_key,
                'status_text' => $status_text,
                'doc_id' => $doc_id,
                'amount' => $amt
            ];
        }
    }
}

// 3. ตรวจสอบการทับซ้อน (Overlap) ถ้ามีการส่ง period_month_start / to มา
$has_overlap = false;
$overlap_type = ''; // 'paid' or 'pending'
$overlap_details = [];
$overlap_message = '';

if ($period_month_start > 0 && $period_month_to > 0) {
    if ($period_month_to < $period_month_start) {
        $period_month_to = $period_month_start;
    }

    foreach ($existing_payments as $payment) {
        $p_start = intval($payment['period_month_start']);
        $p_to = intval($payment['period_month_to']);

        // Interval Overlap Formula: (New_Start <= Existing_To) AND (New_End >= Existing_Start)
        if ($period_month_start <= $p_to && $period_month_to >= $p_start) {
            $has_overlap = true;
            $p_status = $payment['payment_status'];
            $overlap_type = ($p_status === 'Y') ? 'paid' : 'pending';

            $start_name = $thaiMonths[$p_start] ?? $p_start;
            $to_name = $thaiMonths[$p_to] ?? $p_to;
            $period_desc = ($p_start == $p_to) ? "งวดเดือน {$start_name}" : "งวดเดือน {$start_name} ถึง {$to_name}";

            $overlap_details[] = [
                'id' => $payment['id'],
                'doc_id' => $payment['doc_id'],
                'period_month_start' => $p_start,
                'period_month_to' => $p_to,
                'period_desc' => $period_desc,
                'payment_status' => $p_status,
                'amount' => $payment['amount'],
                'payment_date' => $payment['payment_date']
            ];
        }
    }

    if ($has_overlap) {
        $first_conflict = $overlap_details[0];
        if ($first_conflict['payment_status'] === 'Y') {
            $overlap_message = "พบข้อมูลซ้ำซ้อน: บ้านเลขที่ {$house_number} มีรายการชำระค่าส่วนกลาง {$first_conflict['period_desc']} ปี {$period_year} เรียบร้อยแล้ว (เลขที่เอกสาร: {$first_conflict['doc_id']})";
        } else {
            $overlap_message = "พบรายการรอตรวจสอบ: บ้านเลขที่ {$house_number} มีรายการโอนเงิน {$first_conflict['period_desc']} ปี {$period_year} อยู่ระหว่างรอเจ้าหน้าที่ตรวจสอบ (เลขที่เอกสาร: {$first_conflict['doc_id']})";
        }
    }
}

echo json_encode([
    'status' => 'success',
    'house_number' => $house_number,
    'period_year' => $period_year,
    'has_overlap' => $has_overlap,
    'overlap_type' => $overlap_type,
    'overlap_message' => $overlap_message,
    'overlap_details' => $overlap_details,
    'months_status' => array_values($months_status),
    'existing_records_count' => count($existing_payments)
], JSON_UNESCAPED_UNICODE);
