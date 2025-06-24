<?php
session_start();
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
include('../config/connect_db.php'); // เชื่อมต่อฐานข้อมูล
include('../util/number_to_thai_text.php'); // ฟังก์ชันแปลงตัวเลขเป็นข้อความ (อาจไม่จำเป็นต้องใช้ในรายงานนี้)

date_default_timezone_set('Asia/Bangkok');

// รับค่าจาก POST
$year = isset($_POST["year"]) ? trim($_POST["year"]) : '';
$months = isset($_POST["months"]) ? $_POST["months"] : [];

// ตรวจสอบข้อมูลปี
if ($year == '') {
    die("กรุณาเลือกปีให้ถูกต้อง");
}

// ตรวจสอบว่า $months เป็น array และไม่ว่าง
$monthList = [];
if (is_array($months) && count($months) > 0) {
    $monthList = array_filter($months); // ลบค่าว่างออก
}

// สร้าง SQL Query สำหรับดึงข้อมูล
$sql = "SELECT * FROM v_ims_expenses WHERE exp_year = :year";

$params = [':year' => $year];

// เพิ่มเงื่อนไขเดือน (ถ้ามี)
if (count($monthList) > 0) {
    $placeholders = [];
    foreach ($monthList as $index => $month) {
        $ph = ':month' . $index;
        $placeholders[] = $ph;
        $params[$ph] = $month;
    }
    $sql .= " AND exp_month IN (" . implode(',', $placeholders) . ")";
}

// *** เพิ่มการเรียงลำดับตาม expense_date ที่ถูกแปลงเป็น DATE เพื่อให้เรียงตามวันที่จริง ไม่ใช่เรียงตาม String ***
$sql .= " ORDER BY STR_TO_DATE(expense_date, '%d-%m-%Y') ASC, id ASC";

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
$pdf->SetTitle('รายงานค่าใช้จ่าย ปี ' . $year . (count($monthList) > 0 ? ' เดือน ' . implode(', ', $monthList) : ''));
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

// ส่วนหัวรายงาน - เพิ่มโลโก้ที่นี่
$html .= '<div style="text-align: center; margin-bottom: 10px;">';
$html .= '<img src="../img/logo/ps33-rec-logo.png" style="max-height: 30mm; display: block; margin: 0 auto;">'; // ปรับ max-height หรือ width ตามขนาดที่ต้องการ
$html .= '</div>';

$html .= '<h2 style="text-align: center; margin-bottom: 5px;">รายงานค่าใช้จ่าย</h2>';
$html .= '<p style="text-align: center; font-size: 11pt; margin-top: 0;">';
$html .= '<b>ปี:</b> ' . $year;
if (count($monthList) > 0) {
    // Helper function to get Thai month name
    function getThaiMonthName($monthNum) {
        $months = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
            4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
            7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
            10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];
        return $months[(int)$monthNum] ?? '';
    }
    $thai_months = array_map('getThaiMonthName', $monthList);
    $html .= ' &nbsp;&nbsp;<b>เดือน:</b> ' . implode(', ', $thai_months);
}
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
    '8%',  // [0] จ่ายให้ (ผู้ขาย-ผู้รับเหมา)
    '7%',  // [1] วันที่ใช้จ่าย
    '4%',  // [2] เดือน
    '4%',  // [3] ปี
    '8%',  // [4] เลขที่ใบแจ้งหนี้ (INV.)
    '8%',  // [5] หมวดหมู่
    '15%', // [6] รายละเอียด
    '5%',  // [7] จำนวน
    '5%',  // [8] หน่วย
    '7%',  // [9] จำนวนเงิน  <-- คอลัมน์เป้าหมาย
    '10%', // [10] หมายเหตุ
    '7%',  // [11] สถานะอนุมัติ
    '7%',  // [12] วันที่บันทึก
    '5%'   // [13] วิธีชำระเงิน
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
// "จำนวนเงิน" คือคอลัมน์ที่ 10 (index 9)
// จำนวนคอลัมน์ทั้งหมดคือ 14
// ดังนั้น colspan สำหรับข้อความ "รวมยอดค่าใช้จ่ายทั้งสิ้น:" จะเป็น 9 คอลัมน์ (0-8)
// และเซลล์ว่างด้านขวาจะเป็น 4 คอลัมน์ (10-13)
$html .= '<tr>
    <td colspan="9" align="right"><b>รวมยอดค่าใช้จ่ายทั้งสิ้น:</b></td>
    <td width="' . $col_widths[9] . '" align="right"><b>' . number_format($grand_total_amount, 2) . '</b></td>
    <td colspan="4"></td> 
</tr>';

$html .= '</table>';

// เขียน HTML ลงใน PDF
$pdf->writeHTML($html, true, false, true, false, '');

// สร้างชื่อไฟล์สำหรับดาวน์โหลด
$monthText = count($monthList) > 0 ? implode('-', $monthList) : 'all-months';
$filename = "expenses-report-" . $monthText . "-" . $year . "_" . date('Ymd_His') . ".pdf";

// Output PDF ไปยังเบราว์เซอร์
$pdf->Output($filename, 'I'); // 'I' สำหรับแสดงในเบราว์เซอร์, 'D' สำหรับดาวน์โหลด
exit;

?>