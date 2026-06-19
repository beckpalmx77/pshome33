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
$filename_prefix = "expenses-report-" . $start_date_for_filename . "_to_" . $end_date_for_filename;

$sql = "SELECT * FROM v_ims_expenses
        WHERE CASE 
            WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
            WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
            ELSE NULL 
        END BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') AND STR_TO_DATE(:end_date, '%d-%m-%Y')
        ORDER BY CASE 
            WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
            WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
            ELSE NULL 
        END ASC, id ASC";

$params = [':start_date' => $start_date_str, ':end_date' => $end_date_str];

try {
    $query = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $query->bindValue($key, $value, PDO::PARAM_STR);
    }
    $query->execute();
    $expenses_data = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

if (empty($expenses_data)) {
    echo "<script>alert('ไม่พบข้อมูลรายงานค่าใช้จ่ายในช่วงวันที่ " . $start_date_str . " ถึง " . $end_date_str . " โปรดตรวจสอบเงื่อนไข'); window.history.back();</script>";
    exit();
}

// กำหนดหัวตารางและคอลัมน์กว้าง (ใช้สำหรับสร้าง HTML table)
$pdf_headers = [
    "วันที่", "รายการ", "จำนวน", "หมายเหตุ"
];
$col_widths = [
    '10%', '50%', '20%', '20%'
];


// --- Extend TCPDF เพื่อสร้าง Header และ Footer ของตัวเอง ---
class MYPDF extends TCPDF {
    protected $report_start_date;
    protected $report_end_date;
    protected $logo_path = '../img/logo/ps33-rec-logo-1xx.png'; // Path to your logo image

    // Constructor
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
    }

    // Method to set report dates
    public function setReportDates($start_date, $end_date) {
        $this->report_start_date = $start_date;
        $this->report_end_date = $end_date;
    }

    // Page header: This method is called automatically for each new page
    public function Header() {
        $page_width = $this->getPageWidth(); // ความกว้างของหน้ากระดาษ
        $margin_left = 10; // กำหนดค่า margin ซ้าย (ควรตรงกับ SetMargins)
        $margin_right = 10; // กำหนดค่า margin ขวา (ควรตรงกับ SetMargins)
        $usable_width = $page_width - $margin_left - $margin_right;

        $page_right_edge = $page_width - $margin_right;

        $header_start_y = 10; // เริ่มต้นการวาด Header ที่ 5mm จากขอบบนสุดของกระดาษ

        // --- 1. Logo (ซ้ายบน) ---
        $logo_x = $margin_left; // เริ่มต้นที่ Left Margin
        $logo_y = $header_start_y;
        $logo_width = 20;
        $logo_height = 10; // ความสูงของโลโก้ 10mm
        if (file_exists($this->logo_path)) {
            $this->Image($this->logo_path, $logo_x, $logo_y, $logo_width, $logo_height, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            $this->SetFont('THSarabunNew', 'B', 10);
            $this->SetXY($logo_x, $logo_y + ($logo_height / 4)); // ปรับให้ข้อความ No Logo อยู่กึ่งกลาง
            $this->Cell($logo_width, $logo_height/2, 'No Logo', 0, 0, 'C');
        }

        // --- 2. Report Title & Date Range (อยู่กลางหน้ากระดาษ ในระดับเดียวกับโลโก้) ---
        $text_y_center_aligned_with_logo = $logo_y;

        $this->SetFont('THSarabunNew', 'B', 16);
        $title_text = 'รายงานค่าใช้จ่าย';
        $date_range_text = 'วันที่ ' . $this->report_start_date . ' - ' . $this->report_end_date;

        // คำนวณความกว้างรวมของข้อความสองส่วน
        $combined_text = $title_text . '   ' . $date_range_text; // เพิ่มช่องว่างกลาง
        $combined_width = $this->GetStringWidth($combined_text);

        // คำนวณตำแหน่ง X สำหรับจัดกึ่งกลางข้อความสองส่วน
        $center_x = ($page_width / 2) - ($combined_width / 2);
        // ให้แน่ใจว่าไม่ชนโลโก้ (โลโก้ถึง $logo_x + $logo_width)
        if ($center_x < ($logo_x + $logo_width + 5)) {
            $center_x = $logo_x + $logo_width + 5; // ขยับไปทางขวา 5mm จากโลโก้
        }

        $this->SetXY($center_x, $text_y_center_aligned_with_logo);
        $this->Cell(0, $logo_height, $title_text . '   ' . $date_range_text, 0, 0, 'L', 0, '', 0, false, 'M', 'M');


        // --- 3. Print Date/Time (มุมขวาบน) ---
        $this->SetFont('THSarabunNew', '', 9);
        $print_date_text = 'วันที่พิมพ์: ' . date('d/m/Y H:i:s');
        $print_date_width = $this->GetStringWidth($print_date_text);

        // กำหนด Y ให้เท่ากับ Y ของโลโก้ (หรือ Y ของข้อความชื่อรายงาน)
        $this->SetXY($page_right_edge - $print_date_width, $text_y_center_aligned_with_logo);
        $this->Cell($print_date_width, $logo_height, $print_date_text, 0, 0, 'R', 0, '', 0, false, 'M', 'M');


        // --- 4. กำหนด Y สำหรับเนื้อหาหลัก (ตาราง) ---
        $final_header_y = $logo_y + $logo_height; // จุดที่ส่วนหัวสิ้นสุดลง
        $this->SetY($final_header_y + 3); // เพิ่มระยะห่าง 3mm (เล็กน้อย) จากส่วนหัว
    }

    // Page footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('THSarabunNew', '', 8);
        $this->Cell(0, 10, 'หน้า ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'L');
    }
}

