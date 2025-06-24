<?php
session_start();
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
include('../config/connect_db.php'); // เชื่อมต่อฐานข้อมูล
include('../util/number_to_thai_text.php'); // ฟังก์ชันแปลงตัวเลขเป็นข้อความ (อาจไม่จำเป็นต้องใช้ในรายงานนี้)

date_default_timezone_set('Asia/Bangkok');

// รับค่าจาก POST (เปลี่ยนจาก months และ year เป็น start_date และ end_date)
$start_date_str = isset($_POST["start_date"]) ? trim($_POST["start_date"]) : '';
$end_date_str = isset($_POST["end_date"]) ? trim($_POST["end_date"]) : '';

// ตรวจสอบข้อมูลวันที่
if ($start_date_str == '' || $end_date_str == '') {
    die("กรุณาเลือก 'เริ่มต้นวันที่' และ 'ถึงวันที่' ให้ถูกต้อง");
}

// แปลงรูปแบบวันที่จาก DD-MM-YYYY (ที่ส่งมาจาก Frontend)
// และตั้งชื่อไฟล์ให้สอดคล้องกับช่วงวันที่
$start_date_for_filename = DateTime::createFromFormat('d-m-Y', $start_date_str)->format('Y-m-d');
$end_date_for_filename = DateTime::createFromFormat('d-m-Y', $end_date_str)->format('Y-m-d');
$filename_prefix = "expenses-report-" . $start_date_for_filename . "_to_" . $end_date_for_filename;


// สร้าง SQL Query สำหรับดึงข้อมูล
// ใช้ STR_TO_DATE เพื่อแปลง expense_date จาก 'DD-MM-YYYY' ใน DB ให้เป็น DATE type สำหรับการเปรียบเทียบ
// และแปลง :start_date, :end_date ที่ส่งมา (ในรูปแบบ 'DD-MM-YYYY') ให้เป็น DATE type เช่นกัน
$sql = "SELECT * FROM v_ims_expenses 
        WHERE STR_TO_DATE(expense_date, '%d-%m-%Y') BETWEEN STR_TO_DATE(:start_date, '%d-%m-%Y') AND STR_TO_DATE(:end_date, '%d-%m-%Y')
        ORDER BY STR_TO_DATE(expense_date, '%d-%m-%Y') ASC, id ASC"; // เรียงตามวันที่และ ID เพื่อความสม่ำเสมอ

$params = [
    ':start_date' => $start_date_str, // ส่งวันที่ในรูปแบบ DD-MM-YYYY
    ':end_date' => $end_date_str    // ส่งวันที่ในรูปแบบ DD-MM-YYYY
];

// เตรียมและประมวลผล Query
try {
    $query = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $query->bindValue($key, $value, PDO::PARAM_STR);
    }
    $query->execute();
    $expenses_data = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}


// --- TCPDF Initialization ---
// สร้าง PDF document ใหม่ (แนวนอน A4)
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// กำหนดข้อมูลเอกสาร
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('รายงานค่าใช้จ่าย วันที่ ' . $start_date_str . ' ถึง ' . $end_date_str);
$pdf->SetSubject('รายงานค่าใช้จ่าย');

// กำหนดฟอนต์ Monospaced เริ่มต้น
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// กำหนด Margin (ปรับสำหรับแนวนอน)
$pdf->SetMargins(10, 10, 10); // ซ้าย, บน, ขวา
$pdf->SetAutoPageBreak(TRUE, 15); // ตั้งค่าการแบ่งหน้าอัตโนมัติ 15mm จากขอบล่าง

// กำหนดฟอนต์หลัก
$pdf->SetFont('THSarabunNew', '', 10); // ฟอนต์ขนาดเล็กลงสำหรับตารางรายละเอียด

// เพิ่มหน้าใหม่
$pdf->AddPage();

// --- สร้าง HTML สำหรับรายงาน ---
$html = '';

// ส่วนหัวรายงาน
$html .= '<h2 style="text-align: center; margin-bottom: 5px;">รายงานค่าใช้จ่าย</h2>';
$html .= '<p style="text-align: center; font-size: 11pt; margin-top: 0;">';
$html .= '<b>เริ่มต้นวันที่:</b> ' . $start_date_str . ' &nbsp;&nbsp;<b>ถึงวันที่:</b> ' . $end_date_str;
$html .= '</p>';
$html .= '<p style="text-align: right; font-size: 9pt;"><b>วันที่พิมพ์:</b> ' . date('d/m/Y H:i:s') . '</p>';

// ส่วนตารางข้อมูล
$html .= '<table border="1" cellspacing="0" cellpadding="4" style="font-size:9pt; width: 100%;">
    <tr style="background-color:#f2f2f2;">';

// หัวตาราง PDF
$pdf_headers = [
    "จ่ายให้",
    "วันที่ใช้จ่าย",
    "เดือน",
    "ปี",
    "เลขที่ INV.",
    "หมวดหมู่",
    "รายละเอียด",
    "จำนวน",
    "หน่วย",
    "จำนวนเงิน",
    "หมายเหตุ",
    "สถานะอนุมัติ",
    "วันที่บันทึก",
    "วิธีชำระเงิน"
];

