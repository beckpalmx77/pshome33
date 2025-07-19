<?php
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php');

// ตรวจสอบค่า installment_id และ line_no ที่ส่งมาใน URL
$installment_id = isset($_GET['installment_id']) ? $_GET['installment_id'] : '';
$line_no = isset($_GET['line_no']) ? $_GET['line_no'] : '';

// หากไม่พบข้อมูลที่จำเป็น ให้แสดงข้อความผิดพลาดและหยุดการทำงาน
if (!$installment_id || !$line_no) {
    die("ไม่พบข้อมูล installment_id หรือ line_no ที่จำเป็นสำหรับการพิมพ์ใบเสร็จ");
}

// ดึงข้อมูลบริษัทจากตาราง ims_company
$sql = "SELECT company_name, address_1, address_2, state, zip_code, phone FROM ims_company LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลการผ่อนชำระหลักจาก ims_installment และรายละเอียดการผ่อนชำระจาก ims_installment_detail
// โดยใช้ installment_id และ line_no เป็นเงื่อนไขในการค้นหา
$stmt = $conn->prepare("
    SELECT
        i.house_number,
        i.debtor,
        i.detail,
        i.principal_amount,
        i.down_payment,
        i.num_installments,
        i.installment_per_period,
        i.interest_rate,
        i.start_date,
        id.line_no,
        id.installment_number,
        id.doc_date,
        id.amount_due,
        id.principal_per_installment,
        id.interest_per_installment,
        id.payment_method,
        id.amount_paid,
        id.payment_date,
        id.status AS detail_status,
        id.notes,
        id.print_status,         -- เพิ่ม field print_status
        id.print_first_date,     -- เพิ่ม field print_first_date
        id.print_last_date       -- เพิ่ม field print_last_date
    FROM
        ims_installment i
    JOIN
        ims_installment_detail id ON i.installment_id = id.installment_id
    WHERE
        i.installment_id = :installment_id AND id.line_no = :line_no
");
$stmt->bindParam(':installment_id', $installment_id, PDO::PARAM_STR);
$stmt->bindParam(':line_no', $line_no, PDO::PARAM_INT);
$stmt->execute();
$receipt = $stmt->fetch(PDO::FETCH_ASSOC);

// หากไม่พบข้อมูลใบเสร็จ ให้แสดงข้อความผิดพลาดและหยุดการทำงาน
if (!$receipt) {
    die("ไม่พบข้อมูลใบเสร็จสำหรับการผ่อนชำระนี้ (Installment ID: " . htmlspecialchars($installment_id) . ", Line No: " . htmlspecialchars($line_no) . ")");
}

// สำหรับใบเสร็จงวดผ่อนชำระนี้ จะมีเพียง 1 รายการ
$items = [$receipt];

// คำนวณยอดรวม ซึ่งคือ amount_paid ของงวดผ่อนชำระนี้
$total = $receipt['amount_paid'];
$thai_text_total = converNumberToThaiText($total);

// กำหนดคลาส TCPDF ใหม่เพื่อสร้าง footer (ยังคงใช้ CustomPDF เดิม)
class CustomPDF extends TCPDF
{
    public $printed_by = '';

    public function Footer()
    {
        // ไม่ต้องทำ footer เพราะกำหนดใน HTML แล้ว
    }
}

// สร้าง PDF object
$pdf = new CustomPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->printed_by = isset($_SESSION['user_name']) ? 'ผู้พิมพ์: ' . $_SESSION['user_name'] : 'ผู้พิมพ์: ฝ่ายการเงิน';
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);

// ลด margin เพื่อไม่ให้ล้นหน้า
$pdf->SetMargins(8, 5, 8);

// ลดขนาดฟอนต์
$pdf->SetFont('THSarabunNew', '', 12);

$pdf->AddPage();

