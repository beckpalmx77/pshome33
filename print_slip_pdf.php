<?php
error_reporting(E_ALL & ~E_DEPRECATED); // Hides deprecated warnings. For production, consider 'error_reporting(0);'

session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php'); // Assuming this function is available for number to text conversion

// Check for doc_no from GET request
$doc_no = isset($_GET['doc_no']) ? $_GET['doc_no'] : '';
if (!$doc_no) {
    die("ไม่พบเลขที่เอกสารเงินเดือน");
}

// Fetch company information (if needed for header/footer)
$sql_company = "SELECT company_name, address_1, address_2, state, zip_code, phone FROM ims_company LIMIT 1";
$stmt_company = $conn->prepare($sql_company);
$stmt_company->execute();
$company = $stmt_company->fetch(PDO::FETCH_ASSOC);

// Fetch master payroll data from ims_payroll
$stmt_payroll = $conn->prepare("SELECT ip.*, CONCAT(ie.f_name, ' ', ie.l_name) AS emp_fullname, ie.salary_type, ie.salary
                                FROM ims_payroll ip
                                LEFT JOIN memployee ie ON ip.emp_id = ie.emp_id
                                WHERE ip.doc_no = :doc_no");
$stmt_payroll->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
$stmt_payroll->execute();
$payroll_master = $stmt_payroll->fetch(PDO::FETCH_ASSOC);

if (!$payroll_master) {
    die("ไม่พบข้อมูลเงินเดือนสำหรับเลขที่เอกสาร: " . $doc_no);
}

// Fetch detail payroll data from ims_payroll_detail
$stmt_details = $conn->prepare("SELECT
                                    ipd.*,
                                    icd.icd_type_desc,                                    
                                    icd.icd_type_sign
                                FROM ims_payroll_detail ipd
                                LEFT JOIN m_income_deduct_type icd ON ipd.icd_type_id = icd.icd_type_id
                                WHERE ipd.doc_no = :doc_no
                                ORDER BY ipd.id ASC"); // Order by id for consistent display
$stmt_details->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
$stmt_details->execute();
$payroll_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);


// Convert total amount to Thai text
$thai_text_net_total = converNumberToThaiText($payroll_master['total_amount']); // total_amount is the net pay

// Define CustomPDF class for TCPDF
class CustomPDF extends TCPDF
{
    // No default footer needed as HTML contains it.
    public function Footer() {}
}

// Create new PDF document
$pdf = new CustomPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true); // Still enable to use the overridden Footer method if you add anything there

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('ใบแจ้งเงินเดือน ' . $payroll_master['doc_no']);
$pdf->SetSubject('Payslip');

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins for each half (relative to the full A4 page)
$pdf->SetMargins(10, 10, 10); // Left, Top, Right
$pdf->SetAutoPageBreak(FALSE, 0); // Disable auto page break, we control layout manually

// Set font
$pdf->SetFont('THSarabunNew', '', 10); // Slightly smaller font for compactness

// Add a single page for both payslips
$pdf->AddPage();

