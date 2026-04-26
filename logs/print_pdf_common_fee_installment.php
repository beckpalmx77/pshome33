<?php
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php');

// ตรวจสอบค่า installment_id ที่ส่งมา
$installment_id_param = isset($_GET['installment_id']) ? $_GET['installment_id'] : '';
if (!$installment_id_param) {
    die("ไม่พบข้อมูล installment_id ที่จำเป็นสำหรับการพิมพ์ใบเสร็จ");
}

// ดึงข้อมูลบริษัท
$sql = "SELECT company_name, address_1, address_2, state, zip_code, phone FROM ims_company LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลหลักจาก ims_installment
$stmt_main = $conn->prepare("SELECT * FROM ims_installment WHERE installment_id = :installment_id");
$stmt_main->bindParam(':installment_id', $installment_id_param, PDO::PARAM_STR);
$stmt_main->execute();
$main_installment = $stmt_main->fetch(PDO::FETCH_ASSOC);

if (!$main_installment) {
    die("ไม่พบข้อมูลการผ่อนชำระสำหรับ Installment ID: " . htmlspecialchars($installment_id_param));
}

// เตรียมรายการสำหรับใบเสร็จ (เน้น down_payment เป็นหลัก)
$items = [];
$total = 0;

// เพิ่มเงินทำสัญญาเป็นรายการหลัก
if ($main_installment['down_payment'] > 0) {
    $items[] = [
        'description' => 'เงินทำสัญญา',
        'quantity' => 1,
        'amount' => $main_installment['down_payment'],
        // ใช้วันที่เอกสารเป็นวันที่ชำระสำหรับ down_payment
        'payment_date' => $main_installment['doc_date'],
        'payment_method' => 'เงินสด/โอนเงิน' // วิธีการชำระเงินทั่วไปสำหรับ down_payment
    ];
    $total = $main_installment['down_payment']; // ยอดรวมคือ down_payment เท่านั้น
}

$thai_text_total = converNumberToThaiText($total);

// กำหนดคลาส TCPDF ใหม่เพื่อสร้าง footer
class CustomPDF extends TCPDF
{
    public $printed_by = '';

    public function Footer()
    {
        // ไม่ต้องทำ footer เพราะกำหนดใน HTML แล้ว
    }
}

// สร้าง PDF
$pdf = new CustomPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->printed_by = isset($_SESSION['user_name']) ? 'ผู้พิมพ์: ' . $_SESSION['user_name'] : 'ผู้พิมพ์: ฝ่ายการเงิน';
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);

// ลด margin เพื่อไม่ให้ล้นหน้า
$pdf->SetMargins(8, 5, 8);

// ลดขนาดฟอนต์เริ่มต้นของ PDF
$pdf->SetFont('THSarabunNew', '', 11);

$pdf->AddPage();

