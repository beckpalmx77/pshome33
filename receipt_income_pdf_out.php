<?php
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php');

// ===== 1. กำหนดคลาส TCPDF ใหม่ เพื่อเพิ่ม Timestamp =====
class MYPDF extends TCPDF {
    public function Footer() {
        // Move to 15 mm from bottom
        $this->SetY(-15);
        $this->SetFont('freeserif', '', 10);
        $timestamp = date('d/m/Y H:i:s');
        $this->Cell(0, 10, 'พิมพ์เมื่อ: ' . $timestamp, 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 10, 'หน้าที่ ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

// ===== 2. รับค่าพารามิเตอร์จาก GET =====
$start_date = $_GET["doc_date_start"] ?? '';
$end_date = $_GET["doc_date_to"] ?? '';
$pm = $_GET["payment_method"] ?? 'all';

$header_text = "รายการรายรับ-รายได้";

// ===== 3. เงื่อนไข payment_method =====
$payment_method_sql = "";
$payment_method = "เงินสด - โอนเงิน";

if ($pm === "cash") {
    $payment_method_sql = " AND payment_method = 'เงินสด' ";
    $payment_method = 'เงินสด';
} elseif ($pm === "bank") {
    $payment_method_sql = " AND payment_method = 'โอนเงิน' ";
    $payment_method = 'โอนเงิน';
}

// ===== 4. สร้าง PDF ด้วยคลาสใหม่ MYPDF =====
//$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('My System');
$pdf->SetAuthor('My System');
$pdf->SetTitle('รายงานรายการรายรับ');

// ปิด Header ดั้งเดิม
$pdf->setPrintHeader(false);
$pdf->setFooterFont(['freeserif', '', 10]);
$pdf->SetMargins(5, 10, 5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('freeserif', '', 12);
$pdf->AddPage();

// ใส่โลโก้ที่ด้านบนซ้าย
$pdf->Image(__DIR__ . '/img/logo/PS33Logo-01.png', 5, 5, 20);
$pdf->Ln(15); // เว้นระยะห่างหลังโลโก้

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
$html = '<h4 style="text-align:center;">' . $header_text . ' (' . $payment_method . ')<br>ช่วงวันที่ ' . $start_date . ' ถึง ' . $end_date . '</h4>';
$html .= '<table border="1" cellpadding="4" cellspacing="0">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th width="5%" align="center">ลำดับ</th>
            <th width="10%" align="center">วันที่</th>
            <th width="5%" align="center">ปี</th>
            <th width="30%" align="center">รายละเอียดรายรับ</th>
            <th width="15%" align="center">ผู้ชำระ</th>
            <th width="10%" align="center">วิธีชำระ</th>
            <th width="15%" align="center">จำนวนเงิน (บาท)</th>
            <th width="10%" align="center">สถานะ</th>            
        </tr>
    </thead>';

$i = 1;
$total_amount = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $amount = (float)$row['amount'];
    $total_amount += $amount;
    $approve_status_desc = $row['approve_status']==="Y"?"ยืนยันการชำระ":"ยังไม่ยืนยัน";
    $html .= '<tr>
        <td width="5%">' . $i++ . '</td>
        <td width="10%">' . date('d/m/Y', strtotime($row['reciept_date'])) . '</td>
        <td width="5%">' . htmlspecialchars($row['rec_year']) . '</td>        
        <td width="30%">' . htmlspecialchars($row['description']) . '</td>
        <td width="15%">' . htmlspecialchars($row['supplier_name']) . '</td>        
        <td width="10%">' . htmlspecialchars($row['payment_method']) . '</td>
        <td width="15%" align="right">' . number_format($amount, 2) . '</td>
        <td width="10%">' . htmlspecialchars($approve_status_desc) . '</td>
    </tr>';
}

$html .= '</tbody>
    <tfoot>
        <tr style="background-color:#d9edf7; font-weight:bold;">
            <td width="5%"></td>
            <td width="10%"></td>            
            <td width="5%"></td>
            <td width="30%"></td>
            <td width="15%"></td>
            <td width="10%" align="center">รวม</td>
            <td width="15%" align="right">' . number_format($total_amount, 2) . '</td>            
            <td width="10%"></td>
        </tr>
    </tfoot>
</table>';

// ===== 7. สร้าง PDF Output =====
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('income_report_print.pdf', 'I');
?>
