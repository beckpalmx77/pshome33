<?php
include '../config/connect_db.php';
require '../vendor/autoload.php'; // อย่าลืมปรับ path ให้ตรงกับโปรเจกต์

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// ดึงข้อมูลจาก ims_house_master + LEFT JOIN ims_house
$sql = "
SELECT 
    m.house_number,
    m.alley,    
    m.area_size,
    m.garbage_collection_fee,
    m.common_fee,
    m.status AS master_status,
    h.contact_name,
    h.phone_number,
    h.alley,
    m.land_no,
    h.remark AS house_remark,
    h.house_status
FROM 
    ims_house_master m
LEFT JOIN 
    ims_house h ON m.house_number = h.house_number
ORDER BY 
    m.id
";

$stmt = $conn->query($sql);
$houses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// สร้าง Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// ตั้งหัวตาราง
$sheet->fromArray(
    [
        'เลขที่บ้าน',
        'ซอย',
        'ขนาดพื้นที่',
        'ค่าขยะ',
        'ค่าส่วนกลาง',
        'สถานะ (master)',
        'ชื่อผู้ติดต่อ',
        'เบอร์โทร',
        'ซอย',
        'หมายเลขโฉนด',
        'หมายเหตุ',
        'สถานะบ้าน'
    ],
    NULL,
    'A1'
);

// เติมข้อมูลแถวถัดไป
$row = 2;
foreach ($houses as $house) {
    $sheet->setCellValue("A$row", $house['house_number']);
    $sheet->setCellValue("B$row", $house['alley']);
    $sheet->setCellValue("C$row", $house['area_size']);
    $sheet->setCellValue("D$row", $house['garbage_collection_fee']);
    $sheet->setCellValue("E$row", $house['common_fee']);
    $sheet->setCellValue("F$row", $house['master_status']);
    $sheet->setCellValue("G$row", $house['contact_name']);
    $sheet->setCellValue("H$row", $house['phone_number']);
    $sheet->setCellValue("I$row", $house['alley']);
    $sheet->setCellValue("J$row", $house['land_no']);
    $sheet->setCellValue("K$row", $house['house_remark']);
    $sheet->setCellValue("L$row", $house['house_status'] === "O" ? "บ้านตนเอง-ครอบครัว" : ($house['house_status'] === "R" ? "บ้านเช่า" : "ไม่ระบุ"));
    $row++;
}

// ตั้งค่า header ให้ดาวน์โหลดไฟล์
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="house_master_export.xlsx"');
header('Cache-Control: max-age=0');

// เขียนไฟล์ Excel ออก
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
