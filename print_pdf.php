<?php
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php');

// ตรวจสอบค่า ID
$id = isset($_GET['id']) ? $_GET['id'] : '';
if (!$id) {
    die("ไม่พบข้อมูล");
}

// ดึงข้อมูลบริษัท
$sql = "SELECT company_name, address_1, address_2, state, zip_code, phone FROM ims_company LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลใบเสร็จ
$stmt = $conn->prepare("SELECT * FROM v_ims_house_payment WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$receipt = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงรายการ
$stmt_items = $conn->prepare("SELECT * FROM v_ims_house_payment WHERE id = :id");
$stmt_items->bindParam(':id', $id, PDO::PARAM_INT);
$stmt_items->execute();
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// รวมยอด
$total = 0;
foreach ($items as $item) {
    $total += $item['amount'];
}
$thai_text_total = converNumberToThaiText($total);

// กำหนดคลาส TCPDF ใหม่เพื่อสร้าง footer
class CustomPDF extends TCPDF
{
    public $printed_by = '';

    public function Footer()
    {
        // ไม่ต้องทำ footer ของ TCPDF เพราะเรากำหนด Footer ใน HTML แล้ว
    }
}

// สร้าง PDF
$pdf = new CustomPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->printed_by = isset($_SESSION['user_name']) ? 'ผู้พิมพ์: ' . $_SESSION['user_name'] : 'ผู้พิมพ์: ฝ่ายการเงิน';
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);

// ลด margin เพื่อไม่ให้ล้นหน้า
$pdf->SetMargins(8, 5, 8);

// ลดขนาดฟอนต์
$pdf->SetFont('THSarabunNew', '', 12);

$pdf->AddPage();

// -----------------------------------------------------------------------------
// ฟังก์ชันสร้าง HTML สำหรับใบเสร็จ
// -----------------------------------------------------------------------------
function generate_receipt_html($company, $receipt, $items, $total, $thai_text_total, $title_note = '')
{
    $full_name = $_SESSION['first_name'] . " " . $_SESSION['last_name'];
    $user_signature = isset($_SESSION['user_signature']) ? $_SESSION['user_signature'] : '';
    $signature_path = $user_signature ? 'img_sig/' . $user_signature : '';
    $signature_img = $user_signature && file_exists($signature_path)
        ? '<img src="' . $signature_path . '" height="30">'
        : '____________';

    // --- ส่วนหัวกระดาษ ---
    $html = '
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:10px; margin-top:10px;">
        <tr>
            <td width="70%" align="left" valign="top">
                <b style="font-size:14pt;">' . $company['company_name'] . '</b><br>
                <span style="font-size:12pt;">' . $company['address_1'] . ' ' . $company['address_2'] . ' ' . $company['state'] . ' ' . $company['zip_code'] . '</span><br>
                <img src="img/logo/niti_ps33_header.png" height="40" style="display: block; margin-top: 5px;">
            </td>
            
            <td width="30%" align="right" valign="top">
                <h2 style="margin-top: 0;">ใบเสร็จรับเงิน ' . $title_note . '</h2>
            </td>
        </tr>
    </table>

    <table border="0" cellspacing="0" cellpadding="4" width="100%" style="font-size:12pt;">
        <tr>        
            <td width="60%" valign="top">
                <b>บ้านเลขที่ : ' . $receipt['house_number'] . ' ซอย : ' . $receipt['alley'] . ' &nbsp; ผู้ชำระเงิน : ' . $receipt['detail'] . '</b>
            </td>            
            <td width="40%" align="right" valign="top">
                <b>เลขที่ใบเสร็จ: ' . $receipt['doc_id'] . '</b><br>  
                <b>วันที่: ' . date('d/m/Y', strtotime($receipt['payment_date'])) . '</b>
            </td>
        </tr>
    </table>';

    // --- ส่วนตารางรายการ ---
    $html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%" style="table-layout: fixed; font-size:12pt;">
        <tr style="background-color:#f2f2f2;">
            <th width="10%" align="center"><b>#</b></th>
            <th width="65%" align="center"><b>รายการ</b></th>
            <th width="10%" align="center"><b>จำนวนงวด</b></th>
            <th width="15%" align="center"><b>จำนวนเงิน</b></th>
        </tr>';

    foreach ($items as $index => $item) {
        $period_month = $receipt['month_name_start'] == $receipt['month_name_to']
            ? $receipt['month_name_start']
            : $receipt['month_name_start'] . " - " . $receipt['month_name_to'];

        $html .= '<tr>
            <td align="center">' . ($index + 1) . '</td>
            <td><b>ค่าส่วนกลาง งวดเดือน </b><b> ' . $period_month . ' ' . $receipt['period_year']+543 . '</b> </td>
            <td align="right">' . ( $receipt['payment_type']) . '</td>
            <td align="right"><b>' . number_format($item['amount'], 2) . '</b></td>
        </tr>';
    }

    $html .= '<tr>
    <td colspan="2" align="left"><b>วิธีการชำระเงิน : ' . $receipt['payment_method'] . '</b></td>
    <td align="right"><b>รวมทั้งสิ้น:</b></td>
    <td align="right"><b>' . number_format($total, 2) . '</b></td>
</tr>';

    $html .= '<tr>
        <td colspan="4" align="right"><i><b>( ' . $thai_text_total . ' )</b></i></td>
    </tr>';

    $html .= '</table><br><br>';

    $html .= '<table border="0" cellspacing="0" cellpadding="5" width="100%" style="margin-top:20px; margin-bottom:20px; font-size:12pt;">
<tr>
    <!--td align="left"><b>ผู้ชำระเงิน</b> _______________ (' . $receipt['detail'] . ')</td-->
    <td align="left"></td>
    <td align="center">
        <b>ผู้รับเงิน</b><br>
        ' . $signature_img . '<br>
        (' . $full_name . ')  &nbsp; &nbsp; &nbsp;ตำแหน่ง: เจ้าหน้าที่นิติฯ
    </td>
</tr>
<tr>
    <td align="left" style="font-size:10pt;">
        วันที่พิมพ์: ' . date('d/m/Y H:i') . '
    </td>
    <td align="right" style="font-size:10pt;">
        ผู้พิมพ์: ' . (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'เจ้าหน้าที่นิติฯ') . '
    </td>
</tr>
</table>';

    return $html;
}

