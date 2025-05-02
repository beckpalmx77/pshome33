<?php
require_once 'vendor/autoload.php'; // โหลด TCPDF ผ่าน Composer

// สร้าง PDF ใหม่
$pdf = new \TCPDF();

// ตั้งค่าขนาดหน้าและการจัดรูปแบบ
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('ตัวอย่าง PDF ภาษาไทย');
$pdf->SetSubject('TCPDF Example');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// ตั้งค่าหน้ากระดาษ
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->AddPage();

// เพิ่มฟอนต์ THSarabunNew
$fontPath = 'font/THSarabunNew.ttf'; // ระบุที่เก็บฟอนต์
$pdf->AddFont('THSarabunNew', '', $fontPath, '', 'true');
$pdf->SetFont('THSarabunNew', '', 16);

// เนื้อหา PDF
$html = '
<h1 style="text-align:center;">ใบเสร็จรับเงิน</h1>
<p>บริษัท XYZ จำกัด</p>
<p>ที่อยู่: 123/45 ถนนตัวอย่าง แขวง/ตำบล ตัวอย่าง เขต/อำเภอ ตัวอย่าง จังหวัด ตัวอย่าง 10110</p>
<p>โทร: 02-123-4567</p>
<table border="1" cellpadding="5">
    <tr>
        <th>รายการ</th>
        <th>จำนวน</th>
        <th>ราคา</th>
    </tr>
    <tr>
        <td>สินค้า A</td>
        <td>2</td>
        <td>500 บาท</td>
    </tr>
    <tr>
        <td>สินค้า B</td>
        <td>1</td>
        <td>300 บาท</td>
    </tr>
</table>
<p style="text-align:right;">รวมทั้งหมด: 1,300 บาท</p>
';

// เขียน HTML ลงใน PDF
$pdf->writeHTML($html, true, false, true, false, '');

// แสดง PDF ในเบราว์เซอร์
$pdf->Output('example.pdf', 'I'); // 'I' เพื่อแสดงในเบราว์เซอร์, 'D' เพื่อดาวน์โหลด
