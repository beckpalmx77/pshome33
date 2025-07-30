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
$filename_prefix = "downpayment-report-" . $month . "-" . $year; // เปลี่ยนชื่อไฟล์ให้สื่อถึงว่าเป็นรายงานเงินทำสัญญา

// Define month names for header display
$month_names_th = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

// กำหนดข้อความสำหรับแสดงช่วงรายงาน
$selected_months_text = $month_names_th[$month]; // ใช้ชื่อเดือนเดียว
$report_period_text = 'เดือน ' . $selected_months_text . ' ปี ' . $year;


// --- SQL Query Construction ---
// Querying only ims_installment table
$sql = "
SELECT
    installment_id,
    house_number,
    doc_date, -- ใช้ doc_date แทน
    detail,
    principal_amount,
    down_payment,
    num_installments,
    installment_per_period,
    interest_rate,
    start_date,
    status AS installment_status
FROM
    v_ims_installment
WHERE
    doc_year = $year  AND doc_month = $month 
ORDER BY
    house_number, doc_date;
";

// Parameters for the query
$params = [
    ':year' => $year,
    ':month_selected' => $month
];

/*
$myfile = fopen("a_permission.txt", "w") or die("Unable to open file!");
fwrite($myfile, "SQL: " . $sql . "\n");
fwrite($myfile, "Month received: " . $month . "\n");
fwrite($myfile, "Year received: " . $year . "\n");
fwrite($myfile, "Parameters: " . print_r($params, true) . "\n");
fclose($myfile);
*/

try {
    $query = $conn->prepare($sql);
    $query->execute();
    $installment_data = $query->fetchAll(PDO::FETCH_ASSOC); // Renamed variable for clarity

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

if (empty($installment_data)) {
    echo "<script>alert('ไม่พบข้อมูลเงินทำสัญญาในช่วงเดือน " . $selected_months_text . " ปี " . $year . " โปรดตรวจสอบเงื่อนไข'); window.history.back();</script>";
    exit();
}

// กำหนดหัวตารางและคอลัมน์กว้าง (สำหรับตาราง ims_installment)
$pdf_headers = [
    "ลำดับที่",
    "บ้านเลขที่",
    "วันที่ทำสัญญา", // เปลี่ยนเป็น วันที่ทำสัญญา
    "เงินต้นรวม",
    "เงินทำสัญญา",
    "จำนวนงวด",
    "ผ่อนต่องวด",
    "ดอกเบี้ย (%)",
    "รายละเอียด"
];

// Adjusted widths to sum up to 100% for A4 portrait (10 columns)
$col_widths = [
    '6%', '9%', '11%', '11%', '11%', '9%', '11%', '9%', '12%', '11%'
];


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
        $title_text = 'รายงานสรุปเงินทำสัญญาการผ่อนชำระค่าส่วนกลางที่ค้าง'; // Updated title to reflect down payment
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
$pdf->SetTitle('รายงานสรุปเงินทำสัญญา ' . $selected_months_text . ' ปี ' . $year); // Updated title
$pdf->SetSubject('รายงานสรุปเงินทำสัญญา'); // Updated subject

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

$grand_total_down_payment = 0; // เปลี่ยนเป็นรวมเฉพาะเงินทำสัญญา
$line_no = 0;

foreach ($installment_data as $row) {
    $grand_total_down_payment += (float)($row['down_payment'] ?? 0); // รวมเฉพาะ down_payment
    $line_no++;
    $html_table .= '<tr>';
    $html_table .= '<td width="' . $col_widths[0] . '">' . ($line_no) . '</td>';
    $html_table .= '<td width="' . $col_widths[1] . '">' . ($row['house_number'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[2] . '">' . ($row['doc_date'] ?? '') . '</td>'; // แสดง doc_date
    $html_table .= '<td width="' . $col_widths[3] . '" align="right">' . number_format($row['principal_amount'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[4] . '" align="right">' . number_format($row['down_payment'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[5] . '" align="center">' . ($row['num_installments'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[6] . '" align="right">' . number_format($row['installment_per_period'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[7] . '" align="right">' . number_format($row['interest_rate'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[8] . '">' . ($row['detail'] ?? '') . '</td>';
    $html_table .= '</tr>';
}

$html_table .= '<tr>
    <td colspan="4" align="right"><b>รวมยอดเงินทำสัญญาทั้งสิ้น:</b></td>
    <td width="' . $col_widths[4] . '" align="right"><b>' . number_format($grand_total_down_payment, 2) . '</b></td>
    <td colspan="5"></td>
</tr>';


$html_table .= '</tbody></table>';

$pdf->writeHTML($html_table, true, false, true, false, '');

// แปลงยอดรวมเงินทำสัญญาเป็นตัวอักษร
if (function_exists('number_to_thai_text') && $grand_total_down_payment > 0) {
    $pdf->SetY($pdf->GetY() + 5);
    $pdf->SetFont('THSarabunNew', '', 10);
    $pdf->writeHTMLCell(0, 0, '', '', '<p style="text-align: left;"><b>ตัวอักษร (รวมเงินทำสัญญา):</b> ' . number_to_thai_text($grand_total_down_payment) . '</p>', 0, 1, 0, true, '', true);
}


$filename = $filename_prefix . "_" . date('Ymd_His') . ".pdf";
$pdf->Output($filename, 'I');
exit;