<?php

require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php');

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

// TCPDF setup
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(10, 10, 15); // เพิ่มขอบขวาให้มากขึ้น
$pdf->SetFont('THSarabunNew', '', 14);
$pdf->AddPage();

// ฟังก์ชันสร้างเนื้อหา HTML
function generate_receipt_html($company, $receipt, $items, $total, $thai_text_total, $title_note = '')
{
    $html = '
    <h2 style="text-align:center;">ใบเสร็จรับเงิน ' . $title_note . '</h2>
    <div style="text-align:center;">
        <img src="img/logo/ps33-rec-logo.png" height="50">
    </div>
    <table border="0" cellspacing="0" cellpadding="4">
        <tr>        
            <td><b>' . $company['company_name'] . '</b></td>
            <td align="right"><b>เลขที่ใบเสร็จ:</b> ' . $receipt['doc_id'] . '</td>
        </tr>
        <tr>
            <td><b>ที่อยู่:</b> ' . $company['address_1'] . ' ' . $company['address_2'] . ' ' . $company['state'] . ' ' . $company['zip_code'] . '<br><b>โทร:</b> ' . $company['phone'] . '</td>
            <td align="right"><b>วันที่:</b> ' . date('d/m/Y', strtotime($receipt['payment_date'])) . '</td>
        </tr>
    </table>';

    $html .= '<table border="1" cellspacing="0" cellpadding="5">
        <tr style="background-color:#f2f2f2;">
            <th width="10%" align="center"><b>#</b></th>
            <th width="70%" align="center"><b>รายการ</b></th>
            <th width="10%" align="center"><b>จำนวน</b></th>
            <th width="15%" align="center"><b>จำนวนเงิน</b></th>
        </tr>';

    foreach ($items as $index => $item) {
        $period_month = $receipt['month_name_start'] == $receipt['month_name_to']
            ? $receipt['month_name_start']
            : $receipt['month_name_start'] . " - " . $receipt['month_name_to'];

        $html .= '<tr>
            <td align="center">' . ($index + 1) . '</td>
            <td><b>ค่าส่วนกลาง งวดเดือน </b> ' . $period_month . ' ' . $receipt['period_year'] . '</td>
            <td align="right">1</td>
            <td align="right">' . number_format($item['amount'], 2) . '</td>
        </tr>';
    }

    $html .= '<tr>
        <td colspan="3" align="right"><b>รวมทั้งสิ้น:</b></td>
        <td align="right"><b>' . number_format($total, 2) . '</b></td>
    </tr>';

    $html .= '<tr>
        <td colspan="4" align="right"><i>( ' . $thai_text_total . ' )</i></td>
    </tr>';

    $html .= '</table><br><br>';

    $html .= '<table border="0" cellspacing="0" cellpadding="5">
    <tr>
        <td align="left"><b>ผู้ชำระเงิน</b> ___________ (' . $receipt['detail'] . ')</td>
        <td align="right"><b>ผู้รับเงิน</b> ____________</td>
    </tr>
    <tr>
        <td></td>
        <!--td align="right">(<b>                     </b>)<br>ตำแหน่ง: ผู้จัดการ / ฝ่ายการเงิน</td-->
        <td align="right">ตำแหน่ง: ผู้จัดการ / ฝ่ายการเงิน</td>
    </tr>
</table><br>';

    return $html;
}

// HTML ทั้งสองส่วนในหน้าเดียว
$html = generate_receipt_html($company, $receipt, $items, $total, $thai_text_total, "(ต้นฉบับ)");
$html .= '<hr style="border-top: dashed 1px; margin: 15px 0;">';
$html .= generate_receipt_html($company, $receipt, $items, $total, $thai_text_total, "(สำเนา)");

// เขียนลง PDF
$pdf->writeHTML($html, true, false, false, false, '');

// แสดงผล
$pdf->Output('receipt-double.pdf', 'I');
