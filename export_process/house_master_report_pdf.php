<?php
session_start();
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
include('../config/connect_db.php');
// include('../util/number_to_thai_text.php'); // ไม่ได้ใช้สำหรับรายงานนี้ จึงคอมเมนต์ออก

date_default_timezone_set('Asia/Bangkok');

// รับค่าจาก POST สำหรับซอยเริ่มต้นและซอยสิ้นสุด
$alley_start = isset($_POST["alley_start"]) ? trim($_POST["alley_start"]) : '';
$alley_to = isset($_POST["alley_to"]) ? trim($_POST["alley_to"]) : '';

// ตรวจสอบข้อมูลซอย
if ($alley_start == '' || $alley_to == '') {
    echo "<script>alert('กรุณาเลือก \"หมายเลขซอย เริ่มต้น\" และ \"หมายเลขซอย ถึง\" ให้ถูกต้อง'); window.history.back();</script>";
    exit();
}

// ตั้งชื่อไฟล์ PDF
$filename_prefix = "house-master-report-" . $alley_start . "_to_" . $alley_to;

// สร้าง SQL Query สำหรับ ims_house_master
$sql = "SELECT house_number, alley, area_size, garbage_collection_fee, common_fee, land_no, remark, status 
        FROM ims_house_master 
        WHERE CAST(alley AS UNSIGNED) BETWEEN CAST(:alley_start AS UNSIGNED) AND CAST(:alley_to AS UNSIGNED)
        ORDER BY CAST(alley AS UNSIGNED) ASC, house_number ASC";

$params = [':alley_start' => $alley_start, ':alley_to' => $alley_to];

try {
    $query = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $query->bindValue($key, $value, PDO::PARAM_STR);
    }
    $query->execute();
    $house_data = $query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

if (empty($house_data)) {
    echo "<script>alert('ไม่พบข้อมูลบ้านในช่วงซอย " . $alley_start . " ถึง " . $alley_to . " โปรดตรวจสอบเงื่อนไข'); window.history.back();</script>";
    exit();
}

// กำหนดหัวตารางและคอลัมน์กว้างสำหรับ ims_house_master
$pdf_headers = [
    "บ้านเลขที่", "ซอย", "ขนาดพื้นที่ (ตร.ว.)", "ค่าเก็บขยะ", "ค่าส่วนกลาง", "หมายเลขโฉนด", "หมายเหตุ", "สถานะ"
];
// ปรับขนาดความกว้างรวมกันให้ได้ 100% สำหรับ A4 แนวตั้ง
$col_widths = [
    '12%', '8%', '12%', '12%', '12%', '15%', '18%', '11%'
];


// --- Extend TCPDF เพื่อสร้าง Header และ Footer ของตัวเอง ---
class MYPDF extends TCPDF {
    protected $report_alley_start;
    protected $report_alley_to;
    protected $logo_path = '../img/logo/ps33-rec-logo-1xx.png'; // Path to your logo image

    // Constructor
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
    }

    // Method to set report alley range
    public function setReportAlleys($alley_start, $alley_to) {
        $this->report_alley_start = $alley_start;
        $this->report_alley_to = $alley_to;
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

        // --- 2. Report Title & Alley Range (อยู่กลางหน้ากระดาษ ในระดับเดียวกับโลโก้) ---
        $text_y_center_aligned_with_logo = $logo_y;

        $this->SetFont('THSarabunNew', 'B', 16);
        $title_text = 'รายงานข้อมูลบ้าน';
        $alley_range_text = 'ซอย ' . $this->report_alley_start . ' ถึง ' . $this->report_alley_to;

        // คำนวณความกว้างรวมของข้อความสองส่วน
        $combined_text = $title_text . '   ' . $alley_range_text; // เพิ่มช่องว่างกลาง
        $combined_width = $this->GetStringWidth($combined_text);

        // คำนวณตำแหน่ง X สำหรับจัดกึ่งกลางข้อความสองส่วน
        $center_x = ($page_width / 2) - ($combined_width / 2);
        // ให้แน่ใจว่าไม่ชนโลโก้ (โลโก้ถึง $logo_x + $logo_width)
        if ($center_x < ($logo_x + $logo_width + 5)) {
            $center_x = $logo_x + $logo_width + 5; // ขยับไปทางขวา 5mm จากโลโก้
        }

        $this->SetXY($center_x, $text_y_center_aligned_with_logo);
        $this->Cell(0, $logo_height, $title_text . '   ' . $alley_range_text, 0, 0, 'L', 0, '', 0, false, 'M', 'M');


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
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false); // 'P' for Portrait

$pdf->setReportAlleys($alley_start, $alley_to);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('รายงานข้อมูลบ้าน ซอย ' . $alley_start . ' ถึง ' . $alley_to);
$pdf->SetSubject('รายงานสรุปข้อมูลบ้าน');

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

foreach ($house_data as $row) {
    $html_table .= '<tr>';
    $html_table .= '<td width="' . $col_widths[0] . '" align="center">' . ($row['house_number'] ?? '-') . '</td>';
    $html_table .= '<td width="' . $col_widths[1] . '" align="center">' . ($row['alley'] ?? '-') . '</td>';
    $html_table .= '<td width="' . $col_widths[2] . '" align="right">' . number_format($row['area_size'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[3] . '" align="right">' . number_format($row['garbage_collection_fee'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[4] . '" align="right">' . number_format($row['common_fee'] ?? 0, 2) . '</td>';
    $html_table .= '<td width="' . $col_widths[5] . '">' . ($row['land_no'] ?? '-') . '</td>';
    $html_table .= '<td width="' . $col_widths[6] . '">' . ($row['remark'] ?? '') . '</td>';
    $html_table .= '<td width="' . $col_widths[7] . '" align="center">' . '</td>'; // เพิ่มคอลัมน์สถานะ
    $html_table .= '</tr>';
}

$html_table .= '</tbody></table>';

$pdf->writeHTML($html_table, true, false, true, false, '');

$filename = $filename_prefix . "_" . date('Ymd_His') . ".pdf";
$pdf->Output($filename, 'I');
exit;