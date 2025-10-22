<?php
require_once('../vendor/tcpdf/tcpdf.php');
include('../config/connect_db.php');

define('PROMPT_REGULAR', TCPDF_FONTS::addTTFfont('../font/Prompt-Regular.ttf', 'TrueTypeUnicode'));
define('PROMPT_BOLD', TCPDF_FONTS::addTTFfont('../font/Prompt-Bold.ttf', 'TrueTypeUnicode'));

// สร้างอ็อบเจ็กต์ TCPDF
//$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');

// create new PDF document
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 001');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

$pdf->setPrintHeader(false);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

$pdf->SetFont(PROMPT_BOLD, '', 14);


$pdf->SetTitle('ตัวอย่างรายงาน PDF');
$pdf->SetAuthor('ชื่อผู้สร้างรายงาน');
$pdf->SetSubject('ตัวอย่างรายงาน PDF');
$pdf->SetKeywords('ตัวอย่าง, รายงาน, PDF');

// Print text
$pdf->Write(0, 'เมียโหดโมโหผัว คว้าฉมวกแทงเป้าแล้วบิด อวัยวะเพศเหวอะ เย็บ 17 เข็ม ด้านผัวยังไม่กล้ากลับบ้าน รอเมียใจเย็นก่อน

(3 มี.ค. 65) ผู้สื่อข่าวได้รับแจ้งจากชาวบ้านหมู่ 20 ต.ตาจง อ.ละหานทราย จ.บุรีรัมย์ ว่ามีสองผัวเมียในหมู่บ้านชอบทะเลาะกันถึงขั้นใช้ฉมวกแทงปลาแทงอวัยวะเพศ ต้องเย็บถึง 17 เข็ม
');


$pdf->SetFont(PROMPT_REGULAR, '', 14);
$query = "SELECT * FROM memployee ";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

//$html = '<h1>รายงาน PDF จากฐานข้อมูล</h1>';
//$html .= '<table>';
//$html .= '<tr><th>ชื่อ</th><th>อีเมล์</th><th>โทรศัพท์</th></tr>';

foreach ($result as $row) {
    $html .= '<tr>';
    $html .= '<td>' . $row['emp_id'] . '</td>';
    $html .= '<td>' . $row['f_name'] . '</td>';
    $html .= '<td>' . $row['l_name'] . '</td>';
    $html .= '</tr>';
}

$html .= '</table>';
$pdf->Write(0,$html );

// Print text
//$pdf->Write(0, '
//จากการตรวจสอบเบื้องต้นบ้านหลังที่เกิดเหตุปลูกเป็นลักษณะกระท่อมปลายนา พบนางชนิตา อายุ 43 ปี ซึ่งเมื่อพบผู้สื่อข่าวที่จะเข้าไปสัมภาษณ์ได้เปิดฉากตำหนิสามีต่างๆ นานา ระบุว่าสามีชอบกินเหล้าแล้วหาเรื่องเป็นประจำตั้งแต่อยู่กันมา 21 ปี ตนต้องทนต่อสามีที่มาหาเรื่องทุกครั้งที่เมาสุรา

//วันเกิดเหตุคือเมื่อคืนที่ผ่านมาเหมือนเช่นเดิม สามีเมาเหล้าแล้วมาชวนทะเลาะอีกจนพยายามพูดดีแต่ไม่เคยได้ผลจึงเกิดการทะเลาะกันอีก แต่ครั้งนี้ตนยอมรับว่าเหนื่อยจากการไปรับจ้างตัดอ้อยจะทนไม่ไหวที่สามีเข้ามาบีบคอตนเองก่อนจึงตัดสินใจเอามือคว้าอวัยวะเพศแล้วบิดเพื่อเป็นการตอบโต้ จนกระทั่งสามีบอกให้ปล่อยแล้ววิ่งหนีไป ยืนยันไม่คิดจะเอาอาวุธไปเชือนอวัยวะเพศของสามีแต่อย่างใด');

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.

$pdf->Output('hello-tcpdf.pdf', 'I');
$pdf->Close();