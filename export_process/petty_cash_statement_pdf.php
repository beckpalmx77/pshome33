<?php
session_start();
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
include('../config/connect_db.php');
include('../util/number_to_thai_text.php');

date_default_timezone_set('Asia/Bangkok');

$start_date_str = isset($_POST["start_date"]) ? trim($_POST["start_date"]) : '';
$end_date_str = isset($_POST["end_date"]) ? trim($_POST["end_date"]) : '';

if ($start_date_str == '' || $end_date_str == '') {
    echo "<script>alert('กรุณาเลือก \"เริ่มต้นวันที่\" และ \"ถึงวันที่\" ให้ถูกต้อง'); window.history.back();</script>";
    exit();
}

$start_date_for_filename = DateTime::createFromFormat('d-m-Y', $start_date_str)->format('Y-m-d');
$end_date_for_filename = DateTime::createFromFormat('d-m-Y', $end_date_str)->format('Y-m-d');
$filename_prefix = "petty-cash-statement-" . $start_date_for_filename . "_to_" . $end_date_for_filename;

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
    die("เกิดข้อผิดพลาดในการคำนวณยอดยกมา: " . $e->getMessage());
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
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

// กำหนดหัวตารางและคอลัมน์กว้าง
$pdf_headers = [
    "วันที่", "เลขที่เอกสาร", "ประเภท", "รายละเอียด", "รับจาก/จ่ายให้", "รับเข้า (บาท)", "จ่ายออก (บาท)", "คงเหลือ (บาท)"
];
$col_widths = [
    '10%', '15%', '8%', '27%', '16%', '8%', '8%', '8%'
];

// --- Extend TCPDF เพื่อสร้าง Header และ Footer ---
class MYPDF extends TCPDF {
    protected $report_start_date;
    protected $report_end_date;
    protected $logo_path = '../img/logo/ps33-rec-logo-1xx.png';

    public function setReportDates($start_date, $end_date) {
        $this->report_start_date = $start_date;
        $this->report_end_date = $end_date;
    }