// Function to generate HTML for payslip
function generate_payslip_html($company, $payroll_master, $payroll_details, $thai_text_net_total, $copy_type = "")
{
    // Retrieve user session info for "ผู้พิมพ์" if available
    $full_name_signer = isset($_SESSION['first_name']) && isset($_SESSION['last_name']) ? $_SESSION['first_name'] . " " . $_SESSION['last_name'] : 'ผู้จัดทำ';
    $user_name_printer = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'ฝ่ายบัญชี/บุคคล';

    // Get salary type description
    $salary_type_desc = '';
    if ($payroll_master['salary_type'] === 'D') {
        $salary_type_desc = 'รายวัน';
    } elseif ($payroll_master['salary_type'] === 'M') {
        $salary_type_desc = 'รายเดือน';
    }

    // Adjusted styles for compactness for half A4
    $html = '
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:5px; text-align:center;">
        <tr>
            <td style="width: 100%;">
                <h3 style="margin-bottom: 2px;">ใบแจ้งเงินเดือน ' . $copy_type . '</h3>
                <img src="img/logo/your_company_logo.png" height="30" style="display: block; margin: 0 auto; margin-bottom: 5px;">
                <h4 style="margin-bottom: 2px;">' . ($company['company_name'] ?? 'ชื่อบริษัทของคุณ') . '</h4>
                <p style="margin-top: 0; font-size: 8pt;">
                    ' . ($company['address_1'] ?? '') . ' ' . ($company['address_2'] ?? '') . '<br>
                    ' . ($company['state'] ?? '') . ' ' . ($company['zip_code'] ?? '') . '<br>
                    โทร: ' . ($company['phone'] ?? '') . '
                </p>
            </td>
        </tr>
    </table>

    <table border="0" cellspacing="0" cellpadding="1" width="100%" style="font-size:9pt; margin-top:5px;">
        <tr>
            <td width="50%"><b>เลขที่เอกสาร:</b> ' . $payroll_master['doc_no'] . '</td>
            <td width="50%" align="right"><b>วันที่:</b> ' . date('d/m/Y', strtotime($payroll_master['doc_date'])) . '</td>
        </tr>
        <tr>
            <td width="50%"><b>รหัสพนักงาน:</b> ' . $payroll_master['emp_id'] . '</td>
            <td width="50%" align="right"><b>ชื่อ-นามสกุล:</b> ' . $payroll_master['emp_fullname'] . '</td>
        </tr>
        <tr>
            <td width="50%"><b>ประเภทพนักงาน:</b> ' . $salary_type_desc . '</td>
            <td width="50%" align="right"><b>เงินเดือน/ค่าจ้าง:</b> ' . number_format($payroll_master['salary'], 2) . ' บาท</td>
        </tr>
        <tr>
            <td width="50%"><b>รอบเดือน:</b> ' . getThaiMonthName($payroll_master['payroll_month']) . ' ' . $payroll_master['payroll_year'] . '</td>
            <td width="50%" align="right"><b>วันทำงานในเดือน:</b> ' . number_format($payroll_master['work_day_month'], 2) . ' วัน</td>
        </tr>
    </table>

    <br>';

    // --- Main layout table for Income and Deduction side-by-side ---
    $html .= '<table border="0" cellspacing="0" cellpadding="0" width="100%" style="font-size:9pt;">
                <tr>
                    <td width="49.5%" style="vertical-align: top; padding-right: 2mm;">'; // Left column for Income

    // --- Start Income Table HTML ---
    $html .= '<table border="1" cellspacing="0" cellpadding="3" width="100%" style="table-layout: fixed;">
                <tr style="background-color:#f2f2f2;">
                    <th width="50%" align="center"><b>รายการรายได้</b></th>
                    <th width="25%" align="center"><b>จำนวน</b></th>
                    <th width="25%" align="center"><b>จำนวนเงิน</b></th>
                </tr>';

    $total_income = 0;
    $income_items = [];
    foreach ($payroll_details as $item) {
        if ($item['icd_type_sign'] === '+') {
            $income_items[] = $item;
            $total_income += ($item['total_amount'] ?? 0);
        }
    }

    // Display only up to 3 income items
    for($i = 0; $i < max(count($income_items), 3); $i++) {
        if (isset($income_items[$i])) {
            $item = $income_items[$i];
            $html .= '<tr>
                <td>' . ($item['icd_type_desc'] ?? $item['icd_type_id']) . '</td>
                <td align="right">' . number_format($item['quantity'] ?? 0, 2) . '</td>
                <td align="right">' . number_format($item['total_amount'] ?? 0, 2) . '</td>
            </tr>';
        } else {
            $html .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>'; // Empty row for padding
        }
    }

    $html .= '<tr>
        <td align="right" colspan="2"><b>รวมรายได้ทั้งสิ้น:</b></td>
        <td align="right"><b>' . number_format($total_income, 2) . '</b></td>
    </tr>
    </table>';
    // --- End Income Table HTML ---

    $html .= '</td>
                <td width="1%" style="padding: 0;">&nbsp;</td>'; // Small spacer column between income and deduction

    $html .= '<td width="49.5%" style="vertical-align: top; padding-left: 2mm;">'; // Right column for Deduction

    // --- Start Deduction Table HTML ---
    $html .= '<table border="1" cellspacing="0" cellpadding="3" width="100%" style="table-layout: fixed;">
                <tr style="background-color:#f2f2f2;">
                    <th width="50%" align="center"><b>รายการหัก</b></th>
                    <th width="25%" align="center"><b>จำนวน</b></th>
                    <th width="25%" align="center"><b>จำนวนเงิน</b></th>
                </tr>';

    $total_deduction = 0;
    $deduction_items = [];
    foreach ($payroll_details as $item) {
        if ($item['icd_type_sign'] === '-') {
            $deduction_items[] = $item;
            $total_deduction += ($item['total_amount'] ?? 0);
        }
    }

    // Display only up to 3 deduction items
    for($i = 0; $i < max(count($deduction_items), 3); $i++) {
        if (isset($deduction_items[$i])) {
            $item = $deduction_items[$i];
            $html .= '<tr>
                <td>' . ($item['icd_type_desc'] ?? $item['icd_type_id']) . '</td>
                <td align="right">' . number_format($item['quantity'] ?? 0, 2) . '</td>
                <td align="right">' . number_format($item['total_amount'] ?? 0, 2) . '</td>
            </tr>';
        } else {
            $html .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>'; // Empty row for padding
        }
    }

    $html .= '<tr>
        <td align="right" colspan="2"><b>รวมรายหักทั้งสิ้น:</b></td>
        <td align="right"><b>' . number_format($total_deduction, 2) . '</b></td>
    </tr>
    </table>';
    // --- End Deduction Table HTML ---

    $html .= '</td>
                </tr>
              </table>'; // End main layout table for income/deduction

    $html .= '<br style="line-height: 2mm;">'; // Small space before net total

    $html .= '
    <table border="1" cellspacing="0" cellpadding="3" width="100%" style="font-size:10pt;">
        <tr>
            <td width="75%" align="right" style="background-color:#e6e6e6;"><b>เงินได้สุทธิ:</b></td>
            <td width="25%" align="right" style="background-color:#e6e6e6;"><b>' . number_format($payroll_master['total_amount'], 2) . '</b></td>
        </tr>
        <tr>
            <td colspan="2" align="right"><i>( ' . $thai_text_net_total . ' )</i></td>
        </tr>
    </table>';

    // Footer/Signature Section
    $html .= '<table border="0" cellspacing="0" cellpadding="2" width="100%" style="margin-top:10px; font-size:8pt;">
        <tr>
            <td width="50%" align="left">
                <br><br>
                ______________________________<br>
                ( ชื่อ-นามสกุล พนักงาน )<br>
                ผู้รับเงิน
            </td>
            <td width="50%" align="center">
                <br><br>
                ______________________________<br>
                (' . $full_name_signer . ')<br>
                ผู้จัดทำ
            </td>
        </tr>
    </table>';

    $html .= '<table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-top:10px; font-size:7pt;">
        <tr>
            <td width="50%" align="left">วันที่พิมพ์: ' . date('d/m/Y H:i:s') . '</td>
            <td width="50%" align="right">ผู้พิมพ์: ' . $user_name_printer . '</td>
        </tr>
    </table>';

    return $html;
}

