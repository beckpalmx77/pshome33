<?php
session_start();
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
include('../config/connect_db.php');
include('../util/number_to_thai_text.php');

date_default_timezone_set('Asia/Bangkok');

// --- รับค่าจาก $_POST เป็น ปี และ เดือน ---
$year = isset($_POST["year"]) ? trim($_POST["year"]) : '';
$months = isset($_POST["months"]) ? $_POST["months"] : []; // เป็น Array ของเดือนที่เลือก (e.g., ['01', '02'])

if ($year === '' || empty($months)) {
    echo "<script>alert('กรุณาเลือก \"ปี\" และ \"เดือน\" อย่างน้อย 1 เดือนให้ถูกต้อง'); window.history.back();</script>";
    exit();
}

// แปลง Array ของเดือนให้เป็น String สำหรับแสดงผลและชื่อไฟล์
$month_names = [
    '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.', '05' => 'พ.ค.', '06' => 'มิ.ย.',
    '07' => 'ก.ค.', '08' => 'ส.ค.', '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
];

$selected_month_names = [];
foreach ($months as $month_num) {
    if (isset($month_names[$month_num])) {
        $selected_month_names[] = $month_names[$month_num];
    }
}
$display_months = implode(', ', $selected_month_names); // เช่น ม.ค., ก.พ., มี.ค.

$filename_prefix = "expenses-report-" . $year . "_" . implode('-', $months); // ชื่อไฟล์เช่น expenses-report-2025_01-02-03

// --- สร้างเงื่อนไข SQL ใหม่ ---
// ใช้ PDO placeholder สำหรับ IN clause
$placeholders = implode(',', array_fill(0, count($months), '?')); // สร้าง ?,?,?,...
$sql = "SELECT * FROM v_ims_expenses
        WHERE exp_year = ? AND exp_month IN ($placeholders)
        ORDER BY CASE 
            WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
            WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
            ELSE NULL 
        END ASC, id ASC";

