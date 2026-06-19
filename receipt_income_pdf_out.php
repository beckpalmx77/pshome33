<?php
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php');

date_default_timezone_set('Asia/Bangkok'); // ตั้งค่า timezone

// ===== 1. กำหนดคลาส TCPDF ใหม่ เพื่อเพิ่ม Header และ Footer ที่กำหนดเอง =====
class MYPDF extends TCPDF {
    protected $report_header_text;
    protected $report_payment_method;
    protected $report_start_date;
    protected $report_end_date;
    protected $logo_path = __DIR__ . '/img/logo/ps33-rec-logo-1xx.png'; // Path to your logo image

    // Constructor
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true,  $encoding = 'UTF-8', $diskcache = false, $pdfa = false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
    }

    // Method to set report information
    public function setReportInfo($header_text, $payment_method, $start_date, $end_date) {
        $this->report_header_text = $header_text;
        $this->report_payment_method = $payment_method;
        $this->report_start_date = $start_date;
        $this->report_end_date = $end_date;
    }

    // Page Header
    public function Header() {
        $page_width = $this->getPageWidth();
        // --- FIX START: ใช้ค่า margin ที่กำหนดไว้หรือเรียกจาก protected properties ---
        $margin_left = $this->lMargin; // ใช้ protected property lMargin
        $margin_right = $this->rMargin; // ใช้ protected property rMargin
        // หรือถ้าอยากใช้ค่าคงที่ ให้ประกาศไว้ที่ Global scope หรือส่งเข้ามา
        // $margin_left = 10;
        // $margin_right = 10;
        // --- FIX END ---

        $page_right_edge = $page_width - $margin_right;

        // กำหนด Y เริ่มต้นของ Header เพื่อเลื่อนทุกอย่างลงมา
        // ถ้าต้องการให้ Header ลงมาอีก ให้เพิ่มค่านี้
        $header_start_y = 15; // เริ่มต้นที่ 10mm จากขอบบน (ลองปรับค่านี้)

        // --- 1. Logo (ซ้ายบน) ---
        $logo_x = $margin_left;
        $logo_y = $header_start_y;
        $logo_width = 20;
        $logo_height = 10; // กำหนดความสูงของโลโก้ให้คงที่ (ปรับตามสัดส่วนจริงของรูป)
        if (file_exists($this->logo_path)) {
            $this->Image($this->logo_path, $logo_x, $logo_y, $logo_width, $logo_height, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            // Fallback if logo not found
            $this->SetFont('THSarabunNew', 'B', 10);
            $this->SetXY($logo_x, $logo_y + ($logo_height / 4));
            $this->Cell($logo_width, $logo_height / 2, 'No Logo', 0, 0, 'C');
        }

        // --- 2. Report Title & Date Range (อยู่กลางหน้ากระดาษ) ---
        // คำนวณ Y เพื่อให้ข้อความอยู่ตรงกลางความสูงของโลโก้
        $text_y_aligned_with_logo = $logo_y + ($logo_height / 2) - 5; // 5mm คือครึ่งหนึ่งของ Cell height (10mm)

        $this->SetFont('THSarabunNew', 'B', 16);
        $title_text = $this->report_header_text . ' (' . $this->report_payment_method . ')';
        $date_range_text = 'ช่วงวันที่ ' . $this->report_start_date . ' ถึง ' . $this->report_end_date;

        // คำนวณความกว้างรวมของข้อความสองส่วน
        $combined_text_width_title = $this->GetStringWidth($title_text);
        $combined_text_width_date = $this->GetStringWidth($date_range_text);
        $max_text_width = max($combined_text_width_title, $combined_text_width_date);

        // คำนวณตำแหน่ง X สำหรับจัดกึ่งกลางข้อความสองส่วน
        $center_x = ($page_width / 2) - ($max_text_width / 2);
        // ให้แน่ใจว่าไม่ชนโลโก้
        if ($center_x < ($logo_x + $logo_width + 5)) {
            $center_x = $logo_x + $logo_width + 5;
        }

        // ตั้งค่า X, Y และพิมพ์ชื่อรายงาน
        $this->SetXY($center_x, $text_y_aligned_with_logo - 5); // เลื่อนชื่อรายงานขึ้นไป 5mm
        $this->Cell(0, 10, $title_text, 0, 1, 'L', 0, '', 0, false, 'M', 'M'); // 1 ทำให้ขึ้นบรรทัดใหม่

        // พิมพ์ช่วงวันที่
        $this->SetX($center_x); // กลับมาที่ตำแหน่ง X เดียวกับชื่อรายงาน
        $this->SetFont('THSarabunNew', '', 11); // ลดขนาดฟอนต์ลงสำหรับช่วงวันที่
        $this->Cell(0, 10, $date_range_text, 0, 1, 'L', 0, '', 0, false, 'M', 'M'); // 1 ทำให้ขึ้นบรรทัดใหม่

        // --- 3. ลากเส้นคั่น (ถ้าต้องการ) ---
        // $this->Line($margin_left, $this->GetY() + 2, $page_right_edge, $this->GetY() + 2); // GetY() คือ Y ปัจจุบันหลังพิมพ์ช่วงวันที่

        // --- 4. กำหนด Y สำหรับเนื้อหาหลัก (ตาราง) ---
        // หาตำแหน่ง Y ที่ต่ำที่สุดใน Header หลังจากวาดทุกองค์ประกอบแล้ว
        // GetY() จะบอกตำแหน่ง Y ปัจจุบันหลังจากพิมพ์ Cell สุดท้าย
        $final_header_y = $this->GetY();
        $this->SetY($final_header_y); // เพิ่มระยะห่าง 5mm ก่อนตารางจะเริ่ม
    }

    // Page Footer
    public function Footer() {
        $this->SetY(-15); // Move to 15 mm from bottom
        $this->SetFont('THSarabunNew', '', 9); // ลดขนาดฟอนต์เล็กน้อยเพื่อความกระชับ

        // Timestamp (ซ้ายล่าง)
        $timestamp = date('d/m/Y H:i:s');
        $this->Cell(0, 10, 'พิมพ์เมื่อ: ' . $timestamp, 0, false, 'L', 0, '', 0, false, 'T', 'M');

        // Page Number (ขวาล่าง)
        $this->Cell(0, 10, 'หน้า ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}


// ===== 2. รับค่าพารามิเตอร์จาก GET =====
$start_date = $_GET["doc_date_start"] ?? '';
$end_date = $_GET["doc_date_to"] ?? '';
$pm = $_GET["payment_method"] ?? 'all';

$header_text = "รายงานรายการรายรับ-รายได้"; // ชื่อรายงานหลัก

// ===== 3. กำหนด payment_method สำหรับแสดงผล =====
$payment_method_sql = "";
$payment_method_display = "เงินสด และ โอนเงิน"; // ค่าเริ่มต้นสำหรับแสดงผล

if ($pm === "cash") {
    $payment_method_sql = " AND payment_method = 'เงินสด' ";
    $payment_method_display = 'เงินสด';
} elseif ($pm === "bank") {
    $payment_method_sql = " AND payment_method = 'โอนเงิน' ";
    $payment_method_display = 'โอนเงิน';
}

function formatRemark($remark)
{
    if (empty($remark)) {
        return '';
    }
    // ถ้าข้อความเป็นเลขที่บ้าน เช่น 67/8 หรือ 68/56 ให้ใส่คำว่า บ้านเลขที่ ตามด้วย remark
    if (preg_match('/^\d+\/\d+$/', trim($remark))) {
        return 'บ้านเลขที่ ' . $remark;
    }
    return $remark;
}

// ===== 4. สร้าง PDF ด้วยคลาสใหม่ MYPDF =====
$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('My System');
$pdf->SetAuthor('My System');
$pdf->SetTitle('รายงานรายการรายรับ');

// ส่งข้อมูลที่จำเป็นสำหรับ Header
$pdf->setReportInfo($header_text, $payment_method_display, $start_date, $end_date);

$pdf->setFooterFont(['THSarabunNew', '', 9]); // ตั้งค่าฟอนต์ Footer ให้สอดคล้องกับ Footer() method
// กำหนด Margin ทั้งหมด
// Top Margin ควรคำนวณจากความสูงของ Header ที่เรากำหนดใน Header() method
// Header เริ่มที่ Y=10, โลโก้สูง 10mm, ชื่อรายงาน 2 บรรทัดสูงรวมประมาณ 20mm
// ดังนั้น Header จะจบประมาณ 10 (start_y) + 10 (logo/cell) + 10 (second line) = 30mm
// เราต้องการให้มีช่องว่าง 5mm ก่อนตาราง
$calculated_top_margin = 35; // 10 (header_start_y) + (approx header content height) + 5 (padding to table)

$pdf->SetMargins(10, $calculated_top_margin, 10); // Left, Top, Right (ปรับ Top Margin ที่นี่)
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15); // Auto page break enabled with 15mm margin at bottom

$pdf->SetFont('THSarabunNew', '', 12); // ตั้งค่าฟอนต์หลักสำหรับเนื้อหา
$pdf->AddPage(); // เพิ่มหน้าแรก

// ===== 5. ดึงข้อมูลจากฐานข้อมูล =====

$table = "v_ims_reciepts";

$sql = "
    SELECT * FROM $table 
    WHERE 1=1 $payment_method_sql
    AND STR_TO_DATE(reciept_date, '%d-%m-%Y') 
        BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') 
        AND STR_TO_DATE(:end_date, '%d-%m-%Y')
    ORDER BY STR_TO_DATE(reciept_date, '%d-%m-%Y');    
";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();

// ===== 6. สร้าง HTML ตาราง =====
// ลบ H4 ออกจาก HTML เนื่องจากเราย้ายไปจัดการใน Header() method แล้ว
$html = '<table border="1" cellpadding="4" cellspacing="0" style="font-size:9pt;">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th width="5%" align="center">ลำดับ</th>
            <th width="8%" align="center">วันที่</th>
            <th width="12%" align="center">เลขที่เอกสาร</th>
            <th width="5%" align="center">ปี</th>
            <th width="18%" align="center">รายละเอียดรายรับ</th>
            <th width="13%" align="center">ผู้ชำระ</th>
            <th width="8%" align="center">วิธีชำระ</th>
            <th width="13%" align="center">หมายเหตุ</th>
            <th width="9%" align="center">จำนวนเงิน (บาท)</th>
            <th width="9%" align="center">สถานะ</th>            
        </tr>
    </thead>
    <tbody>'; // เพิ่ม tbody tag

$i = 1;
$total_amount = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $amount = (float)$row['amount'];
    $total_amount += $amount;
    $approve_status_desc = $row['approve_status']==="Y"?"ยืนยันการชำระ":"ยังไม่ยืนยัน";
    $html .= '<tr>
        <td width="5%">' . $i++ . '</td>
        <td width="8%">' . date('d/m/Y', strtotime($row['reciept_date'])) . '</td>
        <td width="12%">' . htmlspecialchars($row['doc_id'] ?? '') . '</td>
        <td width="5%">' . htmlspecialchars($row['rec_year'] ?? '') . '</td>        
        <td width="18%">' . htmlspecialchars($row['description'] ?? '') . '</td>
        <td width="13%">' . htmlspecialchars($row['supplier_name'] ?? '') . '</td>        
        <td width="8%">' . htmlspecialchars($row['payment_method'] ?? '') . '</td>
        <td width="13%">' . htmlspecialchars(formatRemark($row['remark'])) . '</td>
        <td width="9%" align="right">' . number_format($amount, 2) . '</td>
        <td width="9%">' . htmlspecialchars($approve_status_desc ?? '') . '</td>
    </tr>';
}

$html .= '</tbody>
    <tfoot>
        <tr style="background-color:#d9edf7; font-weight:bold;">
            <td width="5%"></td>
            <td width="8%"></td>            
            <td width="12%"></td>            
            <td width="5%"></td>
            <td width="18%"></td>
            <td width="13%"></td>
            <td width="8%"></td>
            <td width="13%" align="center">รวม</td>
            <td width="9%" align="right">' . number_format($total_amount, 2) . '</td>            
            <td width="9%"></td>
        </tr>
    </tfoot>
</table>';

// เพิ่มส่วนแสดงตัวอักษรยอดรวม
if (function_exists('number_to_thai_text') && $total_amount > 0) {
    $html .= '<p style="text-align: left; font-size:10pt;"><b>ตัวอักษร:</b> ' . number_to_thai_text($total_amount) . '</p>';
}


// ===== 7. สร้าง PDF Output =====
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('income_report_print.pdf', 'I');
?>