// Generate HTML for Payslip (Original)
$html_original = generate_payslip_html($company, $payroll_master, $payroll_details, $thai_text_net_total, "(ต้นฉบับ)");

// Generate HTML for Payslip (Copy)
$html_copy = generate_payslip_html($company, $payroll_master, $payroll_details, $thai_text_net_total, "(สำเนา)");

// Print Original Payslip on the top half
$pdf->writeHTML($html_original, true, false, true, false, '');

// Draw a dashed line in the middle of the A4 page (A4 height is 297mm, so middle is 148.5mm)
// Parameters: x1, y1, x2, y2, style (dash = 'length,space')
$pdf->Line(10, 148.5, 200, 148.5, array('dash' => '2,2'));

// Set Y position for the copy, slightly below the middle line (148.5mm + 5mm buffer)
$pdf->SetY(148.5 + 5);

// Print Copy Payslip on the bottom half
$pdf->writeHTML($html_copy, true, false, true, false, '');


// Output the PDF to the browser
$filename = 'payslip_' . $payroll_master['doc_no'] . '_' . date('Ymd_His') . '.pdf';
$pdf->Output($filename, 'I'); // 'I' for inline, 'D' for download

/**
 * Helper function to convert month number to Thai month name.
 * You can put this in a separate util file or directly here.
 */
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