// --- TCPDF Initialization ---
$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);

$pdf->setReportDates($start_date_str, $end_date_str);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('รายงานค่าใช้จ่าย วันที่ ' . $start_date_str . ' ถึง ' . $end_date_str);
$pdf->SetSubject('รายงานสรุปค่าใช้จ่าย');

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// --- KEY FIX: Adjust Top Margin Here ---
$top_margin_for_header = 22;

$pdf->SetMargins(10, $top_margin_for_header, 10); // (Left, Top, Right)

// Set auto page break
$pdf->SetAutoPageBreak(TRUE, 15);

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

foreach ($expenses_data as $row) {
    $grand_total_amount += (float)($row['amount'] ?? 0);

    $html_table .= '<tr>';
    // Column 1: วันที่ (expense_date)
    $expense_date_formatted = '';
    if (!empty($row['expense_date'])) {
        $date_obj = DateTime::createFromFormat('d-m-Y', $row['expense_date']);
        if ($date_obj) {
            $expense_date_formatted = $date_obj->format('d/m/Y');
        }
    }
    $html_table .= '<td width="' . $col_widths[0] . '" align="center">' . $expense_date_formatted . '</td>';

    // Column 2: รายการ (description)
    $html_table .= '<td width="' . $col_widths[1] . '">' . ($row['description'] ?? '') . '</td>';

    // Column 3: จำนวน (amount)
    $html_table .= '<td width="' . $col_widths[2] . '" align="right">' . number_format($row['amount'] ?? 0, 2) . '</td>';

    // Column 4: หมายเหตุ (remark)
    $html_table .= '<td width="' . $col_widths[3] . '">' . ($row['remark'] ?? '') . '</td>';
    $html_table .= '</tr>';
}

$html_table .= '<tr>
    <td colspan="2" align="right"><b>รวมยอดค่าใช้จ่ายทั้งสิ้น:</b></td>
    <td width="' . $col_widths[2] . '" align="right"><b>' . number_format($grand_total_amount, 2) . '</b></td>
    <td width="' . $col_widths[3] . '"></td>
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