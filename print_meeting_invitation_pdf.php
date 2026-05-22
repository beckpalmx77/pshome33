<?php
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include('config/connect_db.php');

$preview = isset($_GET['preview']) && $_GET['preview'] == '1';
$meeting_year = isset($_REQUEST['meeting_year']) ? $_REQUEST['meeting_year'] : '';
$meeting_date = isset($_REQUEST['meeting_date']) ? $_REQUEST['meeting_date'] : '';

if (!$preview && (empty($meeting_year) || empty($meeting_date))) {
    die("กรุณาระบุปีและวันที่ประชุม");
}

$meeting_day = isset($_POST['meeting_day']) ? $_POST['meeting_day'] : '';
$meeting_time = isset($_POST['meeting_time']) ? $_POST['meeting_time'] : '';
$meeting_location = isset($_POST['meeting_location']) ? $_POST['meeting_location'] : '';

$agenda_items = array();
for ($i = 1; $i <= 7; $i++) {
    $key = 'agenda_' . $i;
    $val = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    if ($val !== '') {
        $agenda_items[] = $val;
    }
}

$sql_company = "SELECT company_name, address_1, address_2, state, zip_code, phone FROM ims_company LIMIT 1";
$stmt = $conn->prepare($sql_company);
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if ($preview) {
    $meeting_name = '';
    if (empty($meeting_year)) $meeting_year = date('Y') + 543;
    if (empty($meeting_date)) $meeting_date = '15/05/' . (date('Y') + 543);
    if (empty($meeting_day)) $meeting_day = 'วันอาทิตย์ที่ 22 มิถุนายน';
    if (empty($meeting_time)) $meeting_time = '10.00 – 12.00 น.';
    if (empty($meeting_location)) $meeting_location = 'สำนักงานนิติบุคคลหมู่บ้านจัดสรรพฤกษา 33';
} else {
    $sql_meeting = "SELECT * FROM v_ims_house_meeting 
                    WHERE meeting_year = :meeting_year 
                    AND meeting_date = :meeting_date 
                    AND status = 'Y'
                    ORDER BY CAST(alley AS UNSIGNED) ASC, house_number ASC";
    $stmt = $conn->prepare($sql_meeting);
    $stmt->bindValue(':meeting_year', $meeting_year);
    $stmt->bindValue(':meeting_date', $meeting_date);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        die("ไม่พบข้อมูลการประชุมสำหรับปี $meeting_year วันที่ $meeting_date");
    }

    $first_row = $results[0];
    $meeting_name = $first_row['meeting_name'];
}

$date_parts = explode('/', $meeting_date);
$day_thai = isset($date_parts[0]) ? ltrim($date_parts[0], '0') : '';
$month_thai = isset($date_parts[1]) ? ltrim($date_parts[1], '0') : '';
$year_thai = isset($date_parts[2]) ? $date_parts[2] : '';

$thai_months = array(
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
    4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
    7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
    10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
);
$month_name = isset($thai_months[(int)$month_thai]) ? $thai_months[(int)$month_thai] : $month_thai;
$meeting_date_thai = "$day_thai $month_name $year_thai";

class InvitationPDF extends TCPDF
{
    public function Footer() {}
}

$pdf = new InvitationPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(12, 5, 12);
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetFont('THSarabunNew', '', 12);
$pdf->AddPage();

$logo_path = 'img/header/niti_ps33_header.png';
$logo_html = file_exists($logo_path)
    ? '<img src="' . $logo_path . '" style="width:80px;height:auto;">'
    : '';

$company_name = $company['company_name'] ?? 'หมู่บ้านพฤกษา 33';
$company_addr = '';
if ($company) {
    $parts = array_filter([$company['address_1'], $company['address_2'], $company['state']]);
    $company_addr = implode(' ', $parts);
    if (!empty($company['zip_code'])) $company_addr .= ' ' . $company['zip_code'];
    if (!empty($company['phone'])) $company_addr .= ' โทร. ' . $company['phone'];
}

