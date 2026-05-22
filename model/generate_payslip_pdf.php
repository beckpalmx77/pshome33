<?php
// Include Dompdf autoloader (adjust path if not using Composer)
require_once '../vendor/autoload.php'; // Adjust path if dompdf is not in vendor/

use Dompdf\Dompdf;
use Dompdf\Options;

include('../config/connect_db.php'); // Your database connection file

$doc_no = $_GET['doc_no'] ?? '';

if (empty($doc_no)) {
    die('Error: Document number is missing.');
}

$master_data = null;
$detail_data = [];

try {
    // Fetch master payroll data
    $stmt_master = $conn->prepare("SELECT * FROM ims_payroll WHERE doc_no = ?");
    $stmt_master->execute([$doc_no]);
    $master_data = $stmt_master->fetch(PDO::FETCH_ASSOC);

    if (!$master_data) {
        die('Error: Payslip record not found for the given document number.');
    }

    // Fetch detail payroll data
    $stmt_detail = $conn->prepare("SELECT * FROM ims_payroll_detail WHERE doc_no = ? ORDER BY icd_type_sign DESC, icd_type_id ASC");
    $stmt_detail->execute([$doc_no]);
    $detail_data = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error generating PDF: " . $e->getMessage());
    die('Database error: ' . $e->getMessage());
}

// --- Build HTML for PDF ---
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>สลิปเงินเดือน - ' . htmlspecialchars($master_data['doc_no'] ?? '') . '</title>
    <style>
        body {
            font-family: "thsarabunnew", "Garuda", "sans-serif"; /* Use a font that supports Thai characters */
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header, .summary {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0;
        }
        .section-title {
            font-weight: bold;
            background-color: #f2f2f2;
            padding: 5px;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table td, table th {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .total-row td {
            font-weight: bold;
            background-color: #e6e6e6;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #555;
        }
    </style>
    <style>
        @font-face {
            font-family: "thsarabunnew";
            font-style: normal;
            font-weight: normal;
            src: url("../fonts/THSarabunNew.ttf") format("truetype"); /* Adjust path to your font file */
        }
        @font-face {
            font-family: "thsarabunnew";
            font-style: bold;
            font-weight: bold;
            src: url("../fonts/THSarabunNew-Bold.ttf") format("truetype"); /* Adjust path to your font file */
        }
        body { font-family: "thsarabunnew", sans-serif; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>สลิปเงินเดือน</h2>
            <p>[ชื่อบริษัทของคุณ]</p>
            <p>[ที่อยู่บริษัทของคุณ]</p>
            <p>ประจำเดือน: ' . htmlspecialchars(getThaiMonthName($master_data['payroll_month'])) . ' ' . htmlspecialchars($master_data['payroll_year'] ?? '') . '</p>
        </div>

        <table>
            <tr>
                <td style="width: 50%;"><strong>เลขที่เอกสาร:</strong> ' . htmlspecialchars($master_data['doc_no'] ?? '') . '</td>
                <td style="width: 50%;"><strong>วันที่เอกสาร:</strong> ' . htmlspecialchars($master_data['doc_date'] ?? '') . '</td>
            </tr>
            <tr>
                <td><strong>รหัสพนักงาน:</strong> ' . htmlspecialchars($master_data['emp_id'] ?? '') . '</td>
                <td><strong>ชื่อพนักงาน:</strong> [ดึงจาก DB หรือส่งมา]</td>
            </tr>
            <tr>
                 <td><strong>จำนวนวันทำงานในเดือน:</strong> ' . htmlspecialchars(number_format($master_data['work_day_month'], 2)) . ' วัน</td>
                 <td></td>
            </tr>
        </table>

        <div class="section-title">รายการรายได้</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 70%;">รายการ</th>
                    <th style="width: 30%;" class="text-right">จำนวนเงิน (บาท)</th>
                </tr>
            </thead>
            <tbody>';
$total_income = 0;
foreach ($detail_data as $item) {
    if ($item['icd_type_sign'] === '+') {
        $html .= '
                    <tr>
                        <td>' . htmlspecialchars($item['icd_type_desc'] ?? '') . '</td>
                        <td class="text-right">' . htmlspecialchars(number_format($item['amount'], 2)) . '</td>
                    </tr>';
        $total_income += $item['amount'];
    }
}
$html .= '
            <tr class="total-row">
                <td><strong>รวมรายได้</strong></td>
                <td class="text-right"><strong>' . htmlspecialchars(number_format($total_income, 2)) . '</strong></td>
            </tr>
            </tbody>
        </table>

        <div class="section-title">รายการหัก</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 70%;">รายการ</th>
                    <th style="width: 30%;" class="text-right">จำนวนเงิน (บาท)</th>
                </tr>
            </thead>
            <tbody>';
$total_deduction = 0;
foreach ($detail_data as $item) {
    if ($item['icd_type_sign'] === '-') {
        $html .= '
                    <tr>
                        <td>' . htmlspecialchars($item['icd_type_desc'] ?? '') . '</td>
                        <td class="text-right">' . htmlspecialchars(number_format($item['amount'], 2)) . '</td>
                    </tr>';
        $total_deduction += $item['amount'];
    }
}
$html .= '
            <tr class="total-row">
                <td><strong>รวมรายหัก</strong></td>
                <td class="text-right"><strong>' . htmlspecialchars(number_format($total_deduction, 2)) . '</strong></td>
            </tr>
            </tbody>
        </table>

        <table style="margin-top: 20px;">
            <tr class="total-row">
                <td style="width: 70%;"><strong>ยอดรวมสุทธิ (รายได้ - รายหัก)</strong></td>
                <td style="width: 30%;" class="text-right"><strong>' . htmlspecialchars(number_format($master_data['total_amount'], 2)) . '</strong></td>
            </tr>
        </table>

        <div class="footer">
            <p>เอกสารนี้จัดทำขึ้นด้วยระบบ ไม่จำเป็นต้องมีลายเซ็นต์</p>
            <p>วันที่พิมพ์: ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </div>
</body>
</html>';

// Function to get Thai month name
function getThaiMonthName($monthNum) {
    $thaiMonths = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    return $thaiMonths[$monthNum] ?? '';
}

// --- Dompdf Configuration and Generation ---
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
// You might need to set font caching or enable debug
// $options->set('logOutputFile', "/path/to/dompdf_log.htm"); // Path to a writable log file
// $options->set('debugKeepTemp', true);
// $options->set('debugCss', true);
// $options->set('debugLayout', true);
// $options->set('debugPng', true);

$dompdf = new Dompdf($options);

// Load HTML content
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF (inline or attachment)
$dompdf->stream("Payslip_" . $doc_no . ".pdf", array("Attachment" => false)); // Set to true for download
?>