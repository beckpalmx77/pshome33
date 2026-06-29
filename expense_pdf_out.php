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
        $margin_left = $this->lMargin;
        $margin_right = $this->rMargin;
        $page_right_edge = $page_width - $margin_right;

        $header_start_y = 15; // เริ่มต้นที่ 15mm จากขอบบน

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

        // --- 2. Report Title & Date Range (อยู่กลางหน้ากระดาษ) ---
        $text_y_aligned_with_logo = $logo_y + ($logo_height / 2) - 5;

        $this->SetFont('THSarabunNew', 'B', 16);
        $title_text = $this->report_header_text . ' (' . $this->report_payment_method . ')';
        $date_range_text = 'ช่วงวันที่ ' . $this->report_start_date . ' ถึง ' . $this->report_end_date;

        $combined_text_width_title = $this->GetStringWidth($title_text);
        $combined_text_width_date = $this->GetStringWidth($date_range_text);
        $max_text_width = max($combined_text_width_title, $combined_text_width_date);

        $center_x = ($page_width / 2) - ($max_text_width / 2);
        if ($center_x < ($logo_x + $logo_width + 5)) {
            $center_x = $logo_x + $logo_width + 5;
        }

        // ตั้งค่า X, Y และพิมพ์ชื่อรายงาน
        $this->SetXY($center_x, $text_y_aligned_with_logo - 5);
        $this->Cell(0, 10, $title_text, 0, 1, 'L', 0, '', 0, false, 'M', 'M');

        // พิมพ์ช่วงวันที่
        $this->SetX($center_x);
        $this->SetFont('THSarabunNew', '', 11);
        $this->Cell(0, 10, $date_range_text, 0, 1, 'L', 0, '', 0, false, 'M', 'M');

        $final_header_y = $this->GetY();
        $this->SetY($final_header_y);
    }

    // Page Footer
    public function Footer() {
        $this->SetY(-15); // Move to 15 mm from bottom
        $this->SetFont('THSarabunNew', '', 9);

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

$header_text = "รายงานรายการรายจ่าย-ค่าใช้จ่าย"; // ชื่อรายงานหลัก

// ===== 3. กำหนด payment_method สำหรับแสดงผล =====
$payment_method_sql = "";
$payment_method_display = "เงินสด และ โอนเงิน";

if ($pm === "cash") {
    $payment_method_sql = " AND payment_method = 'เงินสด' ";
    $payment_method_display = 'เงินสด';
} elseif ($pm === "bank") {
    $payment_method_sql = " AND payment_method = 'โอนเงิน' ";
    $payment_method_display = 'โอนเงิน';
}

// ===== 4. สร้าง PDF ด้วยคลาสใหม่ MYPDF =====
$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('My System');
$pdf->SetAuthor('My System');
$pdf->SetTitle('รายงานรายการรายจ่าย');

// ส่งข้อมูลที่จำเป็นสำหรับ Header
$pdf->setReportInfo($header_text, $payment_method_display, $start_date, $end_date);

$pdf->setFooterFont(['THSarabunNew', '', 9]);
$calculated_top_margin = 35;

$pdf->SetMargins(10, $calculated_top_margin, 10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);

$pdf->SetFont('THSarabunNew', '', 12);
$pdf->AddPage();

// ===== 5. ดึงข้อมูลจากฐานข้อมูล =====
$table = "v_ims_expenses";

$sql = "
    SELECT * FROM $table 
    WHERE 1=1 $payment_method_sql
    AND CASE 
        WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
        WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
        ELSE NULL 
    END BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') AND STR_TO_DATE(:end_date, '%d-%m-%Y')
    ORDER BY CASE 
        WHEN expense_date REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(expense_date, '%Y-%m-%d')
        WHEN expense_date REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' THEN STR_TO_DATE(expense_date, '%d-%m-%Y')
        ELSE NULL 
    END ASC;
";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();

// ===== 6. สร้าง HTML ตาราง =====
$html = '<table border="1" cellpadding="4" cellspacing="0" style="font-size:9pt;">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th width="5%" align="center">ลำดับ</th>
            <th width="8%" align="center">วันที่</th>
            <th width="12%" align="center">เลขที่เอกสาร</th>
            <th width="5%" align="center">ปี</th>
            <th width="18%" align="center">รายละเอียดค่าใช้จ่าย</th>
            <th width="10%" align="center">หมวดหมู่</th>
            <th width="13%" align="center">จ่ายให้</th>
            <th width="8%" align="center">วิธีชำระ</th>
            <th width="12%" align="center">หมายเหตุ</th>
            <th width="9%" align="center">จำนวนเงิน (บาท)</th>
        </tr>
    </thead>
    <tbody>';

$i = 1;
$total_amount = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $amount = (float)$row['amount'];
    $total_amount += $amount;

    $expense_date_formatted = $row['expense_date'];
    if (!empty($row['expense_date'])) {
        $date_obj = DateTime::createFromFormat('Y-m-d', $row['expense_date']);
        if (!$date_obj) {
            $date_obj = DateTime::createFromFormat('d-m-Y', $row['expense_date']);
        }
        if ($date_obj) {
            $expense_date_formatted = $date_obj->format('d/m/Y');
        }
    }

    $html .= '<tr>
        <td width="5%">' . $i++ . '</td>
        <td width="8%">' . $expense_date_formatted . '</td>
        <td width="12%">' . htmlspecialchars($row['doc_id'] ?? '') . '</td>
        <td width="5%">' . htmlspecialchars($row['exp_year'] ?? '') . '</td>        
        <td width="18%">' . htmlspecialchars($row['description'] ?? '') . '</td>
        <td width="10%">' . htmlspecialchars($row['category_name'] ?? '') . '</td>        
        <td width="13%">' . htmlspecialchars($row['receipt_name'] ?? '') . '</td>        
        <td width="8%">' . htmlspecialchars($row['payment_method'] ?? '') . '</td>
        <td width="12%">' . htmlspecialchars($row['remark'] ?? '') . '</td>
        <td width="9%" align="right">' . number_format($amount, 2) . '</td>
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
            <td width="10%"></td>
            <td width="13%"></td>
            <td width="8%"></td>
            <td width="12%" align="center">รวม</td>
            <td width="9%" align="right">' . number_format($total_amount, 2) . '</td>            
        </tr>
    </tfoot>
</table>';

// เพิ่มส่วนแสดงตัวอักษรยอดรวม
if (function_exists('number_to_thai_text') && $total_amount > 0) {
    $html .= '<p style="text-align: left; font-size:10pt;"><b>ตัวอักษร:</b> ' . number_to_thai_text($total_amount) . '</p>';
}

// ===== 7. สร้าง PDF Output =====
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('expense_report_print.pdf', 'I');
?>
