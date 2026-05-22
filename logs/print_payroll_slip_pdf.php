<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL & ~E_DEPRECATED); // Hides deprecated warnings. For production, consider 'error_reporting(0);'
session_start();
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
include 'config/connect_db.php';
include('util/number_to_thai_text.php'); // Assuming this function is available for number to text conversion

// Get month and year from GET request
$payroll_month = isset($_GET['payroll_month']) ? $_GET['payroll_month'] : '';
$payroll_year = isset($_GET['payroll_year']) ? $_GET['payroll_year'] : '';

if (!$payroll_month || !$payroll_year) {
    die("ไม่พบข้อมูลเดือนและปีที่ต้องการพิมพ์สลิปเงินเดือน");
}

// Fetch company information (if needed for header/footer)
$sql_company = "SELECT company_name, address_1, address_2, state, zip_code, phone FROM ims_company LIMIT 1";
$stmt_company = $conn->prepare($sql_company);
$stmt_company->execute();
$company = $stmt_company->fetch(PDO::FETCH_ASSOC);

// Define CustomPDF class for TCPDF
class CustomPDF extends TCPDF
{
    // No default footer needed as HTML contains it.
    public function Footer()
    {
    }
}

// Create new PDF document
$pdf = new CustomPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true); // Still enable to use the overridden Footer method if you add anything there

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('ใบแจ้งเงินเดือน เดือน ' . getThaiMonthName($payroll_month) . ' ปี ' . $payroll_year); // Updated Title
$pdf->SetSubject('Payslip Batch');

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins for each half (relative to the full A4 page)
$pdf->SetMargins(10, 10, 10); // Left, Top, Right
$pdf->SetAutoPageBreak(FALSE, 0); // Disable auto page break, we control layout manually

// Set font - Increased base font size to 12pt for medium size
$pdf->SetFont('THSarabunNew', '', 12);

