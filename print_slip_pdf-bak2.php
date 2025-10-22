<?php
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php');

$doc_no = $_GET['doc_no'] ?? '';
if (!$doc_no) die("\u0e44\u0e21\u0e48\u0e1e\u0e1a\u0e40\u0e25\u0e02\u0e17\u0e35\u0e48\u0e40\u0e2d\u0e01\u0e2a\u0e32\u0e23\u0e40\u0e07\u0e34\u0e19\u0e40\u0e14\u0e37\u0e2d\u0e19");

$stmt = $conn->prepare("SELECT ip.*, CONCAT(e.f_name, ' ', e.l_name) AS emp_fullname, e.salary_type, e.salary
                        FROM ims_payroll ip
                        LEFT JOIN memployee e ON ip.emp_id = e.emp_id
                        WHERE ip.doc_no = ?");
$stmt->execute([$doc_no]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("\u0e44\u0e21\u0e48\u0e1e\u0e1a\u0e02\u0e49\u0e2d\u0e21\u0e39\u0e25\u0e40\u0e07\u0e34\u0e19\u0e40\u0e14\u0e37\u0e2d\u0e19");

$stmt2 = $conn->prepare("SELECT d.*, t.icd_type_desc, t.icd_type_sign
                          FROM ims_payroll_detail d
                          LEFT JOIN m_income_deduct_type t ON d.icd_type_id = t.icd_type_id
                          WHERE d.doc_no = ? ORDER BY d.id ASC");
$stmt2->execute([$doc_no]);
$details = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$thai_text_net = converNumberToThaiText($data['total_amount']);

class CustomPDF extends TCPDF {
    public function Footer() {}
}

$pdf = new CustomPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(false);
$pdf->SetFont('THSarabunNew', '', 16);
$pdf->AddPage();

function buildTableHTML($title, $data, $details, $thai_text_net) {
    $salary_type = $data['salary_type'] === 'M' ? 'รายเดือน' : 'รายวัน';
    $income = array_filter($details, fn($d) => $d['icd_type_sign'] === '+');
    $deduct = array_filter($details, fn($d) => $d['icd_type_sign'] === '-');

    $income_rows = array_pad($income, 3, null);
    $deduct_rows = array_pad($deduct, 3, null);

    ob_start();
    ?>
    <h3 style="text-align:center; margin-bottom:4px;">ใบแจ้งเงินเดือน <?= $title ?></h3>
    <table width="100%" style="font-size:10pt;">
        <tr>
            <td><b>ชื่อพนักงาน:</b> <?= $data['emp_fullname'] ?></td>
            <td align="right"><b>งวด:</b> <?= getThaiMonthName($data['payroll_month']) . ' ' . $data['payroll_year'] ?></td>
        </tr>
        <tr>
            <td><b>เลขที่เอกสาร:</b> <?= $data['doc_no'] ?></td>
            <td align="right"><b>วันที่:</b> <?= date('d/m/Y', strtotime($data['doc_date'])) ?></td>
        </tr>
        <tr>
            <td><b>ประเภท:</b> <?= $salary_type ?></td>
            <td align="right"><b>เงินเดือน:</b> <?= number_format($data['salary'], 2) ?> บาท</td>
        </tr>
    </table>
    <br>
    <table border="1" cellpadding="4" cellspacing="0" width="100%" style="font-size:9pt;">
        <tr>
            <td width="49%">
                <table width="100%" border="0">
                    <tr bgcolor="#f0f0f0">
                        <th>รายการรายได้</th><th>จำนวน</th><th>จำนวนเงิน</th>
                    </tr>
                    <?php foreach ($income_rows as $item): ?>
                        <tr>
                            <td><?= $item['icd_type_desc'] ?? '' ?></td>
                            <td align="right"><?= isset($item) ? number_format($item['quantity'], 2) : '' ?></td>
                            <td align="right"><?= isset($item) ? number_format($item['total_amount'], 2) : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </td>
            <td width="2%">&nbsp;</td>
            <td width="49%">
                <table width="100%" border="0">
                    <tr bgcolor="#f0f0f0">
                        <th>รายการหัก</th><th>จำนวน</th><th>จำนวนเงิน</th>
                    </tr>
                    <?php foreach ($deduct_rows as $item): ?>
                        <tr>
                            <td><?= $item['icd_type_desc'] ?? '' ?></td>
                            <td align="right"><?= isset($item) ? number_format($item['quantity'], 2) : '' ?></td>
                            <td align="right"><?= isset($item) ? number_format($item['total_amount'], 2) : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </td>
        </tr>
    </table>
    <br>
    <table border="1" cellpadding="4" cellspacing="0" width="100%">
        <tr bgcolor="#e6e6e6">
            <td width="75%" align="right"><b>เงินได้สุทธิ:</b></td>
            <td width="25%" align="right"><b><?= number_format($data['total_amount'], 2) ?></b></td>
        </tr>
        <tr>
            <td colspan="2" align="right"><i>( <?= $thai_text_net ?> )</i></td>
        </tr>
    </table>
    <br><br>
    <table width="100%">
        <tr>
            <td align="center">ลงชื่อ.......................................... ผู้รับเงิน</td>
            <td align="center">วันที่..........................................</td>
        </tr>
    </table>
    <?php
    return ob_get_clean();
}

$pdf->writeHTML(buildTableHTML("(ต้นฉบับ)", $data, $details, $thai_text_net));
$pdf->Line(10, 148.5, 200, 148.5, array('dash' => '2,2'));
$pdf->SetY(153.5);
$pdf->writeHTML(buildTableHTML("(สำเนา)", $data, $details, $thai_text_net));

$pdf->Output('payslip_' . $data['doc_no'] . '.pdf', 'I');

function getThaiMonthName($monthNum) {
    $months = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
        4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
        7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
        10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    return $months[$monthNum] ?? '';
}
?>