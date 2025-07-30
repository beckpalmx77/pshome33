<?php
session_start(); // เพิ่ม session_start หากจำเป็นต้องใช้ session
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
include('../config/connect_db.php');
include('../util/number_to_thai_text.php'); // ยังคง include ไว้เผื่อต้องใช้ในอนาคต

date_default_timezone_set('Asia/Bangkok');

// รับค่าจาก POST (เป็นเดือนเดียวแล้ว)
$month = isset($_POST["month"]) ? (int)$_POST["month"] : 0; // รับเป็นค่าเดียว
$year = isset($_POST["year"]) ? (int)$_POST["year"] : 0;

// ตรวจสอบค่าปีและเดือน ถ้าไม่ถูกต้องจบเลย
if ($year <= 0 || $month <= 0 || $month > 12) {
    echo "<script>alert('กรุณาเลือกเดือนและปีให้ถูกต้อง'); window.history.back();</script>";
    exit();
}

// กำหนดชื่อไฟล์ PDF
$filename_prefix = "payment-installment-report-" . $month . "-" . $year; // ใช้หมายเลขเดือนเดียว

// Define month names for header display
$month_names_th = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

// กำหนดข้อความสำหรับแสดงช่วงรายงาน
$selected_months_text = $month_names_th[$month]; // ใช้ชื่อเดือนเดียว
$report_period_text = 'งวดเดือน ' . $selected_months_text . ' ปี ' . $year;


// --- SQL Query Construction ---
// Note: The original query was using 'v_ims_house_payment' which is a view.
// Based on the provided SQL files, we will now directly query 'ims_installment' and 'v_ims_installment_detail'.
// We need to join these two tables to get the required information.

$sql = "
SELECT
    id.payment_date,
    id.installment_number,
    id.amount_due,
    id.principal_per_installment,
    id.interest_per_installment,
    id.payment_method,
    id.amount_paid,
    id.status AS detail_status,
    i.house_number,
    i.detail AS installment_detail,
    i.start_date,
    i.principal_amount,
    i.interest_rate,
    i.num_installments,
    i.installment_per_period,
    i.status AS installment_status
FROM
    v_ims_installment_detail id
JOIN
    ims_installment i ON id.installment_id = i.installment_id
WHERE
    id.payment_year = :year AND id.payment_month = :month_selected
ORDER BY
    i.house_number, id.installment_number;
";

// Parameters for the query
$params = [
    ':year' => $year,
    ':month_selected' => sprintf('%02d', $month) // Format month as 2 digits for STRFTIME
];

/*
$myfile = fopen("a_permission.txt", "w") or die("Unable to open file!");
fwrite($myfile, " Row sql = " . $sql . " | Month  " . $month  . " | Year " . $year );
fclose($myfile);
*/

/*
// แก้ไขส่วนนี้เพื่อบันทึกหมายเลขเดือนลงในไฟล์ a_permission.txt
$myfile = fopen("a_permission.txt", "w") or die("Unable to open file!");
fwrite($myfile, "SQL: " . $sql . "\n");
fwrite($myfile, "Month received: " . $month . "\n"); // แสดงหมายเลขเดือนที่ส่งมา
fwrite($myfile, "Year received: " . $year . "\n");
fwrite($myfile, "Parameters: " . print_r($params, true) . "\n"); // แสดงค่าพารามิเตอร์ที่ใช้ใน query
fclose($myfile);
*/

try {
    $query = $conn->prepare($sql);
    $query->execute($params); // Pass parameters to execute
    $payment_data = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

if (empty($payment_data)) {
    echo "<script>alert('ไม่พบข้อมูลรายงานค่าผ่อนชำระในช่วงเดือน " . $selected_months_text . " ปี " . $year . " โปรดตรวจสอบเงื่อนไข'); window.history.back();</script>";
    exit();
}

// กำหนดหัวตารางและคอลัมน์กว้าง (ใช้สำหรับสร้าง HTML table ใน PDF)
$pdf_headers = [
    "ลำดับที่",
    "วันที่ชำระ",
    "งวดที่", // Changed from "เดือน"
    "ปี",
    "บ้านเลขที่",
    "เงินต้น", // Added based on new schema
    "ดอกเบี้ย", // Added based on new schema
    "จำนวนเงิน (รวม)", // Changed from "จำนวนเงิน"
    "วิธีการชำระ",
    "สถานะ" // Added based on new schema
];
$col_widths = [
    '6%', '12%', '7%', '6%', '7%', '10%', '10%', '12%', '15%', '15%'
]; // Adjust widths to sum up to 100% for A4 portrait


// --- Extend TCPDF เพื่อสร้าง Header และ Footer ของตัวเอง ---
class MYPDF extends TCPDF
{
    protected $report_period_text;
    protected $logo_path = '../img/logo/ps33-rec-logo-1xx.png'; // Path to your logo image

    // Constructor
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
    }

    // Method to set report period text
    public function setReportPeriod($period_text)
    {
        $this->report_period_text = $period_text;
    }

    // Page header: This method is called automatically for each new page
    public function Header()
    {
        $page_width = $this->getPageWidth();
        $margin_left = 10;
        $margin_right = 10;
        $header_start_y = 10;

        $page_right_edge = $page_width - $margin_right;

        // --- 1. Logo (ซ้ายบน) ---
        $logo_x = $margin_left;
        $logo_y = $header_start_y;
        $logo_width = 20;
        $logo_height = 10;
        if (file_exists($this->logo_path)) {
            $this->Image($this->logo_path, $logo_x, $logo_y, $logo_width, $logo_height, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            $this->SetFont('THSarabunNew', 'B', 10);
            $this->SetXY($logo_x, $logo_y + ($logo_height / 4));
            $this->Cell($logo_width, $logo_height / 2, 'No Logo', 0, 0, 'C');
        }

        // --- 2. Report Title & Period (อยู่กลางหน้ากระดาษ ในระดับเดียวกับโลโก้) ---
        $text_y_center_aligned_with_logo = $logo_y;

        $this->SetFont('THSarabunNew', 'B', 16);
        $title_text = 'รายงานการชำระเงินผ่อนชำระ'; // Changed title
        $combined_text = $title_text . ' ' . $this->report_period_text;
        $combined_width = $this->GetStringWidth($combined_text);

        $center_x = ($page_width / 2) - ($combined_width / 2);
        if ($center_x < ($logo_x + $logo_width + 5)) {
            $center_x = $logo_x + $logo_width + 5;
        }

        $this->SetXY($center_x, $text_y_center_aligned_with_logo);
        $this->Cell(0, $logo_height, $combined_text, 0, 0, 'L', 0, '', 0, false, 'M', 'M');


        // --- 3. Print Date/Time (มุมขวาบน) ---
        $this->SetFont('THSarabunNew', '', 9);
        $print_date_text = 'วันที่พิมพ์: ' . date('d/m/Y H:i:s');
        $print_date_width = $this->GetStringWidth($print_date_text);

        $this->SetXY($page_right_edge - $print_date_width, $text_y_center_aligned_with_logo);
        $this->Cell($print_date_width, $logo_height, $print_date_text, 0, 0, 'R', 0, '', 0, false, 'M', 'M');

        // --- 4. กำหนด Y สำหรับเนื้อหาหลัก (ตาราง) ---
        $final_header_y = $logo_y + $logo_height;
        $this->SetY($final_header_y + 3);
    }

    // Page footer
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('THSarabunNew', '', 8);
        $this->Cell(0, 10, 'หน้า ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'L');
    }
}

// --- TCPDF Initialization ---
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false); // แนวตั้ง 'P'

