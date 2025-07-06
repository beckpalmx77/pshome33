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
$filename_prefix = "payment-report-" . $month . "-" . $year; // ใช้หมายเลขเดือนเดียว

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
$sql_where_parts = ["period_year = :year"];
$params = [':year' => $year];

// เพิ่มเงื่อนไขเดือนเดียว
$sql_where_parts[] = ":month_selected BETWEEN period_month_start AND period_month_to";
$params[':month_selected'] = $month;


$sql = "
SELECT
    doc_id,
    payment_date,
    house_number,
    alley,
    detail,
    period_month_start,    
    period_month_to,
    month_name_start,    
    month_name_to,    
    period_year,
    payment_method,
    amount,
    common_fee,
    CASE
        WHEN period_month_to = period_month_start THEN amount
        WHEN period_month_to > period_month_start THEN ROUND(amount / (period_month_to - period_month_start + 1), 2)
        ELSE 0 -- เผื่อกรณีข้อมูลผิดพลาด เช่น start > to
    END AS amount_for_month,
    payment_status_desc
FROM
    v_ims_house_payment
WHERE
    period_year = $year AND $month BETWEEN period_month_start AND period_month_to 
ORDER BY
    CAST(alley AS UNSIGNED), house_number; 

";

// แก้ไขส่วนนี้เพื่อบันทึกหมายเลขเดือนลงในไฟล์ a_permission.txt
/*
$myfile = fopen("a_permission.txt", "w") or die("Unable to open file!");
fwrite($myfile, "SQL: " . $sql . "\n");
fwrite($myfile, "Month received: " . $month . "\n"); // แสดงหมายเลขเดือนที่ส่งมา
fwrite($myfile, "Year received: " . $year . "\n");
fwrite($myfile, "Parameters: " . print_r($params, true) . "\n"); // แสดงค่าพารามิเตอร์ที่ใช้ใน query
fclose($myfile);
*/


try {
    $query = $conn->prepare($sql);
    $query->execute();
    $payment_data = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

if (empty($payment_data)) {
    echo "<script>alert('ไม่พบข้อมูลรายงานค่าส่วนกลางในช่วงเดือน " . $selected_months_text . " ปี " . $year . " โปรดตรวจสอบเงื่อนไข'); window.history.back();</script>";
    exit();
}

// กำหนดหัวตารางและคอลัมน์กว้าง (ใช้สำหรับสร้าง HTML table ใน PDF)
$pdf_headers = [
    "ลำดับที่",
    "วันที่ชำระ",
    "เดือน",
    "ปี",
    "บ้านเลขที่",
    "ค่าส่วนกลาง",
    "รายละเอียด",
    "จำนวนเงิน",
    "วิธีการชำระ"
];
$col_widths = [
    '6%','12%', '8%', '7%', '7%', '10%', '22%', '15%', '15%'
]; // รวมกัน 100% สำหรับหน้ากระดาษแนวตั้ง A4


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
        $title_text = 'รายงานการชำระค่าส่วนกลาง';

        $combined_text = $title_text . ' ' . $this->report_period_text;
        $combined_width = $this->GetStringWidth($combined_text);

        $center_x = ($page_width / 2) - ($combined_width / 2);
        if ($center_x < ($logo_x + $logo_width + 5)) {
            $center_x = $logo_x + $logo_width + 5;
        }

        $this->SetXY($center_x, $text_y_center_aligned_with_logo);
        $this->Cell(0, $logo_height, $title_text . ' ' . $this->report_period_text, 0, 0, 'L', 0, '', 0, false, 'M', 'M');


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
$pdf->SetTitle('รายงานการชำระค่าส่วนกลาง ' . $selected_months_text . ' ปี ' . $year);
$pdf->SetSubject('รายงานสรุปค่าส่วนกลาง');

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// กำหนด Margin ด้านบนให้พอดีกับ Header
$top_margin_for_header = 22; // ค่าเดียวกับ expense_summary_daily_report_pdf.php

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
$line_no =0;
foreach ($payment_data as $row) {
    $grand_total_amount += (float)($row['amount_for_month'] ?? 0);
    $line_no++;
    $html_table .= '<tr>';
    $html_table .= '<td width="' . $col_widths[0] . '">' . ($line_no) . '</td>';
    $html_table .= '<td width="' . $col_widths[1] . '">' . ($row['payment_date'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[2] . '" align="center">' . ($selected_months_text ?? '') . '</td>'; //
    $html_table .= '<td width="' . $col_widths[3] . '" align="center">' . ($row['period_year'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[4] . '">' . ($row['house_number'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[5] . '" align="right">' . number_format($row['common_fee'] ?? 0, 2) . '</td>'; // เพิ่ม common_fee จัดชิดขวา
    $html_table .= '<td width="' . $col_widths[6] . '">' . ($row['detail'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[7] . '" align="right">' . number_format($row['amount_for_month'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[8] . '">' . ($row['payment_method'] ?? '') . '</td>';
    $html_table .= '</tr>';
}

$html_table .= '<tr>
    <td colspan="6" align="right"><b>รวมยอดการชำระทั้งสิ้น:</b></td>
    <td width="' . $col_widths[6] . '" align="right"><b>' . number_format($grand_total_amount, 2) . '</b></td>
    <td width="' . $col_widths[7] . '"></td>
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