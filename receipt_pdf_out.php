<?php
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php');

// รับค่าพารามิเตอร์จาก GET
$start_date = $_GET["doc_date_start"] ?? '';
$end_date = $_GET["doc_date_to"] ?? '';
$pm = $_GET["payment_method"] ?? 'all';

// เงื่อนไข payment_method
$payment_method_sql = "";
$payment_method = "เงินสด - เงินโอน";

if ($pm === "cash") {
    $payment_method_sql = " AND payment_method = 'เงินสด' ";
    $payment_method = 'เงินสด';
} elseif ($pm === "bank") {
    $payment_method_sql = " AND payment_method = 'เงินโอน' ";
    $payment_method = 'เงินโอน';
}

// สร้าง TCPDF object
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('My System');
$pdf->SetAuthor('My System');
$pdf->SetTitle('รายงานรายการชำระเงิน');
$pdf->SetHeaderData('', 0, 'รายงานรายการชำระเงิน', '');
$pdf->setHeaderFont(Array('freeserif', '', 14));
$pdf->setFooterFont(Array('freeserif', '', 12));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(5, 10, 5);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('freeserif', '', 12);
$pdf->AddPage();

// SQL Query
$sql = "
    SELECT * FROM v_ims_house_payment 
    WHERE STR_TO_DATE(payment_date, '%d-%m-%Y') 
          BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') 
          AND STR_TO_DATE(:end_date, '%d-%m-%Y')
          $payment_method_sql
    ORDER BY STR_TO_DATE(payment_date, '%d-%m-%Y');
";

// Prepare & bind
$stmt = $conn->prepare($sql);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();

// HTML Table
$html = '<h2>รายการรับเงินค่าส่วนกลาง (' . $payment_method . ')<br>ช่วงวันที่ ' . $start_date . ' ถึง ' . $end_date . '</h2>';
$html .= '<table border="1" cellpadding="4" cellspacing="0">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th width="6%">ลำดับ</th>
            <th width="14%">วันที่ชำระ</th>
            <th width="14%">บ้านเลขที่</th>
            <th width="14%">เดือนเริ่ม</th>
            <th width="14%">เดือนสิ้นสุด</th>
            <th width="7%">ปี</th>
            <th width="14%">จำนวนเงิน</th>
            <th width="18%">สถานะ</th>
        </tr>
    </thead>
    <tbody>';

$i = 1;
$total_amount = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $amount = (float)$row['amount'];
    $total_amount += $amount;

    $html .= '<tr>
        <td width="6%">' . $i++ . '</td>
        <td width="14%">' . date('d/m/Y', strtotime($row['payment_date'])) . '</td>
        <td width="14%">' . htmlspecialchars($row['house_number']) . '</td>
        <td width="14%">' . htmlspecialchars($row['month_name_start']) . '</td>
        <td width="14%">' . htmlspecialchars($row['month_name_to']) . '</td>
        <td width="7%" align="center">' . htmlspecialchars($row['period_year']) . '</td>
        <td width="14%" align="right">' . number_format($amount, 2) . '</td>
        <td width="18%">' . htmlspecialchars($row['payment_status_desc']) . '</td>
    </tr>';
}

$html .= '</tbody>
    <tfoot>
        <tr style="background-color:#d9edf7; font-weight:bold;">
            <td width="6%"></td>
            <td width="14%"></td>
            <td width="14%"></td>
            <td width="14%"></td>
            <td width="14%"></td>
            <td width="7%" align="center">รวม</td>
            <td width="14%" align="right">' . number_format($total_amount, 2) . '</td>
            <td width="18%"></td>
        </tr>
    </tfoot>
</table>';


// สร้าง PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('payment_report_print.pdf', 'I');
?>