    public function Header() {
        $page_width = $this->getPageWidth();
        $margin_left = 10;
        $margin_right = 10;
        $page_right_edge = $page_width - $margin_right;
        $header_start_y = 10;

        // 1. Logo
        $logo_x = $margin_left;
        $logo_y = $header_start_y;
        $logo_width = 20;
        $logo_height = 10;
        if (file_exists($this->logo_path)) {
            $this->Image($this->logo_path, $logo_x, $logo_y, $logo_width, $logo_height, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            $this->SetFont('THSarabunNew', 'B', 10);
            $this->SetXY($logo_x, $logo_y + ($logo_height / 4));
            $this->Cell($logo_width, $logo_height/2, 'No Logo', 0, 0, 'C');
        }

        // 2. Title
        $this->SetFont('THSarabunNew', 'B', 16);
        $title_text = 'รายงานสรุปความเคลื่อนไหวเงินสดย่อย (Petty Cash Statement)';
        $date_range_text = 'วันที่ ' . $this->report_start_date . ' - ' . $this->report_end_date;

        $combined_text = $title_text . '   ' . $date_range_text;
        $combined_width = $this->GetStringWidth($combined_text);
        $center_x = ($page_width / 2) - ($combined_width / 2);
        if ($center_x < ($logo_x + $logo_width + 5)) {
            $center_x = $logo_x + $logo_width + 5;
        }

        $this->SetXY($center_x, $logo_y);
        $this->Cell(0, $logo_height, $combined_text, 0, 0, 'L', 0, '', 0, false, 'M', 'M');

        // 3. Print Date
        $this->SetFont('THSarabunNew', '', 9);
        $print_date_text = 'วันที่พิมพ์: ' . date('d/m/Y H:i:s');
        $print_date_width = $this->GetStringWidth($print_date_text);
        $this->SetXY($page_right_edge - $print_date_width, $logo_y);
        $this->Cell($print_date_width, $logo_height, $print_date_text, 0, 0, 'R', 0, '', 0, false, 'M', 'M');

        $final_header_y = $logo_y + $logo_height;
        $this->SetY($final_header_y + 3);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('THSarabunNew', '', 8);
        $this->Cell(0, 10, 'หน้า ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'L');
    }
}

// TCPDF Setup
$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setReportDates($start_date_str, $end_date_str);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('รายงานเคลื่อนไหวเงินสดย่อย วันที่ ' . $start_date_str . ' ถึง ' . $end_date_str);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$top_margin_for_header = 22;
$pdf->SetMargins(10, $top_margin_for_header, 10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('THSarabunNew', '', 10);
$pdf->AddPage();

// HTML Table Generation
$html_table = '<table border="1" cellspacing="0" cellpadding="4" style="font-size:9pt; width: 100%;">
    <thead>
        <tr style="background-color:#e9ecef; font-weight:bold;">';
foreach ($pdf_headers as $index => $header_text) {
    $html_table .= '<th width="' . $col_widths[$index] . '" align="center"><b>' . $header_text . '</b></th>';
}
$html_table .= '</tr>
    </thead>
    <tbody>';

// 1. แถวยอดยกมา (Opening Balance)
$html_table .= '<tr style="background-color:#f1f3f5;">
    <td width="' . $col_widths[0] . '" align="center">-</td>
    <td width="' . $col_widths[1] . '" align="center">-</td>
    <td width="' . $col_widths[2] . '" align="center">ยอดยกมา</td>
    <td width="' . $col_widths[3] . '">ยอดสะสมยกมาก่อนช่วงวันที่กรอง</td>
    <td width="' . $col_widths[4] . '" align="center">-</td>
    <td width="' . $col_widths[5] . '" align="right">-</td>
    <td width="' . $col_widths[6] . '" align="right">-</td>
    <td width="' . $col_widths[7] . '" align="right"><b>' . number_format($opening_balance, 2) . '</b></td>
</tr>';

$running_balance = $opening_balance;
$total_inflow = 0;
$total_outflow = 0;

foreach ($statement_data as $row) {
    $inflow = (float)$row['inflow'];
    $outflow = (float)$row['outflow'];
    $total_inflow += $inflow;
    $total_outflow += $outflow;
    $running_balance = $running_balance + $inflow - $outflow;

    // แปลงวันที่
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

    $html_table .= '<tr>';
    $html_table .= '<td width="' . $col_widths[0] . '" align="center">' . $txn_date_formatted . '</td>';
    $html_table .= '<td width="' . $col_widths[1] . '" align="center">' . ($row['doc_id'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[2] . '" align="center">' . $type_text . '</td>';
    $html_table .= '<td width="' . $col_widths[3] . '">' . ($row['description'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[4] . '">' . ($row['contact_person'] ?? '-') . '</td>';
    $html_table .= '<td width="' . $col_widths[5] . '" align="right">' . ($inflow > 0 ? number_format($inflow, 2) : '-') . '</td>';
    $html_table .= '<td width="' . $col_widths[6] . '" align="right">' . ($outflow > 0 ? number_format($outflow, 2) : '-') . '</td>';
    $html_table .= '<td width="' . $col_widths[7] . '" align="right">' . number_format($running_balance, 2) . '</td>';
    $html_table .= '</tr>';
}

// แถวสรุปงวด
$html_table .= '<tr style="background-color:#e9ecef; font-weight:bold;">
    <td colspan="5" align="right"><b>รวมประจำงวด (' . $start_date_str . ' - ' . $end_date_str . '):</b></td>
    <td width="' . $col_widths[5] . '" align="right"><b>' . number_format($total_inflow, 2) . '</b></td>
    <td width="' . $col_widths[6] . '" align="right"><b>' . number_format($total_outflow, 2) . '</b></td>
    <td width="' . $col_widths[7] . '" align="right"><b>' . number_format($running_balance, 2) . '</b></td>
</tr>';

$html_table .= '</tbody></table>';

$pdf->writeHTML($html_table, true, false, true, false, '');

if (function_exists('number_to_thai_text') && $running_balance > 0) {
    $pdf->SetY($pdf->GetY() + 5);
    $pdf->SetFont('THSarabunNew', '', 10);
    $pdf->writeHTMLCell(0, 0, '', '', '<p style="text-align: left;"><b>ยอดคงเหลือปลายงวดตัวอักษร:</b> ' . number_to_thai_text($running_balance) . '</p>', 0, 1, 0, true, '', true);
}

$filename = $filename_prefix . "_" . date('Ymd_His') . ".pdf";
$pdf->Output($filename, 'I');
exit;
