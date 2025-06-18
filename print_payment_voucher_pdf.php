<?php
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php'); // Make sure this utility exists and works correctly

// ตรวจสอบค่า ID ที่ส่งมา
$id = isset($_GET['id']) ? $_GET['id'] : '';
if (!$id) {
    die("ไม่พบข้อมูล: ไม่ได้รับ ID");
}

// ดึง doc_no จาก ims_payment_voucher โดยใช้ ID
$stmt_get_doc_no = $conn->prepare("SELECT doc_no FROM ims_payment_voucher WHERE id = :id");
$stmt_get_doc_no->bindParam(':id', $id, PDO::PARAM_INT);
$stmt_get_doc_no->execute();
$result_doc_no = $stmt_get_doc_no->fetch(PDO::FETCH_ASSOC);

if (!$result_doc_no || empty($result_doc_no['doc_no'])) {
    die("ไม่พบเลขที่เอกสาร (doc_no) สำหรับ ID ที่ระบุ: " . htmlspecialchars($id));
}

$doc_no = $result_doc_no['doc_no'];

// ดึงข้อมูลบริษัท
$sql = "SELECT company_name, address_1, address_2, state, zip_code, phone FROM ims_company LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลใบเสร็จ (Header from ims_payment_voucher โดยใช้ doc_no)
$stmt = $conn->prepare("SELECT * FROM ims_payment_voucher WHERE doc_no = :doc_no");
$stmt->bindParam(':doc_no', $doc_no, PDO::PARAM_STR); // Bind as string
$stmt->execute();
$voucher_header = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$voucher_header) {
    die("ไม่พบข้อมูลใบเสร็จสำหรับเอกสารเลขที่ " . htmlspecialchars($doc_no));
}