try {
    $query = $conn->prepare($sql);

    $param_index = 1;
    $query->bindValue($param_index++, $year, PDO::PARAM_STR); // Bind ปี

    foreach ($months as $month_value) { // Bind แต่ละเดือน
        $query->bindValue($param_index++, $month_value, PDO::PARAM_STR);
    }

    $query->execute();
    $expenses_data = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

if (empty($expenses_data)) {
    echo "<script>alert('ไม่พบข้อมูลรายงานค่าใช้จ่ายสำหรับปี " . $year . " และเดือน " . $display_months . " โปรดตรวจสอบเงื่อนไข'); window.history.back();</script>";
    exit();
}

// กำหนดหัวตารางและคอลัมน์กว้าง (ใช้สำหรับสร้าง HTML table)
$pdf_headers = [
    "จ่ายให้", "วันที่ใช้จ่าย", "เลขที่เอกสาร", "เดือน", "ปี", "เลขที่ INV.", "หมวดหมู่", "รายละเอียด",
    "จำนวน", "หน่วย", "จำนวนเงิน", "หมายเหตุ", "สถานะอนุมัติ", "วันที่บันทึก", "วิธีชำระเงิน"
];
$col_widths = [
    '7%', '6%', '10%', '3%', '3%', '7%', '7%', '13%', '5%', '4%', '7%', '10%', '6%', '7%', '5%'
];


// --- Extend TCPDF เพื่อสร้าง Header และ Footer ของตัวเอง ---
class MYPDF extends TCPDF {
    protected $report_year; // เปลี่ยนเป็นปี
    protected $report_months; // เปลี่ยนเป็นเดือนที่เลือก (array)
    protected $report_display_months; // ชื่อเดือนสำหรับแสดงผล
    protected $logo_path = '../img/logo/ps33-rec-logo-1xx.png'; // Path to your logo image

    // Constructor
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true,  $encoding = 'UTF-8', $diskcache = false, $pdfa = false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
    }

    // Method to set report dates (ปรับเปลี่ยนเป็นปีและเดือน)
    public function setReportPeriod($year, $months_array, $display_months_str) {
        $this->report_year = $year;
        $this->report_months = $months_array;
        $this->report_display_months = $display_months_str;
    }

    // Page header: This method is called automatically for each new page
    public function Header() {
        $page_width = $this->getPageWidth(); // ความกว้างของหน้ากระดาษ
        $margin_left = 10; // กำหนดค่า margin ซ้าย (ควรตรงกับ SetMargins)
        $margin_right = 10; // กำหนดค่า margin ขวา (ควรตรงกับ SetMargins)
        $usable_width = $page_width - $margin_left - $margin_right;

        $page_right_edge = $page_width - $margin_right; // ประกาศตัวแปรนี้ที่นี่

        // --- เพิ่มค่า Y เริ่มต้นของ Header เพื่อเลื่อนทุกอย่างลงมา ***
        $header_start_y = 15; // ลองปรับค่านี้ หากต้องการให้ Header ทั้งก้อนอยู่ต่ำลงไปอีก

        // --- 1. Logo (ซ้ายบน) ---
        $logo_x = $margin_left; // เริ่มต้นที่ Left Margin
        $logo_y = $header_start_y;
        $logo_width = 20;
        $logo_height = 10; // ความสูงของโลโก้ 10mm
        if (file_exists($this->logo_path)) {
            $this->Image($this->logo_path, $logo_x, $logo_y, $logo_width, $logo_height, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            $this->SetFont('THSarabunNew', 'B', 12);
            $this->SetXY($logo_x, $logo_y + ($logo_height / 4)); // ปรับให้ข้อความ No Logo อยู่กึ่งกลาง
            $this->Cell($logo_width, $logo_height/2, 'No Logo', 0, 0, 'C');
        }

        // --- 2. Report Title & Period (อยู่กลางหน้ากระดาษ ในระดับเดียวกับโลโก้) ---
        $text_y_center_aligned_with_logo = $logo_y; // ตั้งให้ Y เท่ากับโลโก้เลย

        $this->SetFont('THSarabunNew', 'B', 18);
        $title_text = 'รายงานค่าใช้จ่าย';
        // *** ปรับการแสดงผลเดือนและปี ***
        $period_text = 'เดือน ' . $this->report_display_months . ' ปี ' . $this->report_year;

        // คำนวณความกว้างรวมของข้อความสองส่วน
        $combined_text = $title_text . '   ' . $period_text; // เพิ่มช่องว่างกลาง
        $combined_width = $this->GetStringWidth($combined_text);

        // คำนวณตำแหน่ง X สำหรับจัดกึ่งกลางข้อความสองส่วน
        $center_x = ($page_width / 2) - ($combined_width / 2);
        // ให้แน่ใจว่าไม่ชนโลโก้ (โลโก้ถึง $logo_x + $logo_width)
        if ($center_x < ($logo_x + $logo_width + 5)) {
            $center_x = $logo_x + $logo_width + 5; // ขยับไปทางขวา 5mm จากโลโก้
        }

        $this->SetXY($center_x, $text_y_center_aligned_with_logo);
        $this->Cell(0, $logo_height, $title_text . '   ' . $period_text, 0, 0, 'L', 0, '', 0, false, 'M', 'M');


        // --- 3. Print Date/Time (มุมขวาบน) ---
        $this->SetFont('THSarabunNew', '', 11);
        $print_date_text = 'วันที่พิมพ์: ' . date('d/m/Y H:i:s');
        $print_date_width = $this->GetStringWidth($print_date_text);

        // กำหนด Y ให้เท่ากับ Y ของโลโก้ (หรือ Y ของข้อความชื่อรายงาน)
        $this->SetXY($page_right_edge - $print_date_width, $text_y_center_aligned_with_logo);
        $this->Cell($print_date_width, $logo_height, $print_date_text, 0, 0, 'R', 0, '', 0, false, 'M', 'M');


        // --- 4. กำหนด Y สำหรับเนื้อหาหลัก (ตาราง) ---
        // หาตำแหน่ง Y ที่ต่ำที่สุดใน Header หลังจากวาดทุกองค์ประกอบแล้ว
        $final_header_y = $logo_y + $logo_height; // จุดที่ส่วนหัวสิ้นสุดลง
        $this->SetY($final_header_y + 3); // เพิ่มระยะห่าง 3mm (เล็กน้อย) จากส่วนหัว
    }

    // Page footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('THSarabunNew', '', 10);
        $this->Cell(0, 10, 'หน้า ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'L');
    }
}

// --- TCPDF Initialization ---
$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// --- ส่งค่าปีและเดือนที่เลือกเข้าสู่ Class ---
$pdf->setReportPeriod($year, $months, $display_months);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('รายงานค่าใช้จ่าย เดือน ' . $display_months . ' ปี ' . $year); // ปรับ Title
$pdf->SetSubject('รายงานสรุปค่าใช้จ่าย');

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// --- *** ปรับ Margin ด้านบน (ถ้าต้องการให้ Header ลงมาอีก) *** ---
// คำนวณ Margin ด้านบนให้พอดีกับความสูงของ Header ที่มี โลโก้สูง 10mm + padding เล็กน้อย
// โลโก้เริ่ม Y ($header_start_y) + ความสูงโลโก้ ($logo_height) + ระยะห่างตาราง (3mm)
// ถ้า $header_start_y = 15, $logo_height = 10 => 15 + 10 + 3 = 28mm
$top_margin_for_header = 28; // <<< ปรับค่านี้เพื่อให้ Header เลื่อนลงมาอีก (ถ้าต้องการ) >>>

$pdf->SetMargins(10, $top_margin_for_header, 10); // (Left, Top, Right)

// Set auto page break
$pdf->SetAutoPageBreak(TRUE, 15);

// Set main font for the document content
$pdf->SetFont('THSarabunNew', '', 12);

// Add the first page. The Header() method will be called automatically here.
$pdf->AddPage();

// --- สร้าง HTML สำหรับทั้งตาราง (รวม <thead/> และ <tbody/>) ---
// TCPDF จะเริ่มเขียน HTML content จาก Y position ที่กำหนดโดย Top Margin
$html_table = '<table border="1" cellspacing="0" cellpadding="4" style="font-size:11pt; width: 100%;">
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
    $html_table .= '<td width="' . $col_widths[0] . '">' . ($row['receipt_name'] ?? '') . '</td>';

    $expense_date_formatted = '';
    if (!empty($row['expense_date'])) {
        $date_obj = DateTime::createFromFormat('Y-m-d', $row['expense_date']);
        if (!$date_obj) {
            $date_obj = DateTime::createFromFormat('d-m-Y', $row['expense_date']);
        }
        if ($date_obj) {
            $expense_date_formatted = $date_obj->format('d/m/Y');
        } else {
            $expense_date_formatted = $row['expense_date'];
        }
    }
    $html_table .= '<td width="' . $col_widths[1] . '" align="center">' . $expense_date_formatted . '</td>';
    $html_table .= '<td width="' . $col_widths[2] . '" align="center">' . ($row['doc_id'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[3] . '" align="center">' . ($row['exp_month'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[4] . '" align="center">' . ($row['exp_year'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[5] . '">' . ($row['inv'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[6] . '">' . ($row['category_name'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[7] . '">' . ($row['description'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[8] . '" align="right">' . number_format($row['qty'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[9] . '" align="center">' . ($row['unit_name'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[10] . '" align="right">' . number_format($row['amount'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[11] . '">' . ($row['remark'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[12] . '" align="center">' . (($row['approve_status'] ?? 'N') === "Y" ? "อนุมัติแล้ว" : "รออนุมัติ") . '</td>';
    $created_at_formatted = '';
    if (!empty($row['created_at'])) {
        $date_obj = DateTime::createFromFormat('Y-m-d H:i:s', $row['created_at']);
        if ($date_obj) {
            $created_at_formatted = $date_obj->format('d/m/Y H:i');
        }
    }
    $html_table .= '<td width="' . $col_widths[13] . '" align="center">' . $created_at_formatted . '</td>';
    $html_table .= '<td width="' . $col_widths[14] . '">' . ($row['payment_method'] ?? '') . '</td>';
    $html_table .= '</tr>';
}

$html_table .= '<tr>
    <td colspan="10" align="right"><b>รวมยอดค่าใช้จ่ายทั้งสิ้น:</b></td>
    <td width="' . $col_widths[10] . '" align="right"><b>' . number_format($grand_total_amount, 2) . '</b></td>
    <td colspan="4"></td>
</tr>';

$html_table .= '</tbody></table>';

$pdf->writeHTML($html_table, true, false, true, false, '');

if (function_exists('number_to_thai_text') && $grand_total_amount > 0) {
    $pdf->SetY($pdf->GetY() + 5);
    $pdf->SetFont('THSarabunNew', '', 12);
    $pdf->writeHTMLCell(0, 0, '', '', '<p style="text-align: left;"><b>ตัวอักษร:</b> ' . number_to_thai_text($grand_total_amount) . '</p>', 0, 1, 0, true, '', true);
}

$filename = $filename_prefix . "_" . date('Ymd_His') . ".pdf";
$pdf->Output($filename, 'I');
exit;