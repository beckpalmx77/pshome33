<?php
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php');

date_default_timezone_set('Asia/Bangkok'); // Set timezone

// ===== 1. Define the custom TCPDF class with custom Header and Footer =====
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

    // Method to set report information from outside the class
    public function setReportInfo($header_text, $payment_method, $start_date, $end_date) {
        $this->report_header_text = $header_text;
        $this->report_payment_method = $payment_method;
        $this->report_start_date = $start_date;
        $this->report_end_date = $end_date;
    }

    // Page Header
    public function Header() {
        $page_width = $this->getPageWidth();
        $margin_left = $this->lMargin; // Get left margin set in SetMargins
        $margin_right = $this->rMargin; // Get right margin set in SetMargins
        $page_right_edge = $page_width - $margin_right;

        // Determine the starting Y for the header content
        $header_start_y = 10; // Start at 10mm from top edge (Can be adjusted)

        // --- 1. Logo (Top Left) ---
        $logo_x = $margin_left;
        $logo_y = $header_start_y;
        $logo_width = 20;
        $logo_height = 10; // Fixed logo height
        if (file_exists($this->logo_path)) {
            $this->Image($this->logo_path, $logo_x, $logo_y, $logo_width, $logo_height, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            // Fallback if logo not found
            $this->SetFont('THSarabunNew', 'B', 10);
            $this->SetXY($logo_x, $logo_y + ($logo_height / 4));
            $this->Cell($logo_width, $logo_height / 2, 'No Logo', 0, 0, 'C');
        }

        // --- 2. Report Title & Date Range (Centered, aligned with logo) ---
        $text_y_center_aligned_with_logo = $logo_y + ($logo_height / 2) - 5; // -5 to shift first line up

        $this->SetFont('THSarabunNew', 'B', 16);
        $title_line1 = $this->report_header_text;
        $title_line2 = '(' . $this->report_payment_method . ')';
        $date_range_line = 'ช่วงวันที่ ' . $this->report_start_date . ' ถึง ' . $this->report_end_date;

        // Get approximate widths to calculate max width for centering
        $width_line1 = $this->GetStringWidth($title_line1);
        $width_line2 = $this->GetStringWidth($title_line2);
        $width_date_range = $this->GetStringWidth($date_range_line);
        $max_text_width = max($width_line1, $width_line2, $width_date_range);

        // Calculate X position for centering, considering left margin
        $center_x = ($page_width / 2) - ($max_text_width / 2);
        // Ensure it doesn't overlap with the logo
        if ($center_x < ($logo_x + $logo_width + 5)) {
            $center_x = $logo_x + $logo_width + 5; // Move right 5mm from logo's right edge
        }

        // Print first line of title
        $this->SetXY($center_x, $text_y_center_aligned_with_logo);
        $this->Cell(0, 10, $title_line1, 0, 1, 'L', 0, '', 0, false, 'M', 'M'); // `1` for new line

        // Print second line of title
        $this->SetX($center_x); // Reset X to align with the first line
        $this->Cell(0, 10, $title_line2, 0, 1, 'L', 0, '', 0, false, 'M', 'M'); // `1` for new line

        // Print date range
        $this->SetX($center_x); // Reset X to align
        $this->SetFont('THSarabunNew', '', 11); // Slightly smaller font for date range
        $this->Cell(0, 10, $date_range_line, 0, 1, 'L', 0, '', 0, false, 'M', 'M'); // `1` for new line

        // --- 3. Set Y for main content (table) ---
        // Get current Y position after printing all header elements
        $final_header_y = $this->GetY();
        // --- FIX START: ลดระยะห่างตรงนี้ ---
        $this->SetY($final_header_y + 2); // ลดจาก 5mm เป็น 2mm (หรือค่าอื่นตามต้องการ)
        // --- FIX END ---
    }

    // Page Footer
    public function Footer() {
        $this->SetY(-15); // Move to 15 mm from bottom
        $this->SetFont('THSarabunNew', '', 9); // Slightly smaller font for compactness

        // Timestamp (bottom left)
        $timestamp = date('d/m/Y H:i:s');
        $this->Cell(0, 10, 'พิมพ์เมื่อ: ' . $timestamp, 0, false, 'L', 0, '', 0, false, 'T', 'M');

        // Page Number (bottom right)
        $this->Cell(0, 10, 'หน้าที่ ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}


// ===== 2. Get parameters from GET =====
$start_date = $_GET["doc_date_start"] ?? '';
$end_date = $_GET["doc_date_to"] ?? '';
$pm = $_GET["payment_method"] ?? 'all';

$header_text = "รายการรับเงินค่าส่วนกลาง"; // Main report title

// ===== 3. Determine payment_method for display and SQL condition =====
$payment_method_sql = "";
$payment_method_display = "เงินสด และ โอนเงิน"; // Default display value

if ($pm === "cash") {
    $payment_method_sql = " AND payment_method = 'เงินสด' ";
    $payment_method_display = 'เงินสด';
} elseif ($pm === "bank") {
    $payment_method_sql = " AND payment_method = 'โอนเงิน' ";
    $payment_method_display = 'โอนเงิน';
}

// ===== 4. Create PDF with the custom MYPDF class =====
// Using 'L' for Landscape orientation as per your previous code
$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('My System');
$pdf->SetAuthor('My System');
$pdf->SetTitle('รายงานรายการรายรับค่าส่วนกลาง'); // Updated Title

// Set report information for the custom Header
$pdf->setReportInfo($header_text, $payment_method_display, $start_date, $end_date);

$pdf->setFooterFont(['THSarabunNew', '', 9]); // Set Footer font to match Footer() method

// Calculate Top Margin for the main content (table)
// Header starts at $header_start_y (10mm).
// Logo is 10mm high.
// Title + Payment Method are two lines, approx 20mm high combined (2 * 10mm cell height).
// Date range is one line, approx 10mm high.
// Total header content height is approx 10 (logo) + 3 * 10 (text lines) = 40mm from top edge.
// If we want 2mm padding after header, total top margin = approx 40 + 2 = 42mm.
// However, TCPDF's SetMargins sets the *fixed* margin for all pages.
// We are setting Y dynamically in Header(). So this value for SetMargins()
// should be set to allow enough room for the header to draw.
// The previous 45mm was already allowing more than enough room.
// We can set it to the actual calculated final Y from Header() plus a bit more
// to be safe for auto page breaks. Let's keep it around 40-45 to be safe.
$calculated_top_margin = 30; // Adjusted to be closer, based on new padding of 2mm

$pdf->SetMargins(10, $calculated_top_margin, 10); // Left, Top, Right (Adjust Top Margin here)
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15); // Auto page break enabled with 15mm margin at bottom

$pdf->SetFont('THSarabunNew', '', 12); // Main font for content
$pdf->AddPage(); // Add the first page

// ===== 5. Fetch data from database =====
$sql = "
    SELECT * FROM v_ims_house_payment
    WHERE STR_TO_DATE(payment_date, '%d-%m-%Y')
          BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y')
          AND STR_TO_DATE(:end_date, '%d-%m-%Y')
          $payment_method_sql
    ORDER BY STR_TO_DATE(payment_date, '%d-%m-%Y');
";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();

// ===== 6. Build HTML Table =====
$html = '<table border="1" cellpadding="4" cellspacing="0" style="font-size:9pt;">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th width="6%" align="center">ลำดับ</th>
            <th width="12%" align="center">วันที่รับชำระ</th>
            <th width="10%" align="center">บ้านเลขที่</th>
            <th width="12%" align="center">เดือนเริ่ม</th>
            <th width="12%" align="center">เดือนสิ้นสุด</th>
            <th width="7%" align="center">ปี</th>
            <th width="14%" align="center">จำนวนเงิน</th>
            <th width="17%" align="center">สถานะ</th>
            <th width="10%" align="center">วิธีชำระ</th>
        </tr>
    </thead>
    <tbody>';

$i = 1;
$total_amount = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $amount = (float)$row['amount'];
    $total_amount += $amount;

    $html .= '<tr>
        <td width="6%">' . $i++ . '</td>
        <td width="12%">' . date('d/m/Y', strtotime($row['payment_date'])) . '</td>
        <td width="10%">' . htmlspecialchars($row['house_number']) . '</td>
        <td width="12%">' . htmlspecialchars($row['month_name_start']) . '</td>
        <td width="12%">' . htmlspecialchars($row['month_name_to']) . '</td>
        <td width="7%" align="center">' . htmlspecialchars($row['period_year']) . '</td>
        <td width="14%" align="right">' . number_format($amount, 2) . '</td>
        <td width="17%">' . htmlspecialchars($row['payment_status_desc']) . '</td>
        <td width="10%">' . htmlspecialchars($row['payment_method']) . '</td>
    </tr>';
}

$html .= '</tbody>
    <tfoot>
        <tr style="background-color:#d9edf7; font-weight:bold;">
            <td width="6%"></td>
            <td width="12%"></td>
            <td width="10%"></td>
            <td width="12%"></td>
            <td width="12%"></td>
            <td width="7%" align="center">รวม</td>
            <td width="14%" align="right">' . number_format($total_amount, 2) . '</td>
            <td width="17%"></td>
            <td width="10%"></td>
        </tr>
    </tfoot>
</table>';

// Add total amount in Thai text
if (function_exists('number_to_thai_text') && $total_amount > 0) {
    $html .= '<p style="text-align: left; font-size:10pt;"><b>ตัวอักษร:</b> ' . number_to_thai_text($total_amount) . '</p>';
}

// ===== 7. Generate PDF Output =====
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('payment_report_print.pdf', 'I');
?>