// ดึงรายการ (Details from ims_payment_voucher_items โดยใช้ doc_no)
$stmt_items = $conn->prepare("SELECT * FROM ims_payment_voucher_items WHERE doc_no = :doc_no ORDER BY line_no ASC");
$stmt_items->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
$stmt_items->execute();
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// รวมยอด
$total = 0;
foreach ($items as $item) {
    // Ensure quantity and price are numeric before calculation
    $quantity = is_numeric($item['quantity']) ? (float)$item['quantity'] : 0;
    $price = is_numeric($item['price']) ? (float)$item['price'] : 0;
    $total += ($quantity * $price);
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

// ลด margin เพื่อไม่ให้ล้นหน้า - Margins are already quite small.
$pdf->SetMargins(8, 5, 8);

// ลดขนาดฟอนต์ (already at 12pt, which is reasonable)
$pdf->SetFont('THSarabunNew', '', 12);

$pdf->AddPage();

// ฟังก์ชันสร้าง HTML สำหรับใบเสร็จ
function generate_receipt_html($company, $voucher_header, $items, $total, $thai_text_total, $title_note = '')
{
    $full_name = $_SESSION['first_name'] . " " . $_SESSION['last_name'];
    // Removed signature image logic
    $signature_placeholder = '_________________________'; // Placeholder for signature line

    $html = '
    <table border="0" cellspacing="0" cellpadding="0" width="100%" style="font-size:12pt;">
        <tr>
            <td width="30%" align="left" valign="top">
                <img src="img/logo/ps33-rec-logo.png" height="30">
            </td>
            <td width="70%" align="left" valign="top">
                <b>' . htmlspecialchars($company['company_name']) . '</b><br>
                ' . htmlspecialchars($company['address_1'] . ' ' . $company['address_2'] . ' ' . $company['state'] . ' ' . $company['zip_code']) . '
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:10px; margin-top:10px; text-align:center;">
        <tr>
            <td>
                <h2 style="margin-bottom: 5px;">ใบสำคัญจ่าย ' . $title_note . '</h2>
            </td>
        </tr>
    </table>

    <table border="0" cellspacing="0" cellpadding="4" width="100%" style="font-size:12pt;">
        <tr>
            <td align="left" width="50%"><b>เลขที่เอกสาร:</b> ' . htmlspecialchars($voucher_header['doc_no']) . '</td>
            <td align="right" width="50%"><b>วันที่:</b> ' . date('d/m/Y', strtotime($voucher_header['doc_date'])) . '</td>
        </tr>
        <tr>
            <td width="50%"><b>จ่ายให้แก่:</b> ' . htmlspecialchars($voucher_header['supplier_name'] ?? ' - ') . '</td>
            <td width="50%"><b>จ่ายเพื่อ:</b> ' . htmlspecialchars($voucher_header['purpose']) . '</td>
        </tr>
        <tr>
            <td width="50%"><b>วิธีการชำระเงิน:</b> ' . htmlspecialchars($voucher_header['payment_method']) . '</td>
            <td width="50%"><b>หมายเลขบัญชีธนาคาร:</b> ' . htmlspecialchars($voucher_header['bank_no'] ?? ' - ') . '</td>
        </tr>
    </table>';

    $html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%" style="table-layout: fixed; font-size:12pt;">
        <tr style="background-color:#f2f2f2;">
            <th width="10%" align="center"><b>ลำดับ</b></th>
            <th width="50%" align="center"><b>รายการ</b></th>
            <th width="15%" align="center"><b>จำนวน</b></th>
            <th width="10%" align="center"><b>หน่วย</b></th>
            <th width="15%" align="center"><b>จำนวนเงิน</b></th>
        </tr>';

    if (empty($items)) {
        $html .= '<tr><td colspan="5" align="center">ไม่มีรายการ</td></tr>';
    } else {
        foreach ($items as $index => $item) {
            $quantity = is_numeric($item['quantity']) ? (float)$item['quantity'] : 0;
            $price = is_numeric($item['price']) ? (float)$item['price'] : 0;
            $item_amount = $quantity * $price;

            $html .= '<tr>
                <td align="center">' . ($index + 1) . '</td>
                <td>' . htmlspecialchars($item['product_name']) . '</td>
                <td align="right">' . number_format($quantity, 2) . '</td>
                <td align="center">' . htmlspecialchars($item['unit_name']) . '</td>
                <td align="right">' . number_format($item_amount, 2) . '</td>
            </tr>';
        }
    }

    $html .= '<tr>
        <td colspan="3" align="left"><b>วิธีการชำระเงิน : ' . htmlspecialchars($voucher_header['payment_method']) . '</b></td>
        <td colspan="1" align="right"><b>รวมทั้งสิ้น:</b></td>
        <td align="right"><b>' . number_format($total, 2) . '</b></td>
    </tr>';

    $html .= '<tr>
        <td colspan="5" align="right"><i>( ' . $thai_text_total . ' )</i></td>
    </tr>';

    $html .= '</table><br><br>';

    // Footer layout with all four roles on one line
    $html .= '<table border="0" cellspacing="0" cellpadding="5" width="100%" style="margin-top:20px; margin-bottom:20px; font-size:12pt;">
        <tr>
            <td width="25%" align="center">
                <b>ผู้จัดทำ</b><br>
                ' . $signature_placeholder . '<br>
                (' . htmlspecialchars($voucher_header['create_name']) . ')
            </td>
            <td width="25%" align="center">
                <b>ผู้ตรวจสอบ</b><br>
                ' . $signature_placeholder . '<br>
                (' . htmlspecialchars($voucher_header['checker_name']) . ')
            </td>
            <td width="25%" align="center">
                <b>ผู้อนุมัติ</b><br>
                ' . $signature_placeholder . '<br>
                (' . htmlspecialchars($voucher_header['approve_name']) . ')
            </td>
            <td width="25%" align="center">
                <b>ผู้รับเงิน</b><br>
                ' . $signature_placeholder . '<br>
                (' . htmlspecialchars($voucher_header['receipt_name']) . ')
            </td>
        </tr>
    </table>';

    // Keep the print date and printed by info as a separate line
    $html .= '<table border="0" cellspacing="0" cellpadding="5" width="100%" style="font-size:10pt;">
        <tr>
            <td align="left">
                วันที่พิมพ์: ' . date('d/m/Y H:i') . '
            </td>
            <td align="right">
                ผู้พิมพ์: ' . (isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'เจ้าหน้าที่นิติฯ') . '
            </td>
        </tr>
    </table>';

    return $html;
}

// รวม HTML สองชุด (ต้นฉบับ + สำเนา) โดยเว้น space ระหว่างต้นฉบับกับสำเนา
$html = generate_receipt_html($company, $voucher_header, $items, $total, $thai_text_total, "(ต้นฉบับ)");
// Reverted HR margin to small and added explicit div with height for space
$html .= '<hr style="border-top: dashed 1px; margin: 5px 0;">';
$html .= '<div style="height: 20px;"></div>'; // เพิ่ม div เปล่าที่มีความสูง 20px
$html .= generate_receipt_html($company, $voucher_header, $items, $total, $thai_text_total, "(สำเนา)");

// เขียนลง PDF
$pdf->writeHTML($html, true, false, false, false, '');

// สร้างชื่อไฟล์
$filename = 'payment_voucher_' . $voucher_header['doc_no'] . '_' . date('Ymd_His') . '.pdf';
$pdf->Output($filename, 'I');
?>