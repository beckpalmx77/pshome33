<?php
session_start();
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
include('../config/connect_db.php');
include('../util/number_to_thai_text.php');

date_default_timezone_set('Asia/Bangkok');

// เปลี่ยนการรับค่าจาก 'id' เป็น 'doc_no'
$doc_no = $_GET["doc_no"] ?? ''; // รับ doc_no จาก URL (GET parameter)

// ถ้าไม่มี doc_no ส่งมา ให้แจ้งเตือน
if (empty($doc_no)) {
    echo "<script>alert('ไม่พบเลขที่เอกสารจัดซื้อ โปรดตรวจสอบ'); window.history.back();</script>";
    exit();
}

$filename_prefix = $doc_no; // เปลี่ยน prefix ตามเอกสารจัดซื้อ

// เปลี่ยน SQL Query ให้ดึงข้อมูลจาก ims_purchase และ ims_purchase_detail
$sql = "SELECT
            ip.doc_no,
            ip.doc_date,
            ip.purpose,
            ip.requester,
            ip.supplier_name,
            ip.total_amount AS master_total_amount,
            ipd.line_no,
            ipd.product_name,
            ipd.quantity,
            ipd.unit_name,
            ipd.price,
            ipd.total_price AS item_total_price
        FROM ims_purchase ip
        JOIN ims_purchase_detail ipd ON ip.doc_no = ipd.doc_no
        WHERE ip.doc_no = :doc_no
        ORDER BY ipd.line_no ASC";

$params = [':doc_no' => $doc_no]; // ใช้ doc_no เป็น parameter

try {
    $query = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $query->bindValue($key, $value, PDO::PARAM_STR);
    }
    $query->execute();
    $purchase_data = $query->fetchAll(PDO::FETCH_ASSOC); // เปลี่ยนชื่อตัวแปรเป็น purchase_data

} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูลจัดซื้อ: " . $e->getMessage());
}

if (empty($purchase_data)) {
    echo "<script>alert('ไม่พบข้อมูลสำหรับเลขที่เอกสาร " . $doc_no . " โปรดตรวจสอบ'); window.history.back();</script>";
    exit();
}

// ดึงข้อมูล Master Record (แถวแรก) เพื่อใช้ใน Header
$master_data = $purchase_data[0];
$report_doc_date = $master_data['doc_date'];
$report_purpose = $master_data['purpose'];
$report_requester = $master_data['requester'];
$report_supplier_name = $master_data['supplier_name'];
$report_master_total_amount = $master_data['master_total_amount'];


// กำหนดหัวตารางและคอลัมน์กว้าง (ใช้สำหรับสร้าง HTML table)
$pdf_headers = [
    "ลำดับ", // เพิ่มลำดับ
    "รายการสินค้า", // product_name
    "จำนวน",      // quantity
    "หน่วย",      // unit_name
    "ราคาต่อหน่วย", // price
    "ราคารวม"     // total_price
];
$col_widths = [
    '8%',  // ลำดับ
    '42%', // รายการสินค้า
    '10%', // จำนวน
    '10%', // หน่วย
    '15%', // ราคาต่อหน่วย
    '15%'  // ราคารวม
];


// --- Extend TCPDF เพื่อสร้าง Header และ Footer ของตัวเอง ---
class MYPDF extends TCPDF {
    protected $report_doc_no;
    protected $report_doc_date;
    protected $report_purpose;
    protected $report_requester;
    protected $report_supplier_name;
    protected $logo_path = '../img/logo/ps33-rec-logo-1xx.png'; // Path to your logo image

