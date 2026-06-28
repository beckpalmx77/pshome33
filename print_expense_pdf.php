<?php
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php');

// ตรวจสอบค่า ID
$id = isset($_GET['id']) ? $_GET['id'] : '';
if (!$id) die("ไม่พบข้อมูล");

// ดึงข้อมูลบริษัท
$sql = "SELECT company_name, address_1, address_2, state, zip_code, phone FROM ims_company LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลใบสำคัญจ่าย
$stmt = $conn->prepare("SELECT * FROM ims_expenses WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$receipt = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงรายการ
$stmt_items = $conn->prepare("SELECT * FROM ims_expenses WHERE id = :id");
$stmt_items->bindParam(':id', $id, PDO::PARAM_INT);
$stmt_items->execute();
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// รวมยอด
$total = 0;
foreach ($items as $item) {
    $total += $item['amount'];
}
$thai_text_total = converNumberToThaiText($total);

// กำหนดคลาส TCPDF ใหม่
class CustomPDF extends TCPDF {
    public $printed_by = '';
    public function Footer() { /* Footer ถูกสร้างใน HTML */ }
}

// สร้าง PDF
$pdf = new CustomPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->printed_by = isset($_SESSION['user_name']) ? 'ผู้พิมพ์: ' . $_SESSION['user_name'] : 'ผู้พิมพ์: ฝ่ายการเงิน';
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetMargins(8, 5, 8);
$pdf->SetFont('THSarabunNew', '', 14);
$pdf->AddPage();

function generate_voucher_html($company, $receipt, $items, $total, $thai_text_total, $title_note = '') {
    $full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    $user_signature = $_SESSION['user_signature'] ?? '';
    $sign_path = $user_signature ? 'img_sig/'.$user_signature : '';
    $sign_img = ($user_signature && file_exists($sign_path))
        ? '<img src="'.$sign_path.'" height="30">'
        : '____________';

    $html = '
    <table width="100%" cellspacing="0" cellpadding="0" style="margin:20px 0; text-align:center;">
      <tr><td>
        <h2>ใบสำคัญจ่าย ' . $title_note . '</h2>
        <img src="img/logo/niti_ps33_header.png" height="40">
      </td></tr>
    </table>
    <table width="100%" cellpadding="4" style="font-size:14pt">
      <tr>
        <td><b>'.$company['company_name'].'</b><br><b>ที่อยู่:</b> '.
        $company['address_1'].' '.$company['address_2'].' '.$company['state'].' '.$company['zip_code'].'</td>
        <td align="right"><b>เลขที่ใบสำคัญจ่าย:</b> '.$receipt['doc_id'].'<br><b>วันที่:</b> '.date('d/m/Y', strtotime($receipt['expense_date'])).'</td>
      </tr>
    </table>
    <table border="1" width="100%" cellpadding="5" style="table-layout:fixed; font-size:14pt">
      <tr style="background:#f2f2f2">
        <th width="10%" align="center">#</th>
        <th width="50%" align="center">รายการ</th>
        <th width="15%" align="center">จำนวน</th>
        <th width="15%" align="center">หน่วย</th>
        <th width="10%" align="center">จำนวนเงิน</th>
      </tr>';
    foreach ($items as $i => $it) {
        $qty = number_format($it['qty'], 2);
        $unit = $it['unit_id'] ?? '';
        $amt = number_format($it['amount'], 2);
        $html .= '<tr>
          <td align="center">'.($i+1).'</td>
          <td>'.$it['description'].'</td>
          <td align="right">'.$qty.'</td>
          <td align="center">'.$unit.'</td>
          <td align="right">'.$amt.'</td>
        </tr>';
    }
    $html .= '<tr>
      <td colspan="4" align="right"><b>รวมทั้งสิ้น:</b></td>
      <td align="right"><b>'.number_format($total,2).'</b></td>
    </tr>
    <tr>
      <td colspan="5" align="right"><i>('.$thai_text_total.')</i></td>
    </tr>
    </table>
    <br><br>
    <table width="100%" cellpadding="5" style="font-size:14pt">
      <tr>
        <td><b>ผู้รับเงิน</b> ___________ ('.$receipt['receipt_name'].')</td>
        <td align="center"><b>ผู้จ่าย</b><br>'.$sign_img.'<br>('.$full_name.') ตำแหน่ง: ผู้จัดการ/ฝ่ายการเงิน</td>
      </tr>
      <tr>
        <td style="font-size:12pt">วันที่พิมพ์: '.date('d/m/Y H:i').'</td>
        <td align="right" style="font-size:12pt">ผู้พิมพ์: '.
        (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'ฝ่ายการเงิน').'</td>
      </tr>
    </table>';

    return $html;
}

// รวม HTML 2 ชุด
$html = generate_voucher_html($company, $receipt, $items, $total, $thai_text_total, '(ต้นฉบับ)');
$html .= '<hr style="border-top: dashed 1px; margin:30px 0;">';
$html .= generate_voucher_html($company, $receipt, $items, $total, $thai_text_total, '(สำเนา)');

// เขียนลง PDF
$pdf->writeHTML($html, true, false, false, false, '');

// ตั้งชื่อไฟล์
$filename = 'voucher_'.$receipt['doc_id'].'_'.date('Ymd_His').'.pdf';
$pdf->Output($filename, 'I');