// ฟังก์ชันสร้าง HTML สำหรับใบเสร็จ
// แก้ไขลำดับ parameter: ย้าย $pdf_obj ให้เป็น parameter สุดท้าย (required parameter ควรอยู่ก่อน optional parameter)
function generate_receipt_html($company, $main_installment, $items, $total, $thai_text_total, $title_note = '', $pdf_obj = null)
{
    $full_name = $_SESSION['first_name'] . " " . $_SESSION['last_name'];
    $signature_img_html = '____________';

    $html = '
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:5px; margin-top:5px; text-align:center;">
        <tr>
            <td>
                <h2 style="margin-bottom: 3px; font-size:16pt;">ใบเสร็จรับเงิน ' . $title_note . '</h2>
                <img src="img/logo/niti_ps33_header.png" height="35" style="display: block; margin: 0 auto;">
            </td>
        </tr>
    </table>

    <table border="0" cellspacing="0" cellpadding="2" width="100%" style="font-size:11pt;">
        <tr>        
            <td><b>' . htmlspecialchars($company['company_name']) . '</b></td>
            <td align="right"><b>เลขที่ใบเสร็จ:</b> ' . htmlspecialchars($main_installment['installment_id']) . '</td>
        </tr>
        <tr>
            <td><b>ที่อยู่:</b> ' . htmlspecialchars($company['address_1']) . ' ' . htmlspecialchars($company['address_2']) . ' ' . htmlspecialchars($company['state']) . ' ' . htmlspecialchars($company['zip_code']) . '</td>
            <td align="right"><b>วันที่:</b> ' . date('d/m/Y', strtotime($main_installment['doc_date'])) . '</td>
        </tr>
        <tr>
            <td><b>บ้านเลขที่:</b> ' . htmlspecialchars($main_installment['house_number']) . '</td>
            <td align="right"><b>ผู้ทำสัญญา/ผ่อนชำระ:</b> ' . htmlspecialchars($main_installment['debtor']) . '</td>
        </tr>
    </table>';

    $html .= '<table border="1" cellspacing="0" cellpadding="3" width="100%" style="table-layout: fixed; font-size:11pt;">
        <tr style="background-color:#f2f2f2;">
            <th width="10%" align="center"><b>#</b></th>
            <th width="65%" align="center"><b>รายการ</b></th>
            <th width="10%" align="center"><b>จำนวน</b></th>
            <th width="15%" align="center"><b>จำนวนเงิน</b></th>
        </tr>';

    if (empty($items)) {
        $html .= '<tr>
            <td colspan="4" align="center"><i>ไม่พบรายการรับเงิน (ไม่มีเงินทำสัญญา)</i></td>
        </tr>';
    } else {
        foreach ($items as $index => $item) {
            $html .= '<tr>
                <td align="center">' . ($index + 1) . '</td>
                <td>' . htmlspecialchars($item['description']) . '</td>
                <td align="right">' . htmlspecialchars($item['quantity']) . '</td>
                <td align="right">' . number_format($item['amount'], 2) . '</td>
            </tr>';
        }
    }


    $html .= '<tr>
    <td colspan="3" align="right"><b>รวมทั้งสิ้น:</b></td>
    <td align="right"><b>' . number_format($total, 2) . '</b></td>
</tr>';

    $html .= '<tr>
        <td colspan="4" align="right"><i>( ' . htmlspecialchars($thai_text_total) . ' )</i></td>
    </tr>';

    $html .= '</table><br>';

    // ส่วนสรุปรายละเอียดสัญญา
    $html .= '
    <table border="0" cellspacing="0" cellpadding="1" width="100%" style="font-size:11pt;">
        <tr>
            <td colspan="2"><b>สรุปสัญญาผ่อนชำระ:</b></td>
        </tr>
        <tr>
            <td width="50%"><b>ยอดเงินต้นรวม:</b> ' . number_format($main_installment['principal_amount'], 2) . ' บาท</td>
            <td width="50%"><b>ค่าปรับล่าช้ารวม:</b> ' . number_format($main_installment['interest_rate'], 2) . ' บาท</td>
        </tr>
        <tr>
            <td width="50%"><b>เงินทำสัญญา:</b> ' . number_format($main_installment['down_payment'], 2) . ' บาท</td>
            <td width="50%"><b>ยอดเงินที่ต้องผ่อนชำระ (หลังหักเงินทำสัญญา):</b> ' . number_format($main_installment['principal_amount_balance'], 2) . ' บาท</td>
        </tr>
        <tr>
            <td width="50%"><b>จำนวนงวด:</b> ' . htmlspecialchars($main_installment['num_installments']) . ' งวด</td>
            <td width="50%"><b>ยอดผ่อนแต่ละงวด:</b> ' . number_format($main_installment['installment_per_period'], 2) . ' บาท</td>
        </tr>
        <tr>
            <td colspan="2"><b>วันที่ครบกำหนดชำระแต่ละงวด:</b> ' . htmlspecialchars($main_installment['payment_due_day_period'] ?? 'ไม่ระบุ') . '</td>
        </tr>
    </table>
    <br>';


    $html .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" style="margin-top:5px; margin-bottom:5px; font-size:11pt;">
<tr>
    <td align="left"><b>ผู้ชำระเงิน:</b> ' . htmlspecialchars($main_installment['debtor']) . '</td>
    <td align="center">
        <b>ผู้รับเงิน</b><br>
        ' . $signature_img_html . '<br>
        (' . htmlspecialchars($full_name) . ')  &nbsp; &nbsp; &nbsp;ตำแหน่ง: เจ้าหน้าที่นิติฯ
    </td>
</tr>
<tr>
    <td align="left" style="font-size:10pt;">
        วันที่พิมพ์: ' . date('d/m/Y H:i') . '
    </td>
    <td align="right" style="font-size:10pt;">
        ผู้พิมพ์: ' . (isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'เจ้าหน้าที่นิติฯ') . '
    </td>
</tr>
</table>';

    return $html;
}

// รวม HTML สองชุด (ต้นฉบับ + สำเนา) โดยเว้น space ระหว่างต้นฉบับกับสำเนา
$html = generate_receipt_html($company, $main_installment, $items, $total, $thai_text_total, "(ต้นฉบับ)", $pdf);
$html .= '<hr style="border-top: dashed 1px; margin: 10px 0;">';  // ลด margin ของเส้นคั่นอีก
$html .= generate_receipt_html($company, $main_installment, $items, $total, $thai_text_total, "(สำเนา)", $pdf);

// เขียนลง PDF
$pdf->writeHTML($html, true, false, false, false, '');

// เนื่องจาก schema ของ ims_installment ที่ให้มาไม่มีฟิลด์ print_status, print_first_date, print_last_date
// จึงละเว้นการอัปเดตสถานะการพิมพ์ในตารางนี้

// สร้างชื่อไฟล์
$filename = 'receipt_summary_' . $main_installment['installment_id'] . '_' . date('Ymd_His') . '.pdf';
$pdf->Output($filename, 'I');

?>