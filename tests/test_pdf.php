<?php
require_once('../vendor/autoload.php');

include('../config/connect_db.php');

// สร้างอ็อบเจ็กต์ mPDF
require_once __DIR__ . '../vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf();

// ดึงข้อมูลจากฐานข้อมูล
$query = "SELECT * FROM memployee ";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// สร้างเนื้อหาของรายงาน PDF
$html = '<h1>รายงาน PDF จากฐานข้อมูล</h1>';
$html .= '<table>';
$html .= '<tr><th>ชื่อ</th><th>อีเมล์</th><th>โทรศัพท์</th></tr>';

foreach ($result as $row) {
    $html .= '<tr>';
    $html .= '<td>' . $row['emp_id'] . '</td>';
    $html .= '<td>' . $row['f_name'] . '</td>';
    $html .= '<td>' . $row['l_name'] . '</td>';
    $html .= '</tr>';
}

$html .= '</table>';

// เพิ่มเนื้อหาในรายงาน PDF
$mpdf->WriteHTML($html);

// สร้างไฟล์ PDF และส่งให้บราวเซอร์ดาวน์โหลด
$mpdf->Output('report.pdf', 'D');