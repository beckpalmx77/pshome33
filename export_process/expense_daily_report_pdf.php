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
        WHERE STR_TO_DATE(expense_date, '%d-%m-%Y') BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') AND STR_TO_DATE(:end_date, '%d-%m-%Y')
        ORDER BY STR_TO_DATE(expense_date, '%d-%m-%Y') ASC, id ASC";

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
    "จ่ายให้", "วันที่ใช้จ่าย", "เดือน", "ปี", "เลขที่ INV.", "หมวดหมู่", "รายละเอียด",
    "จำนวน", "หน่วย", "จำนวนเงิน", "หมายเหตุ", "สถานะอนุมัติ", "วันที่บันทึก", "วิธีชำระเงิน"
];
$col_widths = [
    '8%', '7%', '4%', '4%', '8%', '8%', '15%', '5%', '5%', '7%', '10%', '7%', '7%', '5%'
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

        // <<< FIX START >>>
        $page_right_edge = $page_width - $margin_right; // *** แก้ไข: ประกาศตัวแปรนี้ที่นี่ ***
        // <<< FIX END >>>

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
        // คำนวณ Y เพื่อให้ข้อความอยู่ตรงกลางความสูงของโลโก้
        // โลโก้สูง 10mm, Cell ข้อความสูง 10mm. เราต้องการให้ตรงกลางของ Cell ข้อความอยู่ที่กึ่งกลางของโลโก้
        // โลโก้เริ่ม Y=5, จบ Y=15. กึ่งกลาง Y=10.
        // Cell ข้อความสูง 10mm, ถ้ากึ่งกลางอยู่ที่ Y=10, Cell จะเริ่มที่ Y=5 และจบที่ Y=15
        $text_y_center_aligned_with_logo = $logo_y; // ตั้งให้ Y เท่ากับโลโก้เลย (Y=5)

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
        // Cell(width, height, text, border, ln=0, align, fill, link, stretch, ignore_min_height, valign, resource_cell_height)
        $this->Cell(0, $logo_height, $title_text . '   ' . $date_range_text, 0, 0, 'L', 0, '', 0, false, 'M', 'M');


        // --- 3. Print Date/Time (มุมขวาบน) ---
        $this->SetFont('THSarabunNew', '', 9);
        $print_date_text = 'วันที่พิมพ์: ' . date('d/m/Y H:i:s');
        $print_date_width = $this->GetStringWidth($print_date_text);

        // กำหนด Y ให้เท่ากับ Y ของโลโก้ (หรือ Y ของข้อความชื่อรายงาน)
        $this->SetXY($page_right_edge - $print_date_width, $text_y_center_aligned_with_logo);
        $this->Cell($print_date_width, $logo_height, $print_date_text, 0, 0, 'R', 0, '', 0, false, 'M', 'M');


        // --- 4. กำหนด Y สำหรับเนื้อหาหลัก (ตาราง) ---
        // หาตำแหน่ง Y ที่ต่ำที่สุดใน Header หลังจากวาดทุกองค์ประกอบแล้ว
        // โลโก้เริ่มที่ Y=5, สูง 10 -> จบที่ Y=15
        // Cell ข้อความเริ่มที่ Y=5, สูง 10 -> จบที่ Y=15
        // ดังนั้น ส่วน Header ของเราจบที่ Y ประมาณ 15mm จากขอบกระดาษบน
        // เราจะตั้งค่า Y เพิ่มขึ้นจากจุดนี้เพียงเล็กน้อย เพื่อให้ตารางอยู่ใกล้
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

// --- *** KEY FIX: Adjust Top Margin Here *** ---
// คำนวณ Margin ด้านบนให้พอดีกับความสูงของ Header ที่มี โลโก้สูง 10mm + padding เล็กน้อย
// โลโก้เริ่ม Y=5, สูง 10 -> จบ Y=15
// ถ้าต้องการตารางห่างจากโลโก้ 3mm
// Margin ที่เราควรตั้งคือ 15mm (โลโก้จบ) + 3mm (ระยะห่าง) = 18mm
$top_margin_for_header = 22; // ปรับลดลงเพื่อให้ตารางอยู่ใกล้ขึ้น

$pdf->SetMargins(10, $top_margin_for_header, 10); // (Left, Top, Right)

// Set auto page break
$pdf->SetAutoPageBreak(TRUE, 15);

// Set main font for the document content
$pdf->SetFont('THSarabunNew', '', 10);

// Add the first page. The Header() method will be called automatically here.
$pdf->AddPage();

// --- สร้าง HTML สำหรับทั้งตาราง (รวม <thead/> และ <tbody/>) ---
// TCPDF จะเริ่มเขียน HTML content จาก Y position ที่กำหนดโดย Top Margin
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
    $html_table .= '<td width="' . $col_widths[0] . '">' . ($row['receipt_name'] ?? '') . '</td>';

    $expense_date_formatted = '';
    if (!empty($row['expense_date'])) {
        $date_obj = DateTime::createFromFormat('d-m-Y', $row['expense_date']);
        if ($date_obj) {
            $expense_date_formatted = $date_obj->format('d/m/Y');
        }
    }
    $html_table .= '<td width="' . $col_widths[1] . '" align="center">' . $expense_date_formatted . '</td>';
    $html_table .= '<td width="' . $col_widths[2] . '" align="center">' . ($row['exp_month'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[3] . '" align="center">' . ($row['exp_year'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[4] . '">' . ($row['inv'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[5] . '">' . ($row['category_name'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[6] . '">' . ($row['description'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[7] . '" align="right">' . number_format($row['qty'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[8] . '" align="center">' . ($row['unit_name'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[9] . '" align="right">' . number_format($row['amount'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[10] . '">' . ($row['remark'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[11] . '" align="center">' . (($row['approve_status'] ?? 'N') === "Y" ? "อนุมัติแล้ว" : "รออนุมัติ") . '</td>';
    $created_at_formatted = '';
    if (!empty($row['created_at'])) {
        $date_obj = DateTime::createFromFormat('Y-m-d H:i:s', $row['created_at']);
        if ($date_obj) {
            $created_at_formatted = $date_obj->format('d/m/Y H:i');
        }
    }
    $html_table .= '<td width="' . $col_widths[12] . '" align="center">' . $created_at_formatted . '</td>';
    $html_table .= '<td width="' . $col_widths[13] . '">' . ($row['payment_method'] ?? '') . '</td>';
    $html_table .= '</tr>';
}

$html_table .= '<tr>
    <td colspan="9" align="right"><b>รวมยอดค่าใช้จ่ายทั้งสิ้น:</b></td>
    <td width="' . $col_widths[9] . '" align="right"><b>' . number_format($grand_total_amount, 2) . '</b></td>
    <td colspan="4"></td>
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