// ฟังก์ชันสำหรับสร้าง HTML ของใบเสร็จ
function generate_receipt_html($company, $receipt, $items, $total, $thai_text_total, $title_note = '')
{
    // ตรวจสอบและกำหนดค่าผู้พิมพ์ (ผู้รับเงิน) และลายเซ็น
    $full_name = isset($_SESSION['first_name']) && isset($_SESSION['last_name']) ? $_SESSION['first_name'] . " " . $_SESSION['last_name'] : 'เจ้าหน้าที่';
    $user_signature = isset($_SESSION['user_signature']) ? $_SESSION['user_signature'] : '';
    $signature_path = $user_signature ? 'img_sig/' . $user_signature : '';
    $signature_img = $user_signature && file_exists($signature_path)
        ? '<img src="' . $signature_path . '" height="30">'
        : '____________';

    $html = '
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:20px; margin-top:20px; text-align:center;">
        <tr>
            <td>
                <h2 style="margin-bottom: 5px;">ใบเสร็จรับเงิน ' . $title_note . '</h2>
                <img src="img/logo/ps33-rec-logo.png" height="40" style="display: block; margin: 0 auto;">
            </td>
        </tr>
    </table>

    <table border="0" cellspacing="0" cellpadding="4" width="100%" style="font-size:12pt;">
        <tr>
            <td><b>' . htmlspecialchars($company['company_name']) . '</b></td>
            <td align="right"><b>เลขที่ใบเสร็จ:</b> ' . htmlspecialchars($receipt['installment_id']) . '</td>
        </tr>
        <tr>
            <td><b>ที่อยู่:</b> ' . htmlspecialchars($company['address_1']) . ' ' . htmlspecialchars($company['address_2']) . ' ' . htmlspecialchars($company['state']) . ' ' . htmlspecialchars($company['zip_code']) . '</td>
            <td align="right"><b>วันที่:</b> ' . date('d/m/Y', strtotime($receipt['payment_date'])) . '</td>
        </tr>
    </table>';

    $html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%" style="table-layout: fixed; font-size:12pt;">
        <tr style="background-color:#f2f2f2;">
            <th width="10%" align="center"><b>#</b></th>
            <th width="65%" align="center"><b>รายการ</b></th>
            <th width="10%" align="center"><b>จำนวน</b></th>
            <th width="15%" align="center"><b>จำนวนเงิน</b></th>
        </tr>';

    // วนลูปแสดงรายการ (ในกรณีนี้จะมีเพียง 1 รายการ)
    foreach ($items as $index => $item) {
        $html .= '<tr>
            <td align="center">' . htmlspecialchars($item['line_no']) . '</td>
            <td><b>ค่าผ่อนชำระบ้านเลขที่ ' . htmlspecialchars($receipt['house_number']) . ' งวดที่ ' . htmlspecialchars($receipt['installment_number']) . '</b><br>
                (' . htmlspecialchars($receipt['notes']) . ')
            </td>
            <td align="right">1</td>
            <td align="right">' . number_format($item['amount_paid'], 2) . '</td>
        </tr>';
    }

    $html .= '<tr>
    <td colspan="2" align="left"><b>วิธีการชำระเงิน : ' . htmlspecialchars($receipt['payment_method']) . '</b></td>
    <td align="right"><b>รวมทั้งสิ้น:</b></td>
    <td align="right"><b>' . number_format($total, 2) . '</b></td>
</tr>';

    $html .= '<tr>
        <td colspan="4" align="right"><i>( ' . htmlspecialchars($thai_text_total) . ' )</i></td>
    </tr>';

    $html .= '</table><br><br>';

    $html .= '<table border="0" cellspacing="0" cellpadding="5" width="100%" style="margin-top:20px; margin-bottom:20px; font-size:12pt;">
<tr>
    <td align="left"><b>ผู้ชำระเงิน</b> ___________ (' . htmlspecialchars($receipt['debtor']) . ')</td>
    <td align="center">
        <b>ผู้รับเงิน</b><br>
        ' . $signature_img . '<br>
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
$html = generate_receipt_html($company, $receipt, $items, $total, $thai_text_total, "(ต้นฉบับ)");
$html .= '<hr style="border-top: dashed 1px; margin: 30px 0;">';  // space เพิ่มขึ้นระหว่างต้นฉบับกับสำเนา
$html .= generate_receipt_html($company, $receipt, $items, $total, $thai_text_total, "(สำเนา)");

// เขียน HTML ลง PDF
$pdf->writeHTML($html, true, false, false, false, '');

// อัปเดตสถานะการพิมพ์ในตาราง ims_installment_detail
$current_print_status = $receipt['print_status']; // ดึงสถานะการพิมพ์ปัจจุบันจากข้อมูลที่ดึงมา

if ($current_print_status == 'N') {
    // ถ้ายังไม่เคยพิมพ์ ให้ตั้งค่าเป็น 'Y' และบันทึกเวลาที่พิมพ์ครั้งแรก
    $stmt_update = $conn->prepare("UPDATE ims_installment_detail
                                  SET print_status = 'Y', print_first_date = NOW()
                                  WHERE installment_id = :installment_id AND line_no = :line_no AND print_status = 'N'");
} else if ($current_print_status == 'Y') {
    // ถ้าเคยพิมพ์แล้ว ให้บันทึกเวลาที่พิมพ์ล่าสุด
    $stmt_update = $conn->prepare("UPDATE ims_installment_detail
                                  SET print_last_date = NOW()
                                  WHERE installment_id = :installment_id AND line_no = :line_no AND print_status = 'Y'");
}

// ตรวจสอบว่ามีการเตรียมคำสั่ง UPDATE หรือไม่ ก่อนที่จะ execute
if (isset($stmt_update)) {
    $stmt_update->bindParam(':installment_id', $installment_id, PDO::PARAM_STR);
    $stmt_update->bindParam(':line_no', $line_no, PDO::PARAM_INT);
    $stmt_update->execute();
}

// สร้างชื่อไฟล์ PDF
$filename = 'receipt_installment_' . $installment_id . '_line_' . $line_no . '_' . date('Ymd_His') . '.pdf';
$pdf->Output($filename, 'I'); // 'I' คือการแสดงผลในเบราว์เซอร์

?>