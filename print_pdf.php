<?php

require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';

$id = isset($_GET['id']) ? $_GET['id'] : ''; // รับค่า ID จาก URL

// ตรวจสอบว่าได้รับค่า ID มาหรือไม่
if (!$id) {
    die("ไม่พบข้อมูล");
}

$sql = "SELECT company_name, address_1, address_2, state, zip_code, phone FROM ims_company LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลใบเสร็จจากฐานข้อมูล (ใช้หมายเลขใบเสร็จเป็นตัวอย่าง)

$stmt = $conn->prepare("SELECT * FROM v_ims_house_payment WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$receipt = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงรายการสินค้าในใบเสร็จ
$stmt_items = $conn->prepare("SELECT * FROM v_ims_house_payment WHERE id = :id");
$stmt_items->bindParam(':id', $id, PDO::PARAM_INT);
$stmt_items->execute();
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// สร้าง PDF ด้วย TCPDF
$pdf = new TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->SetMargins(10, 10, 10);

$pdf->SetCreator('Your Company');
$pdf->SetAuthor('Your Company');
$pdf->SetTitle('ใบเสร็จรับเงิน');
$pdf->SetSubject('Receipt');
$pdf->SetKeywords('Receipt, Invoice, PDF');

// ใช้ฟอนต์ภาษาไทย (THSarabunNew)
$pdf->SetFont('THSarabunNew', '', 14);

// เพิ่มหน้า PDF
$pdf->AddPage();

// ส่วนหัวใบเสร็จ
$html = '
<h2 style="text-align:center;">ใบเสร็จรับเงิน</h2>
<div style="text-align:center;">
    <img src="img/logo/Logo-01-1.png" width="50">
</div>
<table border="0" cellspacing="0" cellpadding="4">
    <tr>        
        <td><b>' . $company['company_name'] . '</b></td>
        <td align="right"><b>เลขที่ใบเสร็จ:</b> ' . $receipt['doc_id'] . '</td>
    </tr>
    <tr>
        <td><b>ที่อยู่:</b> 123 ถนนตัวอย่าง กรุงเทพฯ</td>
        <td align="right"><b>วันที่:</b> ' . date('d/m/Y', strtotime($receipt['payment_date'])) . '</td>
    </tr>
</table>';

// ตารางรายการสินค้า
$html .= '<table border="1" cellspacing="0" cellpadding="5">
    <tr style="background-color:#f2f2f2;">
        <th width="10%" align="center"><b>#</b></th>
        <th width="70%" align="center"><b>รายการ</b></th>
        <th width="10%" align="center"><b>จำนวน</b></th>
        <th width="15%" align="center"><b>ราคา</b></th>
    </tr>';

$total = 0;
foreach ($items as $index => $item) {

    $period_month = $receipt['month_name_start'] == $receipt['month_name_to'] ? $receipt['month_name_start'] : $receipt['month_name_start'] . " - " . $receipt['month_name_to'];

    $html .= '<tr>
        <td align="center">' . ($index + 1) . '</td>        
        <td><b>ค่าส่วนกลาง งวดเดือน </b> ' . $period_month . ' ' . $receipt['period_year'] . '</td>        
        <td align="right">1</td>
        <td align="right">' . number_format($item['amount'], 2) . '</td>
    </tr>';
    $total += $item['amount'];
}

// สรุปยอดเงิน
$html .= '<tr>
    <td colspan="3" align="right"><b>รวมทั้งสิ้น:</b></td>
    <td align="right"><b>' . number_format($total, 2) . '</b></td>
</tr>';

$html .= '</table><br><br>';

// ลายเซ็น
$html .= '<table border="0" cellspacing="0" cellpadding="5">
    <tr>
        <td align="right"><b>ลงชื่อผู้รับเงิน</b> ________________________</td>
    </tr>
</table>';

// แสดง HTML บน PDF
$pdf->writeHTML($html, true, false, false, false, '');

// ส่งออกไฟล์ PDF
$pdf->Output('receipt.pdf', 'I');