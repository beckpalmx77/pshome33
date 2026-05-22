<?php
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php');

$house_number = isset($_GET['house']) ? $_GET['house'] : '';
$start_year = 2025;
$start_month = 1;

$ref_year = isset($_GET['ref_year']) ? (int)$_GET['ref_year'] : (int)date('Y');
$ref_month = isset($_GET['ref_month']) ? (int)$_GET['ref_month'] : (int)date('m');

if (!$house_number) {
    die("ไม่พบข้อมูลบ้านเลขที่");
}

// ดึงข้อมูลบริษัท
$sql = "SELECT company_name, address_1, address_2, state, zip_code, phone, bank_name, bank_account_name, bank_account_no FROM ims_company LIMIT 1";
$company = $conn->query($sql)->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลบ้าน
$sql = "SELECT m.house_number, m.alley, m.area_size, m.common_fee, m.garbage_collection_fee,
               h.contact_name, h.phone_number
        FROM ims_house_master m
        LEFT JOIN ims_house h ON m.house_number = h.house_number
        WHERE m.house_number = :house AND m.status = 'Y'";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':house', $house_number, PDO::PARAM_STR);
$stmt->execute();
$house = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$house) {
    die("ไม่พบข้อมูลบ้านเลขที่ " . htmlspecialchars($house_number ?? ''));
}

// ดึงข้อมูลการชำระเงินของบ้านนี้
$sql_pay = "SELECT period_month_start, period_month_to, period_year, amount, payment_date
            FROM ims_house_payment
            WHERE house_number = :house AND payment_status = 'Y'
            ORDER BY period_year ASC, period_month_start ASC";
$stmt_pay = $conn->prepare($sql_pay);
$stmt_pay->bindParam(':house', $house_number, PDO::PARAM_STR);
$stmt_pay->execute();
$payments = $stmt_pay->fetchAll(PDO::FETCH_ASSOC);

// สร้าง paid_months array
$paid_months = [];
foreach ($payments as $p) {
    $start = (int)$p['period_month_start'];
    $end = (int)$p['period_month_to'];
    $year = (int)$p['period_year'];
    if ($start <= $end) {
        for ($m = $start; $m <= $end; $m++) {
            $paid_months[$year][$m] = true;
        }
    } else {
        for ($m = $start; $m <= 12; $m++) $paid_months[$year][$m] = true;
        for ($m = 1; $m <= $end; $m++) $paid_months[$year + 1][$m] = true;
    }
}

// คำนวณเดือนที่ค้างชำระ (ย้อนหลัง 48 เดือน)
$thai_months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];

$lookback_months = ($ref_year - $start_year) * 12 + ($ref_month - $start_month) + 1;
if ($lookback_months < 1) $lookback_months = 1;

$overdue_months = [];
$consecutive_unpaid = 0;
$total_overdue = 0;
$fee = (float)$house['common_fee'];

for ($i = 0; $i < $lookback_months; $i++) {
    $m = $ref_month - $i;
    $y = $ref_year;
    while ($m < 1) { $m += 12; $y--; }

    $paid = isset($paid_months[$y][$m]);
    if (!$paid) {
        $consecutive_unpaid++;
        $total_overdue += $fee;
        $overdue_months[] = [
            'year' => $y,
            'month' => $m,
            'month_name' => $thai_months[$m - 1],
            'fee' => $fee,
            'consecutive' => $consecutive_unpaid,
        ];
    } else {
        $consecutive_unpaid = 0;
    }
}

$overdue_months = array_reverse($overdue_months);

$max_consecutive = 0;
foreach ($overdue_months as $om) {
    if ($om['consecutive'] > $max_consecutive) $max_consecutive = $om['consecutive'];
}

if ($max_consecutive >= 1 && $max_consecutive <= 3) $dunning_level = 1;
elseif ($max_consecutive >= 4 && $max_consecutive <= 6) $dunning_level = 2;
elseif ($max_consecutive >= 7 && $max_consecutive <= 12) $dunning_level = 3;
else $dunning_level = 4;

$dunning_level_text = "";
if ($dunning_level == 1) $dunning_level_text = "ครั้งที่ 1";
elseif ($dunning_level == 2) $dunning_level_text = "ครั้งที่ 2";
elseif ($dunning_level == 3) $dunning_level_text = "ครั้งที่ 3";
else $dunning_level_text = "ครั้งสุดท้าย";

$thai_text_total = converNumberToThaiText($total_overdue);

// คำนวณวันที่
$ref_date_str = "1/" . $ref_month . "/" . ($ref_year + 543);
$doc_date_thai = date('d/m/', strtotime(date('Y-m-d'))) . (date('Y') + 543);

