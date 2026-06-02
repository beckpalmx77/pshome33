<?php
// เริ่มต้น Output Buffering เพื่อป้องกัน whitespace หรือ echo จากไฟล์อื่นมาทำลาย Header ของ PDF
ob_start();

require_once('../vendor/tcpdf/tcpdf.php');

// ใช้ไฟล์เชื่อมต่อฐานข้อมูลที่มีอยู่ (ระวังเรื่อง echo ในไฟล์เชื่อมต่อ)
// ในที่นี้จะลองใช้ connect_db_tests.php แต่จะใช้ ob_clean ล้างค่าที่มัน echo ออกมา
include('connect_db_tests.php');

// สร้างอ็อบเจ็กต์ TCPDF
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// ตั้งค่าข้อมูลพื้นฐานของเอกสาร
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('System Admin');
$pdf->SetTitle('รายงานภาษาไทย TCPDF');
$pdf->SetSubject('Thai Language Support');

// ปิด Header และ Footer (ถ้าไม่ต้องการ)
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// ตั้งค่าขอบกระดาษ
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// เพิ่มหน้าใหม่
$pdf->AddPage();

// --- แก้ไขปัญหาภาษาไทย ---
// ใช้ font 'freeserif' ซึ่งเป็น font มาตรฐานของ TCPDF ที่รองรับ Unicode/ภาษาไทย ได้ดีมาก
// หากต้องการใช้ Prompt ต้องมั่นใจว่าไฟล์ .ttf อยู่ในที่ที่ระบุ และ addTTFfont ทำงานสำเร็จ
$pdf->SetFont('freeserif', 'B', 16);

// เขียนหัวข้อ
$pdf->Cell(0, 10, 'ตัวอย่างรายงาน PDF ภาษาไทย', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('freeserif', '', 12);

// ข้อความตัวอย่าง
$text = 'เมียโหดโมโหผัว คว้าฉมวกแทงเป้าแล้วบิด อวัยวะเพศเหวอะ เย็บ 17 เข็ม ด้านผัวยังไม่กล้ากลับบ้าน รอเมียใจเย็นก่อน

(3 มี.ค. 65) ผู้สื่อข่าวได้รับแจ้งจากชาวบ้านหมู่ 20 ต.ตาจง อ.ละหานทราย จ.บุรีรัมย์ ว่ามีสองผัวเมียในหมู่บ้านชอบทะเลาะกันถึงขั้นใช้ฉมวกแทงปลาแทงอวัยวะเพศ ต้องเย็บถึง 17 เข็ม';

$pdf->MultiCell(0, 10, $text, 0, 'L');
$pdf->Ln(10);

// ดึงข้อมูลจากฐานข้อมูล (ใช้ตัวแปร $conn จาก connect_db_tests.php)
try {
    $query = "SELECT * FROM memployee LIMIT 20";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // เตรียม HTML Table
    $html = '<table border="1" cellpadding="5">
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <th width="15%">รหัส</th>
                    <th width="40%">ชื่อ</th>
                    <th width="45%">นามสกุล</th>
                </tr>';

    if (empty($result)) {
        $html .= '<tr><td colspan="3" align="center">ไม่พบข้อมูลพนักงาน</td></tr>';
    } else {
        foreach ($result as $row) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['emp_id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['f_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['l_name']) . '</td>';
            $html .= '</tr>';
        }
    }
    $html .= '</table>';

    // ใช้ writeHTML เพื่อแสดงผลตาราง
    $pdf->writeHTML($html, true, false, true, false, '');

} catch (Exception $e) {
    $pdf->Write(0, 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
}

// ล้าง Output Buffer ที่อาจมี echo ค้างอยู่ (เช่น "Connect DB" จากไฟล์ config)
// เพื่อให้ PDF Header ส่งออกไปได้อย่างถูกต้อง
ob_end_clean();

// ส่งออกไฟล์ PDF
// 'I' = Inline (แสดงในเบราว์เซอร์)
// 'D' = Download
$pdf->Output('report_thai.pdf', 'I');
?>