$html_header = '
<table width="100%" cellpadding="2" cellspacing="0" style="border-bottom: 2px solid #333; margin-bottom: 3px;">
    <tr>
        <td width="22%" align="center" valign="middle">' . $logo_html . '</td>
        <td width="78%" align="center" valign="middle">
            <h1 style="font-size: 16pt; margin: 0;">' . $company_name . '</h1>
            <p style="font-size: 10pt; margin: 1px 0;">' . $company_addr . '</p>
        </td>
    </tr>
</table>';

$title_suffix = $preview ? ' (ตัวอย่าง)' : '';
$html_content = '
<p style="font-size: 12pt;"><b>เรื่อง</b> ขอเชิญเข้าร่วมประชุมวิสามัญ ประจำปี ' . $meeting_year . '</p>
<p style="font-size: 12pt;"><b>เรียน</b> ท่านเจ้าของร่วมหมู่บ้านจัดสรรพฤกษา 33 ทุกท่าน</p>
<br>
<p style="font-size: 12pt; text-indent: 2em;">
    ด้วยนิติบุคคลหมู่บ้านจัดสรรพฤกษา 33 จะจัดให้มีการประชุมวิสามัญประจำปี ' . $meeting_year . '
    เพื่อแจ้งให้ท่านทราบถึงนโยบาย แนวทางการดำเนินงาน และการชี้แจงเรื่องต่าง ๆ ที่เกี่ยวข้องกับการบริหารภายในหมู่บ้าน 
    จึงใคร่ขอเรียนเชิญท่านเข้าร่วมประชุม ใน' . $meeting_day . ' ' . $meeting_year . ' เวลา ' . $meeting_time . '
</p>
<p style="font-size: 12pt; text-indent: 2em;">ณ ' . $meeting_location . '</p>
<br>
<p style="font-size: 12pt; text-indent: 2em;">
    ขอให้ท่านลงทะเบียนเข้าร่วมประชุมระหว่างเวลา 09.30 – 10.00 น. และเริ่มประชุมในเวลา 10.00 น. 
    โดยมีระเบียบวาระการประชุมดังนี้
</p>
<br>
<p style="font-size: 12pt;"><b>ระเบียบวาระการประชุม</b></p>
<ol style="font-size: 12pt; margin: 0; padding-left: 20px;">';

if (!empty($agenda_items)) {
    foreach ($agenda_items as $item) {
        $html_content .= '<li>' . htmlspecialchars($item ?? '') . '</li>';
    }
} else {
    $html_content .= '
    <li>เรื่องแจ้งเพื่อทราบ</li>
    <li>เรื่องชี้แจงการดำเนินการของคณะกรรมการ</li>
    <li>เรื่องพิจารณา</li>
    <li>เรื่องอื่น ๆ (ถ้ามี)</li>';
}

$html_content .= '
</ol>
<br>
<p style="font-size: 12pt; text-indent: 2em;">จึงเรียนมาเพื่อโปรดเข้าร่วมประชุมตามวัน เวลา และสถานที่ดังกล่าวโดยพร้อมเพรียง</p>
<br>';

$html_signature = '
<table width="100%" cellpadding="2" cellspacing="0" style="font-size: 12pt;">
    <tr>
        <td align="center">
            ขอแสดงความนับถือ<br><br>
            ลงชื่อ......................................................<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(.........................................................)<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ประธานกรรมการนิติบุคคลหมู่บ้านจัดสรรพฤกษา 33<br><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;วันที่ ........../............./................
        </td>
    </tr>
</table>';

$remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';
if (!empty($remark)) {
    $html_signature .= '<hr style="margin-top: 5px; margin-bottom: 3px;">';
    $html_signature .= '<p style="font-size: 10pt; margin: 0;">' . htmlspecialchars($remark ?? '') . '</p>';
}

$pdf->writeHTML($html_header . $html_content . $html_signature, true, false, true, false, '');

$filename = 'หนังสือเชิญประชุม_' . $meeting_year . '_' . $meeting_date . '.pdf';
$pdf->Output($filename, 'I');
