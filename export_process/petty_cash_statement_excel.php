<?php
include('../config/connect_db.php');
date_default_timezone_set('Asia/Bangkok');

// รับค่าจาก POST
$start_date_str = isset($_POST["start_date"]) ? trim($_POST["start_date"]) : '';
$end_date_str = isset($_POST["end_date"]) ? trim($_POST["end_date"]) : '';

// ตรวจสอบข้อมูลวันที่
if ($start_date_str == '' || $end_date_str == '') {
    exit("กรุณาเลือก 'เริ่มต้นวันที่' และ 'ถึงวันที่' ให้ถูกต้อง");
}

// แปลงวันที่สำหรับชื่อไฟล์
$start_date_for_filename = DateTime::createFromFormat('d-m-Y', $start_date_str)->format('Y-m-d');
$end_date_for_filename = DateTime::createFromFormat('d-m-Y', $end_date_str)->format('Y-m-d');

// ตั้งชื่อไฟล์ CSV (ที่จะเปิดใน Excel)
$filename = "petty-cash-statement-" . $start_date_for_filename . "_to_" . $end_date_for_filename . "_" . date('Ymd_His') . ".csv";

// Header สำหรับดาวน์โหลด CSV รองรับ TIS-620 (Excel ภาษาไทยเปิดได้ตรงๆ)
header('Content-Type: text/csv; charset=TIS-620');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// -------------------------------------------------------------
// 1. คำนวณยอดยกมาก่อนวันที่เริ่มต้น (Opening Balance)
// -------------------------------------------------------------
$total_in_open = 0;
$total_out_open = 0;

try {
    // 1.1 ยอดรับเข้าสะสมก่อน start_date
    $sql_in_open = "SELECT SUM(amount) AS total_in FROM ims_petty_cash 
                    WHERE status = 'Y' AND transaction_type = '+' 
                    AND doc_date < STR_TO_DATE(:start_date, '%d-%m-%Y')";
    $stmt_in_open = $conn->prepare($sql_in_open);
    $stmt_in_open->execute([':start_date' => $start_date_str]);
    $res_in_open = $stmt_in_open->fetch(PDO::FETCH_ASSOC);
    $total_in_open = (float)($res_in_open['total_in'] ?? 0);

    // 1.2 ยอดจ่ายออกสะสมก่อน start_date
    $sql_out_open = "SELECT SUM(amount) AS total_out FROM ims_expenses 
                     WHERE petty_cash_status = 'Y' AND approve_status = 'Y' 
                     AND CASE 
                         WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
                         WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
                         ELSE NULL 
                     END < STR_TO_DATE(:start_date, '%d-%m-%Y')";
    $stmt_out_open = $conn->prepare($sql_out_open);
    $stmt_out_open->execute([':start_date' => $start_date_str]);
    $res_out_open = $stmt_out_open->fetch(PDO::FETCH_ASSOC);
    $total_out_open = (float)($res_out_open['total_out'] ?? 0);

} catch (PDOException $e) {
    die("Database Error (Opening Balance): " . $e->getMessage());
}

$opening_balance = $total_in_open - $total_out_open;

// -------------------------------------------------------------
// 2. ดึงข้อมูลรายการเดินบัญชีระหว่างวันที่ (Statement Items)
// -------------------------------------------------------------
$statement_data = [];
try {
    $sql = "SELECT 
                'receipt' AS source_type,
                id AS source_id,
                doc_date AS txn_date,
                doc_no AS doc_id,
                description AS description,
                received_from AS contact_person,
                amount AS inflow,
                0.00 AS outflow
            FROM ims_petty_cash
            WHERE status = 'Y' AND transaction_type = '+'
              AND doc_date BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') AND STR_TO_DATE(:end_date, '%d-%m-%Y')

            UNION ALL

            SELECT 
                'expense' AS source_type,
                id AS source_id,
                CASE 
                    WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
                    WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
                    ELSE NULL 
                END AS txn_date,
                doc_id AS doc_id,
                description AS description,
                receipt_name AS contact_person,
                0.00 AS inflow,
                amount AS outflow
            FROM ims_expenses
            WHERE petty_cash_status = 'Y' AND approve_status = 'Y'
              AND (CASE 
                    WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
                    WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
                    ELSE NULL 
              END BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') AND STR_TO_DATE(:end_date, '%d-%m-%Y'))

            ORDER BY txn_date ASC, doc_id ASC";

    $query = $conn->prepare($sql);
    $query->execute([
        ':start_date' => $start_date_str,
        ':end_date' => $end_date_str
    ]);
    $statement_data = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error (Statement Data): " . $e->getMessage());
}

// หัวตาราง CSV
$header = [
    "ลำดับ",
    "วันที่",
    "เลขที่เอกสาร",
    "ประเภท",
    "รายละเอียด",
    "รับจาก/จ่ายให้",
    "รับเข้า (บาท)",
    "จ่ายออก (บาท)",
    "ยอดคงเหลือสะสม (บาท)"
];

$output = fopen('php://output', 'w');

// เขียนหัวข้อคอลัมน์ (แปลงเป็น TIS-620)
fputcsv($output, array_map(
    fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', is_null($item) ? '' : strval($item)),
    $header
));

// 1. เขียนแถวยอดยกมา (Opening Balance)
$opening_row = [
    "-",
    "ก่อนวันที่ " . $start_date_str,
    "-",
    "ยอดยกมา",
    "ยอดสะสมยกมาก่อนช่วงวันที่กรอง",
    "-",
    "-",
    "-",
    number_format($opening_balance, 2)
];
fputcsv($output, array_map(
    fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', is_null($item) ? '' : strval($item)),
    $opening_row
));

$running_balance = $opening_balance;
$total_inflow = 0;
$total_outflow = 0;
$row_idx = 1;

// 2. เขียนข้อมูลแต่ละแถว
foreach ($statement_data as $row) {
    $inflow = (float)$row['inflow'];
    $outflow = (float)$row['outflow'];
    $total_inflow += $inflow;
    $total_outflow += $outflow;
    $running_balance = $running_balance + $inflow - $outflow;

    // แปลงรูปแบบวันที่
    $txn_date_formatted = '';
    if (!empty($row['txn_date'])) {
        $date_obj = DateTime::createFromFormat('Y-m-d', $row['txn_date']);
        if ($date_obj) {
            $txn_date_formatted = $date_obj->format('d/m/Y');
        } else {
            $txn_date_formatted = $row['txn_date'];
        }
    }

    $type_text = ($row['source_type'] === 'receipt') ? 'รับเข้า' : 'จ่ายออก';

    $line = [
        $row_idx++,
        $txn_date_formatted,
        $row['doc_id'],
        $type_text,
        $row['description'],
        $row['contact_person'] ?: '-',
        $inflow > 0 ? number_format($inflow, 2) : '-',
        $outflow > 0 ? number_format($outflow, 2) : '-',
        number_format($running_balance, 2)
    ];

    fputcsv($output, array_map(
        fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', is_null($item) ? '' : strval($item)),
        $line
    ));
}

// 3. เขียนแถวสรุปยอดรวมประจำงวด
$summary_row = [
    "",
    "",
    "",
    "",
    "รวมประจำงวด (" . $start_date_str . " - " . $end_date_str . ")",
    "",
    number_format($total_inflow, 2),
    number_format($total_outflow, 2),
    number_format($running_balance, 2)
];
fputcsv($output, array_map(
    fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', is_null($item) ? '' : strval($item)),
    $summary_row
));

fclose($output);
exit;
?>