// --- Fetch ALL payroll master data for the selected month and year ---
$stmt_all_payrolls = $conn->prepare("SELECT ip.*, CONCAT(ie.f_name, ' ', ie.l_name) AS emp_fullname, ie.salary_type, ie.salary
                                     FROM ims_payroll ip
                                     LEFT JOIN memployee ie ON ip.emp_id = ie.emp_id
                                     WHERE ip.payroll_month = :payroll_month AND ip.payroll_year = :payroll_year
                                     ORDER BY ip.emp_id ASC"); // Order by employee for consistency
$stmt_all_payrolls->bindParam(':payroll_month', $payroll_month, PDO::PARAM_INT);
$stmt_all_payrolls->bindParam(':payroll_year', $payroll_year, PDO::PARAM_INT);
$stmt_all_payrolls->execute();
$all_payroll_masters = $stmt_all_payrolls->fetchAll(PDO::FETCH_ASSOC);

if (empty($all_payroll_masters)) {
    die("ไม่พบข้อมูลเงินเดือนสำหรับเดือน " . getThaiMonthName($payroll_month) . " ปี " . $payroll_year);
}

// --- Loop through each employee's payroll and generate their payslip ---
foreach ($all_payroll_masters as $payroll_master) {
    // Fetch detail payroll data for the current doc_no
    $current_doc_no = $payroll_master['doc_no'];
    // Ensure that 'remark' column is fetched if it exists in ims_payroll_detail
    $stmt_details = $conn->prepare("SELECT
                                        ipd.*,
                                        icd.icd_type_desc,
                                        icd.icd_type_sign
                                    FROM ims_payroll_detail ipd
                                    LEFT JOIN m_income_deduct_type icd ON ipd.icd_type_id = icd.icd_type_id
                                    WHERE ipd.doc_no = :doc_no
                                    ORDER BY ipd.id ASC");
    $stmt_details->bindParam(':doc_no', $current_doc_no, PDO::PARAM_STR);
    $stmt_details->execute();
    $payroll_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

    // Convert total amount to Thai text for THIS employee's net total
    // IMPORTANT: Ensure 'converNumberToThaiText' function is properly defined
    // and available from 'util/number_to_thai_text.php'.
    $thai_text_net_total = converNumberToThaiText($payroll_master['total_amount']);

    // Add a new page for each employee's payslip
    // This ensures each employee starts on a fresh A4 page, with 2 copies per page.
    $pdf->AddPage();

    // Generate HTML for Payslip (Original)
    $html_original = generate_payslip_html($company, $payroll_master, $payroll_details, $thai_text_net_total, "(ต้นฉบับ)");

    // Generate HTML for Payslip (Copy)
    $html_copy = generate_payslip_html($company, $payroll_master, $payroll_details, $thai_text_net_total, "(สำเนา)");

    // Print Original Payslip on the top half
    $pdf->writeHTML($html_original, true, false, true, false, '');

    // Draw the dashed line in the middle of the A4 page (A4 height is 297mm, so middle is 148.5mm)
    $pdf->Line(10, 148.5, 200, 148.5, array('dash' => '2,2'));

    // Set Y position for the copy, slightly below the middle line (148.5mm + 5mm buffer)
    $pdf->SetY(148.5 + 5);

    // Print Copy Payslip on the bottom half
    $pdf->writeHTML($html_copy, true, false, true, false, '');

    // อัปเดตสถานะการพิมพ์
    $print_slip_status = $payroll_master['print_slip_status'];
    if ($print_slip_status !== 'Y') {
        $stmt_items = $conn->prepare("UPDATE ims_payroll
                                  SET print_slip_status = 'Y', print_slip_timestamp = NOW()
                                  WHERE doc_no = :doc_no AND print_slip_status = 'N'");
    } else {
        $stmt_items = $conn->prepare("UPDATE ims_payroll
                                  SET print_slip_last_timestamp = NOW()
                                  WHERE doc_no = :doc_no AND print_slip_status = 'Y'");
    }

    $stmt_items->bindParam(':doc_no', $payroll_master['doc_no'], PDO::PARAM_STR);
    $stmt_items->execute();

}

// Output the PDF to the browser
$filename = 'payslip_batch_' . $payroll_month . '_' . $payroll_year . '_' . date('Ymd_His') . '.pdf';
$pdf->Output($filename, 'I');

/**
 * Helper function to convert month number to Thai month name.
 * You can put this in a separate util file or directly here.
 */
function getThaiMonthName($monthNum)
{
    $months = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
        4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
        7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
        10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    return $months[(int)$monthNum] ?? ''; // Ensure monthNum is integer
}

/**
 * Function to generate HTML for payslip for a single employee.
 * This function should be defined once, either here or in an included file.
 */
function generate_payslip_html($company, $payroll_master, $payroll_details, $thai_text_net_total, $copy_type = "")
{
    // Retrieve user session info for "ผู้พิมพ์" if available
    $user_name_printer = $_SESSION['user_name'] ?? 'ฝ่ายบัญชี/บุคคล';

    // Get salary type description
    $salary_type_desc = '';
    if ($payroll_master['salary_type'] === 'D') {
        $salary_type_desc = 'รายวัน';
    } elseif ($payroll_master['salary_type'] === 'M') {
        $salary_type_desc = 'รายเดือน';
    }

    // Adjusted styles for compactness for half A4
    $html = '
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:5px;">
        <tr>
            <td width="20%" style="text-align: left; vertical-align: top;">
                <img src="img/logo/logo text-01.png" height="40">
            </td>
            <td width="80%" style="text-align: right; vertical-align: top; padding-left: 5mm;">
                <h2 style="margin-bottom: 2px;">ใบแจ้งเงินเดือน ' . $copy_type . '</h2>
                <p style="margin-top: 0; font-size: 12pt;"> 
                    <b>' . ($company['company_name'] ?? '') . '</b><br>
                    ' . ($company['address_1'] ?? '') . ' ' . ($company['address_2'] ?? '') . ' ' . ($company['state'] ?? '') . ' ' . ($company['zip_code'] ?? '') . '
                </p>
            </td>
        </tr>
    </table>';

    // Start of the section that was previously in template_payslip_section.php
    $html .= '
    <table border="0" cellspacing="0" cellpadding="1" width="100%" style="font-size:11pt; margin-top:5px;">
        <tr>
            <td width="50%"><b>เลขที่เอกสาร:</b> ' . ($payroll_master['doc_no'] ?? '') . '</td>
            <td width="50%" align="right"><b>วันที่:</b> ' . date('d/m/Y', strtotime($payroll_master['doc_date'] ?? date('Y-m-d'))) . '</td>
        </tr>
        <tr>
            <td width="50%"><b>รหัสพนักงาน:</b> ' . ($payroll_master['emp_id'] ?? '') . '</td>
            <td width="50%" align="right"><b>ชื่อ-นามสกุล:</b> ' . ($payroll_master['emp_fullname'] ?? '') . '</td>
        </tr>
        <tr>
            <td width="50%"><b>ประเภทพนักงาน:</b> ' . $salary_type_desc . '</td>
            <td width="50%" align="right"><b>เงินเดือน/ค่าจ้าง:</b> ' . number_format($payroll_master['salary'] ?? 0, 2) . ' บาท</td>
        </tr>
        <tr>
            <td width="100%" colspan="2"><b>งวดเดือน:</b> ' . getThaiMonthName($payroll_master['payroll_month'] ?? '') . ' ' . ($payroll_master['payroll_year'] ?? '') . '</td>
        </tr>
    </table>

    <br>';

    // --- Main layout table for Income and Deduction side-by-side and now including Net Total ---
    $html .= '<table border="0" cellspacing="0" cellpadding="0" width="100%" style="font-size:11pt;">
                <tr>
                    <td width="49.5%" style="vertical-align: top; padding-right: 2mm;">'; // Left column for Income

    // --- Start Income Table HTML ---
    $html .= '<table border="1" cellspacing="0" cellpadding="3" width="100%" style="table-layout: fixed;">
                <tr style="background-color:#f2f2f2;">
                    <th width="75%" align="center"><b>รายการรายได้</b></th>
                    <th width="25%" align="center"><b>จำนวนเงิน</b></th>
                </tr>';

    $total_income = 0;
    $income_items = [];
    foreach ($payroll_details as $item) {
        if (isset($item['icd_type_sign']) && $item['icd_type_sign'] === '+') {
            $income_items[] = $item;
            $total_income += ($item['total_amount'] ?? 0);
        }
    }

    // Display only up to 3 income items
    for ($i = 0; $i < max(count($income_items), 3); $i++) {
        if (isset($income_items[$i])) {
            $item = $income_items[$i];
            $html .= '<tr>
                <td>' . ($item['icd_type_desc'] ?? $item['icd_type_id']) . ' ' . (isset($item['remark']) && $item['remark'] != '' ? ' (<small style="font-size: 9pt;"><i>' . htmlspecialchars($item['remark'] ?? '') . '</i></small>)' : '') . '
                </td>
                <td align="right">' . number_format($item['total_amount'] ?? 0, 2) . '</td>
            </tr>';
        } else {
            $html .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>'; // Empty row for padding - Adjusted colspan
        }
    }

    $html .= '<tr>
        <td align="right"><b>รวมรายได้ทั้งสิ้น:</b></td>
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
                    <th width="75%" align="center"><b>รายการหัก</b></th>
                    <th width="25%" align="center"><b>จำนวนเงิน</b></th>
                </tr>';

    $total_deduction = 0;
    $deduction_items = [];
    foreach ($payroll_details as $item) {
        if (isset($item['icd_type_sign']) && $item['icd_type_sign'] === '-') {
            $deduction_items[] = $item;
            $total_deduction += ($item['total_amount'] ?? 0);
        }
    }

    // Display only up to 3 deduction items
    for ($i = 0; $i < max(count($deduction_items), 3); $i++) {
        if (isset($deduction_items[$i])) {
            $item = $deduction_items[$i];
            $html .= '<tr>
                <td>' . ($item['icd_type_desc'] ?? $item['icd_type_id']) . ' ' . (isset($item['remark']) && $item['remark'] != '' ? ' (<small style="font-size: 9pt;"><i>' . htmlspecialchars($item['remark'] ?? '') . '</i></small>)' : '') . '
                </td>
                <td align="right">' . number_format($item['total_amount'] ?? 0, 2) . '</td>
            </tr>';
        } else {
            $html .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>'; // Empty row for padding - Adjusted colspan
        }
    }

    $html .= '<tr>
        <td align="right"><b>รวมรายหักทั้งสิ้น:</b></td>
        <td align="right"><b>' . number_format($total_deduction, 2) . '</b></td>
    </tr>
    </table>';
    // --- End Deduction Table HTML ---

    $html .= '</td>
                </tr>'; // End the row for Income and Deduction columns

    // --- New row for Net Total Table, spanning across both main columns ---
    $html .= '<tr>
                <td colspan="3" style="vertical-align: top; padding-top: 2mm;">'; // colspan="3" to span income, spacer, and deduction columns

    $html .= '
    <table border="0" cellspacing="0" cellpadding="0" width="100%" style="font-size:12pt;">
        <tr>
            <td width="75%" align="right" style="background-color:#e6e6e6;"><b>เงินได้สุทธิ:</b></td>
            <td width="25%" align="right" style="background-color:#e6e6e6;"><b>' . number_format($payroll_master['total_amount'] ?? 0, 2) . '</b></td>
        </tr>
        <tr>
            <td colspan="2" align="right"><i>( ' . $thai_text_net_total . ' )</i></td>
        </tr>
    </table>';

    $html .= '</td>
                </tr>'; // End the new row for Net Total

    $html .= '</table>'; // End main layout table for income/deduction/net total

    // Footer/Signature Section - Modified to 2 columns
    $html .= '<table border="0" cellspacing="0" cellpadding="2" width="100%" style="margin-top:10px; font-size:10pt;">
        <tr>
            <td width="50%" align="center" style="vertical-align: top;">
                <br><br>
                ______________________________<br>
                ( ' . ($payroll_master['emp_fullname'] ?? '') . ' )<br>
                ผู้รับเงิน
            </td>
            <td width="50%" align="center" style="vertical-align: top;">
                <br><br>
                ______________________________<br>
                ( วันที่รับเงิน )
            </td>
        </tr>
    </table>';
    // End of the section that was previously in template_payslip_section.php

    $html .= '<table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-top:10px; font-size:9pt;">
        <tr>
            <td width="50%" align="left">วันที่พิมพ์: ' . date('d/m/Y H:i:s') . '</td>
            <td width="50%" align="right">ผู้พิมพ์: ' . $user_name_printer . '</td>
        </tr>
    </table>';

    return $html;
}