// -----------------------------------------------------------------------------
// เริ่มการประกอบร่าง HTML
// -----------------------------------------------------------------------------

// 1. ส่วนต้นฉบับ
$html = generate_receipt_html($company, $receipt, $items, $total, $thai_text_total, "(ต้นฉบับ)");

// 2. ตัวแบ่งและเว้นวรรค
$html .= '<br>'; // เว้นบรรทัดก่อนเส้น
$html .= '<hr style="border-top: dashed 1px; margin: 10px 0;">'; // เส้นประ

// *** จุดแก้ไข: เพิ่ม div สูง 30px เพื่อดันหัวข้อสำเนาลงมาให้ห่างจากเส้น ***
$html .= '<div style="line-height: 30px;">&nbsp;</div>';

// 3. ส่วนสำเนา
$html .= generate_receipt_html($company, $receipt, $items, $total, $thai_text_total, "(สำเนา)");


// เขียนลง PDF
$pdf->writeHTML($html, true, false, false, false, '');

// อัปเดตสถานะการพิมพ์
$print_status = $receipt['print_status'];
if ($print_status == 'N') {
    $stmt_items = $conn->prepare("UPDATE ims_house_payment 
                                  SET print_status = 'Y', print_first_date = NOW() 
                                  WHERE id = :id AND print_status = 'N'");
} else if ($print_status == 'Y') {
    $stmt_items = $conn->prepare("UPDATE ims_house_payment 
                                  SET print_last_date = NOW() 
                                  WHERE id = :id AND print_status = 'Y'");
}
$stmt_items->bindParam(':id', $id, PDO::PARAM_INT);
$stmt_items->execute();

// สร้างชื่อไฟล์
$filename = 'receipt_' . $receipt['doc_id'] . '_' . date('Ymd_His') . '.pdf';
$pdf->Output($filename, 'I');
?>