// กำหนดความกว้างของคอลัมน์ (รวมกันควรเป็น 100% สำหรับ width="100%")
// A4 แนวนอน (297mm) - margin (10*2=20mm) = 277mm พื้นที่ใช้งาน
// สามารถปรับ % ตามความเหมาะสม
$col_widths = [
    '8%',  // จ่ายให้ (ผู้ขาย-ผู้รับเหมา)            [0]
    '7%',  // วันที่ใช้จ่าย                      [1]
    '4%',  // เดือน                             [2]
    '4%',  // ปี                                [3]
    '8%',  // เลขที่ใบแจ้งหนี้ (INV.)           [4]
    '8%',  // หมวดหมู่                          [5]
    '15%', // รายละเอียด                        [6]
    '5%',  // จำนวน                             [7]
    '5%',  // หน่วย                             [8]
    '7%',  // จำนวนเงิน                         [9] <-- คอลัมน์เป้าหมาย
    '10%', // หมายเหตุ                          [10]
    '7%',  // สถานะอนุมัติ                       [11]
    '7%',  // วันที่บันทึก                       [12]
    '5%'   // วิธีชำระเงิน                      [13]
];


foreach ($pdf_headers as $index => $header_text) {
    $html .= '<th width="' . $col_widths[$index] . '" align="center"><b>' . $header_text . '</b></th>';
}
$html .= '</tr>';

$grand_total_amount = 0;

if (count($expenses_data) > 0) {
    foreach ($expenses_data as $row) {
        $grand_total_amount += (float)($row['amount'] ?? 0); // รวมยอดเงิน

        $html .= '<tr>';
        $html .= '<td width="' . $col_widths[0] . '">' . ($row['receipt_name'] ?? '') . '</td>';
        // ตรวจสอบและแปลงรูปแบบวันที่เป็น DD/MM/YYYY ใน PDF
        $expense_date_formatted = '';
        if (!empty($row['expense_date'])) {
            $date_obj = DateTime::createFromFormat('d-m-Y', $row['expense_date']);
            if ($date_obj) {
                $expense_date_formatted = $date_obj->format('d/m/Y');
            }
        }
        $html .= '<td width="' . $col_widths[1] . '" align="center">' . $expense_date_formatted . '</td>';
        $html .= '<td width="' . $col_widths[2] . '" align="center">' . ($row['exp_month'] ?? '') . '</td>';
        $html .= '<td width="' . $col_widths[3] . '" align="center">' . ($row['exp_year'] ?? '') . '</td>';
        $html .= '<td width="' . $col_widths[4] . '">' . ($row['inv'] ?? '') . '</td>';
        $html .= '<td width="' . $col_widths[5] . '">' . ($row['category_name'] ?? '') . '</td>';
        $html .= '<td width="' . $col_widths[6] . '">' . ($row['description'] ?? '') . '</td>';
        $html .= '<td width="' . $col_widths[7] . '" align="right">' . number_format($row['qty'] ?? 0, 2) . '</td>';
        $html .= '<td width="' . $col_widths[8] . '" align="center">' . ($row['unit_name'] ?? '') . '</td>';
        $html .= '<td width="' . $col_widths[9] . '" align="right">' . number_format($row['amount'] ?? 0, 2) . '</td>';
        $html .= '<td width="' . $col_widths[10] . '">' . ($row['remark'] ?? '') . '</td>';
        $html .= '<td width="' . $col_widths[11] . '" align="center">' . (($row['approve_status'] ?? 'N') === "Y" ? "อนุมัติแล้ว" : "รออนุมัติ") . '</td>';
        // ตรวจสอบและแปลงรูปแบบวันที่ created_at
        $created_at_formatted = '';
        if (!empty($row['created_at'])) {
            $date_obj = DateTime::createFromFormat('Y-m-d H:i:s', $row['created_at']);
            if ($date_obj) {
                $created_at_formatted = $date_obj->format('d/m/Y H:i');
            }
        }
        $html .= '<td width="' . $col_widths[12] . '" align="center">' . $created_at_formatted . '</td>';
        $html .= '<td width="' . $col_widths[13] . '">' . ($row['payment_method'] ?? '') . '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="' . count($pdf_headers) . '" align="center">ไม่พบข้อมูลค่าใช้จ่าย</td></tr>';
}

// --- แก้ไขแถวรวมยอดทั้งหมดที่นี่ ---
$html .= '<tr>
    <td colspan="9" align="right"><b>รวมยอดค่าใช้จ่ายทั้งสิ้น:</b></td>
    <td width="' . $col_widths[9] . '" align="right"><b>' . number_format($grand_total_amount, 2) . '</b></td>
    <td colspan="4"></td> 
</tr>';

$html .= '</table>';

// เขียน HTML ลงใน PDF
$pdf->writeHTML($html, true, false, true, false, '');

// สร้างชื่อไฟล์สำหรับดาวน์โหลด
$filename = $filename_prefix . "_" . date('Ymd_His') . ".pdf";

// Output PDF ไปยังเบราว์เซอร์
$pdf->Output($filename, 'I'); // 'I' สำหรับแสดงในเบราว์เซอร์, 'D' สำหรับดาวน์โหลด
exit;

?>