    // Constructor
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
    }

    // Method to set report dates
    public function setReportData($doc_no, $doc_date, $purpose, $requester, $supplier_name) {
        $this->report_doc_no = $doc_no;
        $this->report_doc_date = $doc_date;
        $this->report_purpose = $purpose;
        $this->report_requester = $requester;
        $this->report_supplier_name = $supplier_name;
    }

    // Page header: This method is called automatically for each new page
    public function Header() {
        $page_width = $this->getPageWidth(); // ความกว้างของหน้ากระดาษ
        $margin_left = 10; // กำหนดค่า margin ซ้าย (ควรตรงกับ SetMargins)
        $margin_right = 10; // กำหนดค่า margin ขวา (ควรตรงกับ SetMargins)

        $logo_y = 10; // ตำแหน่ง Y เริ่มต้นของโลโก้
        $logo_width = 20;
        $logo_height = 10;

        // --- 1. Logo (ซ้ายบน) ---
        if (file_exists($this->logo_path)) {
            $this->Image($this->logo_path, $margin_left, $logo_y, $logo_width, $logo_height, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            $this->SetFont('THSarabunNew', 'B', 10);
            $this->SetXY($margin_left, $logo_y + ($logo_height / 4)); // ปรับให้ข้อความ No Logo อยู่กึ่งกลาง
            $this->Cell($logo_width, $logo_height/2, 'No Logo', 0, 0, 'C');
        }

        // --- 2. Report Title (อยู่กลางหน้ากระดาษ) ---
        // เริ่มต้นชื่อรายงานใต้โลโก้ + ระยะห่าง
        $title_start_y = $logo_y + $logo_height + 2; // 2mm padding after logo
        $this->SetY($title_start_y);
        $this->SetFont('THSarabunNew', 'B', 18);
        $title_text = 'ใบขอจัดซื้อ / จัดจ้าง (Purchase Request / Order)';
        $title_width = $this->GetStringWidth($title_text);
        $this->SetX(($page_width / 2) - ($title_width / 2));
        $this->Cell($title_width, 10, $title_text, 0, 1, 'C', 0, '', 0, false, 'M', 'M');

        // --- ข้อมูลหลักของเอกสารจัดซื้อ ---
        $this->SetFont('THSarabunNew', '', 12);
        // เริ่มต้นบล็อกข้อมูลเอกสารหลังจากชื่อรายงาน + ระยะห่าง
        $doc_details_start_y = $this->GetY() + 2; // 2mm padding after title
        $line_height = 5;

        // Doc No
        $this->SetXY($margin_left, $doc_details_start_y);
        $this->Cell(40, $line_height, 'เลขที่เอกสาร (Doc No.):', 0, 0, 'L');
        $this->SetFont('THSarabunNew', 'B', 12);
        $this->Cell(60, $line_height, $this->report_doc_no, 0, 0, 'L');
        $this->SetFont('THSarabunNew', '', 12);

        // Doc Date
        $this->SetX($margin_left + 100);
        $this->Cell(40, $line_height, 'วันที่เอกสาร (Doc Date):', 0, 0, 'L');
        $this->SetFont('THSarabunNew', 'B', 12);
        $this->Cell(0, $line_height, $this->report_doc_date, 0, 1, 'L');


        // Requester
        $this->SetXY($margin_left, $this->GetY());
        $this->Cell(40, $line_height, 'ผู้ขอเบิก/จัดซื้อ:', 0, 0, 'L');
        $this->SetFont('THSarabunNew', 'B', 12);
        $this->Cell(60, $line_height, $this->report_requester, 0, 0, 'L');
        $this->SetFont('THSarabunNew', '', 12);

        // Supplier Name
        $this->SetX($margin_left + 100);
        $this->Cell(40, $line_height, 'ผู้ขาย/ผู้ให้บริการ:', 0, 0, 'L');
        $this->SetFont('THSarabunNew', 'B', 12);
        $this->Cell(0, $line_height, $this->report_supplier_name, 0, 1, 'L');

        // Purpose
        $this->SetXY($margin_left, $this->GetY());
        $this->Cell(40, $line_height, 'วัตถุประสงค์:', 0, 0, 'L');
        $this->SetFont('THSarabunNew', 'B', 12);
        $this->Cell(0, $line_height, $this->report_purpose, 0, 1, 'L');

        // กำหนด Y สำหรับเนื้อหาหลัก (ตาราง)
        $final_header_y = $this->GetY(); // ใช้ GetY() หลังจากเขียนข้อมูลส่วนหัวทั้งหมด
        $this->SetY($final_header_y + 3); // เพิ่มระยะห่าง 3mm (เล็กน้อย) จากส่วนหัว
    }

    // Page footer
    public function Footer() {
        $page_width = $this->getPageWidth();
        $margin_left = 10;
        $margin_right = 10;

        // Signature blocks
        $signature_block_y = -35; // Start signature blocks higher up, e.g., 35mm from bottom
        $line_length = 50; // Length of the signature line
        $text_offset_y = 5; // Offset for text below the line
        $block_padding = 40; // Space between blocks

        $this->SetFont('THSarabunNew', '', 10); // Set font for signature labels

        // ผู้จัดทำ (Prepared by)
        $prepared_by_x = $margin_left;
        $this->SetXY($prepared_by_x, $signature_block_y);
        $this->Cell($line_length, 0, '__________________________________', 0, 0, 'C'); // Signature line
        $this->SetXY($prepared_by_x, $signature_block_y + $text_offset_y);
        $this->Cell($line_length, 0, '(ผู้จัดทำ)', 0, 0, 'C');

        // ผู้อนุมัติ (Approved by)
        $approved_by_x = $margin_left + $line_length + $block_padding;
        $this->SetXY($approved_by_x, $signature_block_y);
        $this->Cell($line_length, 0, '__________________________________', 0, 0, 'C'); // Signature line
        $this->SetXY($approved_by_x, $signature_block_y + $text_offset_y);
        $this->Cell($line_length, 0, '(ผู้อนุมัติ)', 0, 0, 'C');


        // Page number and print date
        $this->SetY(-15); // Set Y back to lower part for page number and print date
        $this->SetFont('THSarabunNew', '', 8);
        $this->Cell(0, 10, 'หน้า ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'L');

        $print_date_text = 'วันที่พิมพ์: ' . date('d/m/Y H:i:s');
        $this->SetX(-$margin_right - $this->GetStringWidth($print_date_text)); // Calculate X to align right
        $this->Cell(0, 10, $print_date_text, 0, 0, 'R');
    }
}

// --- TCPDF Initialization ---
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false); // เปลี่ยนเป็นแนวตั้ง 'P'