// กำหนดคลาส TCPDF
class DunningPDF extends TCPDF
{
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('THSarabunNew', '', 10);
        $this->Cell(0, 10, 'หน้า ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// สร้าง PDF
$pdf = new DunningPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetMargins(15, 10, 15);
$pdf->SetFont('THSarabunNew', '', 14);
$pdf->AddPage();

// ----- ส่วนหัวจดหมาย -----
$html = '
<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td width="70%" align="left" valign="top">
            <b style="font-size:16pt;">' . $company['company_name'] . '</b><br>
            <span style="font-size:12pt;">' . $company['address_1'] . ' ' . $company['address_2'] . ' ' . $company['state'] . ' ' . $company['zip_code'] . '</span><br>
            <span style="font-size:12pt;">โทร. ' . $company['phone'] . '</span>
        </td>
        <td width="30%" align="right" valign="top">
            <img src="img/logo/niti_ps33_header.png" height="50">
        </td>
    </tr>
</table>
<hr style="border-top: 2px solid #000; margin: 5px 0 15px 0;">
';

// วันที่
$html .= '
<table width="100%" cellspacing="0" cellpadding="2">
    <tr>
        <td align="right" style="font-size:13pt;">วันที่ ' . $doc_date_thai . '</td>
    </tr>
</table>
<br>';

// เรื่อง
$html .= '
<table width="100%" cellspacing="0" cellpadding="2">
    <tr>
        <td width="15%" valign="top" style="font-size:14pt;"><b>เรื่อง</b></td>
        <td width="85%" style="font-size:14pt;"><b>หนังสือทวงถามค่าส่วนกลาง ' . $dunning_level_text . '</b></td>
    </tr>
    <tr>
        <td valign="top" style="font-size:14pt;"><b>เรียน</b></td>
        <td style="font-size:14pt;"><b>' . htmlspecialchars($house['contact_name'] ?? 'เจ้าของบ้าน') . '</b></td>
    </tr>
</table>
<br>';

// เนื้อหา
$html .= '
<p style="font-size:14pt; line-height: 1.8;">
&nbsp;&nbsp;&nbsp;&nbsp;ตามที่ ' . $company['company_name'] . ' ได้มีมติเรียกเก็บค่าส่วนกลางจากสมาชิกเพื่อใช้ในการบริหารจัดการส่วนกลางของหมู่บ้าน นั้น<br><br>
&nbsp;&nbsp;&nbsp;&nbsp;เนื่องจากท่านยังมิได้ชำระค่าส่วนกลางสำหรับบ้านเลขที่ <b>' . htmlspecialchars($house['house_number'] ?? '') . '</b> ซอย <b>' . htmlspecialchars($house['alley'] ?? '') . '</b> เป็นจำนวนทั้งสิ้น <b>' . number_format($total_overdue, 2) . ' บาท (' . $thai_text_total . ')</b> รายละเอียดตามตารางเดือนที่ค้างชำระดังนี้
</p>
<br>';

// ตารางแสดงเดือนที่ค้าง
$html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%" style="font-size:12pt;">
    <tr style="background-color:#f2f2f2;">
        <th width="10%" align="center"><b>ลำดับ</b></th>
        <th width="25%" align="center"><b>ประจำเดือน</b></th>
        <th width="25%" align="center"><b>ประจำปี (พ.ศ.)</b></th>
        <th width="20%" align="center"><b>ค่าส่วนกลาง</b></th>
        <th width="20%" align="center"><b>หมายเหตุ</b></th>
    </tr>';

$seq = 1;
foreach ($overdue_months as $om) {
    $note = ($om['consecutive'] == $max_consecutive && $seq == count($overdue_months)) ? 'ค้างชำระ' : '';
    $html .= '<tr>
        <td align="center">' . ($seq++) . '</td>
        <td align="center">' . $om['month_name'] . '</td>
        <td align="center">' . ($om['year'] + 543) . '</td>
        <td align="right">' . number_format($om['fee'], 2) . '</td>
        <td align="center">' . $note . '</td>
    </tr>';
}

$html .= '<tr style="background-color:#f2f2f2;">
    <td colspan="3" align="right"><b>รวมค่าส่วนกลางที่ค้างชำระทั้งสิ้น</b></td>
    <td align="right"><b>' . number_format($total_overdue, 2) . '</b></td>
    <td align="center"><b>บาท</b></td>
</tr>';
$html .= '</table>';
$html .= '<br><br>';

// ส่วนท้าย - ขอให้ชำระเงิน
$html .= '
<p style="font-size:14pt; line-height: 1.8;">
&nbsp;&nbsp;&nbsp;&nbsp;จึงเรียนมาเพื่อโปรดชำระค่าส่วนกลางที่ค้างชำระดังกล่าว ภายใน <b>15 วัน</b> นับแต่วันที่ได้รับหนังสือฉบับนี้ หากพ้นกำหนดดังกล่าวแล้วทางนิติบุคคลจำเป็นต้องดำเนินการตามขั้นตอนทางกฎหมายต่อไป
</p>
<br><br>';

// ลายเซ็น
$signature_path = 'img_sig/approved.png';
$signature_img = (file_exists($signature_path))
    ? '<img src="' . $signature_path . '" height="35">'
    : '_____________________________';

$html .= '
<table width="100%" cellspacing="0" cellpadding="5">
    <tr>
        <td width="50%" align="center">
            <br><br><br>
            ' . $signature_img . '<br>
            (................................................)<br>
            <b>ผู้จัดการนิติบุคคลหมู่บ้านพฤกษา 33</b>
        </td>
        <td width="50%" align="center">
            <br><br><br>
            _____________________________<br>
            (................................................)<br>
            <b>พยาน</b>
        </td>
    </tr>
</table>
<br><br>
<p style="font-size:11pt; text-align:center;">
    หมายเหตุ : กรุณานำหนังสือฉบับนี้มาชำระที่สำนักงานนิติบุคคลฯ' .
    (!empty($company['bank_name']) ? ' หรือ โอนเงินเข้าบัญชี<br>ธนาคาร ' . $company['bank_name'] . ' บัญชี ' . $company['bank_account_name'] . ' เลขที่บัญชี ' . $company['bank_account_no'] : '') . '
</p>';

$pdf->writeHTML($html, true, false, false, false, '');

$filename = 'dunning_' . $house_number . '_' . date('Ymd') . '.pdf';
$pdf->Output($filename, 'I');
?>