$pdf->setReportPeriod($report_period_text);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('รายงานการชำระเงินผ่อนชำระ ' . $selected_months_text . ' ปี ' . $year); // Updated title
$pdf->SetSubject('รายงานสรุปการผ่อนชำระ'); // Updated subject

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// กำหนด Margin ด้านบนให้พอดีกับ Header
$top_margin_for_header = 22;

$pdf->SetMargins(10, $top_margin_for_header, 10); // (Left, Top, Right)

// Set auto page break
$pdf->SetAutoPageBreak(TRUE, 15); // Auto break ด้วย margin ด้านล่าง 15mm

// Set main font for the document content
$pdf->SetFont('THSarabunNew', '', 10);

// Add the first page. The Header() method will be called automatically here.
$pdf->AddPage();

// --- สร้าง HTML สำหรับทั้งตาราง (รวม <thead/> และ <tbody/>) ---
$html_table = '<table border="1" cellspacing="0" cellpadding="4" style="font-size:9pt; width: 100%;">
    <thead>
        <tr style="background-color:#f2f2f2;">';

foreach ($pdf_headers as $index => $header_text) {
    $html_table .= '<th width="' . $col_widths[$index] . '" align="center"><b>' . $header_text . '</b></th>';
}
$html_table .= '</tr>
    </thead>
    <tbody>';

$grand_total_amount = 0;
$line_no = 0;
foreach ($payment_data as $row) {
    $amount_for_month = (float)($row['principal_per_installment'] ?? 0) + (float)($row['interest_per_installment'] ?? 0); // Calculate total amount per installment
    $grand_total_amount += $amount_for_month;
    $line_no++;
    $html_table .= '<tr>';
    $html_table .= '<td width="' . $col_widths[0] . '">' . ($line_no) . '</td>';
    $html_table .= '<td width="' . $col_widths[1] . '">' . ($row['payment_date'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[2] . '" align="center">' . ($row['installment_number'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[3] . '" align="center">' . $year . '</td>'; // Assuming the year from input is the payment year
    $html_table .= '<td width="' . $col_widths[4] . '">' . ($row['house_number'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[5] . '" align="right">' . number_format($row['principal_per_installment'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[6] . '" align="right">' . number_format($row['interest_per_installment'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[7] . '" align="right">' . number_format($amount_for_month, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[8] . '">' . ($row['payment_method'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[9] . '">' . ($row['detail_status'] ?? '') . '</td>'; // Display installment detail status
    $html_table .= '</tr>';
}

$html_table .= '<tr>
    <td colspan="7" align="right"><b>รวมยอดการชำระทั้งสิ้น:</b></td>
    <td width="' . $col_widths[7] . '" align="right"><b>' . number_format($grand_total_amount, 2) . '</b></td>
    <td colspan="2"></td>
</tr>';

$html_table .= '</tbody></table>';

$pdf->writeHTML($html_table, true, false, true, false, '');

if (function_exists('number_to_thai_text') && $grand_total_amount > 0) {
    $pdf->SetY($pdf->GetY() + 5);
    $pdf->SetFont('THSarabunNew', '', 10);
    $pdf->writeHTMLCell(0, 0, '', '', '<p style="text-align: left;"><b>ตัวอักษร:</b> ' . number_to_thai_text($grand_total_amount) . '</p>', 0, 1, 0, true, '', true);
}

$filename = $filename_prefix . "_" . date('Ymd_His') . ".pdf";
$pdf->Output($filename, 'I');
exit;