$pdf->setReportData(
    $master_data['doc_no'],
    $master_data['doc_date'],
    $master_data['purpose'],
    $master_data['requester'],
    $master_data['supplier_name']
);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('ใบขอจัดซื้อ / จัดจ้าง (Purchase Request / Order) เลขที่ ' . $doc_no);
$pdf->SetSubject('รายงานสรุปจัดซื้อ/จัดจ้าง');

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// --- KEY FIX: Adjust Top Margin Here ---
// ปรับ top margin ให้มีที่ว่างสำหรับ Header ที่มีข้อมูลมากขึ้น
$top_margin_for_header = 45; // อาจจะต้องปรับค่านี้ให้เหมาะสมกับการแสดงผลจริง

$pdf->SetMargins(10, $top_margin_for_header, 10); // (Left, Top, Right)

// Set auto page break
$pdf->SetAutoPageBreak(TRUE, 15); // กำหนด Bottom Margin

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

$grand_total_amount_detail = 0; // ยอดรวมจากรายละเอียด

foreach ($purchase_data as $row) {
    // คำนวณ total_price จาก quantity * price
    $calculated_item_total_price = (float)($row['quantity'] ?? 0) * (float)($row['price'] ?? 0);

    // รวมยอดจาก calculated_item_total_price
    $grand_total_amount_detail += $calculated_item_total_price;

    $html_table .= '<tr>';
    // Column 1: ลำดับ (line_no)
    $html_table .= '<td width="' . $col_widths[0] . '" align="center">' . ($row['line_no'] ?? '') . '</td>';

    // Column 2: รายการสินค้า (product_name)
    $html_table .= '<td width="' . $col_widths[1] . '">' . ($row['product_name'] ?? '') . '</td>';

    // Column 3: จำนวน (quantity)
    $html_table .= '<td width="' . $col_widths[2] . '" align="right">' . number_format($row['quantity'] ?? 0, 2) . '</td>';

    // Column 4: หน่วย (unit_name)
    $html_table .= '<td width="' . $col_widths[3] . '" align="center">' . ($row['unit_name'] ?? '') . '</td>';

    // Column 5: ราคาต่อหน่วย (price)
    $html_table .= '<td width="' . $col_widths[4] . '" align="right">' . number_format($row['price'] ?? 0, 2) . '</td>';

    // Column 6: ราคารวม (calculated_item_total_price)
    $html_table .= '<td width="' . $col_widths[5] . '" align="right"><b>' . number_format($calculated_item_total_price, 2) . '</b></td>';
    $html_table .= '</tr>';
}

$html_table .= '<tr>
    <td colspan="5" align="right"><b>รวมยอดทั้งสิ้น:</b></td>
    <td width="' . $col_widths[5] . '" align="right"><b>' . number_format($grand_total_amount_detail, 2) . '</b></td>
</tr>';

$html_table .= '</tbody></table>';

$pdf->writeHTML($html_table, true, false, true, false, '');

// แสดงตัวอักษรของยอดรวม ถ้ามี
if (function_exists('number_to_thai_text') && $grand_total_amount_detail > 0) {
    $pdf->SetY($pdf->GetY() + 5);
    $pdf->SetFont('THSarabunNew', '', 10);
    $pdf->writeHTMLCell(0, 0, '', '', '<p style="text-align: left;"><b>ตัวอักษร:</b> ' . number_to_thai_text($grand_total_amount_detail) . '</p>', 0, 1, 0, true, '', true);
}


$filename = $filename_prefix . "_" . date('Ymd_His') . ".pdf";
$pdf->Output($filename, 'I');
exit;