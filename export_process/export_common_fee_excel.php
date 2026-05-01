<?php
include '../config/connect_db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$house_number = $_GET['house_number'] ?? '';

if (empty($house_number)) {
    die('กรุณาระบุหมายเลขบ้าน');
}

$sql = "SELECT 
    payment_date,
    month_name_start,
    month_name_to,
    period_year,
    amount,
    CASE 
        WHEN payment_status = 'Y' THEN 'ชำระแล้ว'
        WHEN payment_status = 'N' THEN 'ยังไม่ชำระ'
        ELSE payment_status
    END as payment_status,
    detail
FROM v_ims_house_payment
WHERE house_number = :house_number
ORDER BY payment_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute(['house_number' => $house_number]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('ประวัติการชำระค่าส่วนกลาง');

$sheet->fromArray(
    [
        'วันที่เอกสาร',
        'งวดเดือนเริ่มต้น',
        'ถึงงวดเดือน',
        'ปี',
        'ยอดชำระ',
        'สถานะ',
        'ผู้ชำระ'
    ],
    NULL,
    'A1'
);

$row = 2;
foreach ($payments as $payment) {
    $sheet->setCellValue("A$row", $payment['payment_date']);
    $sheet->setCellValue("B$row", $payment['month_name_start']);
    $sheet->setCellValue("C$row", $payment['month_name_to']);
    $sheet->setCellValue("D$row", $payment['period_year']);
    $sheet->setCellValue("E$row", $payment['amount']);
    $sheet->setCellValue("F$row", $payment['payment_status']);
    $sheet->setCellValue("G$row", $payment['detail']);
    $row++;
}

foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$sheet->getStyle('A1:G1')->getFont()->setBold(true);
$sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="common_fee_' . $house_number . '_' . date('Ymd') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
