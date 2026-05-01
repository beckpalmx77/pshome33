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
    h.house_number,
    CONCAT(lhuser.f_name, ' ', lhuser.l_name) as full_name,
    h.house_status,
    CASE 
        WHEN h.sticker_receive_status = 'Y' THEN 'รับแล้ว'
        WHEN h.sticker_receive_status = 'N' THEN 'ยังไม่ได้รับ'
        ELSE '-'
    END as sticker_receive_status,
    h.sticker_receive_date,
    h.car_no1, h.car_no1_province, h.car_no1_brand, h.car_no1_color, h.car_no1_type,
    h.car_no2, h.car_no2_province, h.car_no2_brand, h.car_no2_color, h.car_no2_type,
    h.car_no3, h.car_no3_province, h.car_no3_brand, h.car_no3_color, h.car_no3_type,
    h.car_no4, h.car_no4_province, h.car_no4_brand, h.car_no4_color, h.car_no4_type,
    h.car_no5, h.car_no5_province, h.car_no5_brand, h.car_no5_color, h.car_no5_type,
    h.car_no6, h.car_no6_province, h.car_no6_brand, h.car_no6_color, h.car_no6_type,
    h.car_no7, h.car_no7_province, h.car_no7_brand, h.car_no7_color, h.car_no7_type,
    h.phone_number,
    m.area_size,
    m.common_fee
FROM ims_house AS h
LEFT JOIN ims_house_line_user AS lhuser ON h.house_number = lhuser.house_number
LEFT JOIN ims_house_master AS m ON h.house_number = m.house_number
WHERE h.house_number = :house_number
LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->execute(['house_number' => $house_number]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die('ไม่พบข้อมูลบ้านเลขที่ ' . $house_number);
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('ข้อมูลสติกเกอร์');

$sheet->fromArray(
    ['บ้านเลขที่', $data['house_number']],
    NULL,
    'A1'
);
$sheet->fromArray(
    ['ชื่อ-นามสกุล', $data['full_name']],
    NULL,
    'A2'
);
$sheet->fromArray(
    ['สถานะที่อยู่อาศัย', $data['house_status'] === 'O' ? 'บ้านตนเอง-ครอบครัว' : ($data['house_status'] === 'R' ? 'บ้านเช่า' : '-')],
    NULL,
    'A3'
);
$sheet->fromArray(
    ['สถานะการรับสติกเกอร์', $data['sticker_receive_status']],
    NULL,
    'A4'
);
$sheet->fromArray(
    ['วันที่รับสติกเกอร์', $data['sticker_receive_date']],
    NULL,
    'A5'
);
$sheet->fromArray(
    ['เบอร์โทรศัพท์', $data['phone_number']],
    NULL,
    'A6'
);
$sheet->fromArray(
    ['พื้นที่บ้าน (ตรว.)', $data['area_size']],
    NULL,
    'A7'
);
$sheet->fromArray(
    ['ค่าส่วนกลางรายเดือน', $data['common_fee']],
    NULL,
    'A8'
);

$sheet->fromArray(
    ['', '', '', '', ''],
    NULL,
    'A9'
);

$sheet->fromArray(
    ['ลำดับ', 'ทะเบียนรถ', 'จังหวัด', 'ยี่ห้อ-รุ่น', 'สี', 'ประเภท'],
    NULL,
    'A10'
);

$car_data = [
    [1, $data['car_no1'], $data['car_no1_province'], $data['car_no1_brand'], $data['car_no1_color'], $data['car_no1_type']],
    [2, $data['car_no2'], $data['car_no2_province'], $data['car_no2_brand'], $data['car_no2_color'], $data['car_no2_type']],
    [3, $data['car_no3'], $data['car_no3_province'], $data['car_no3_brand'], $data['car_no3_color'], $data['car_no3_type']],
    [4, $data['car_no4'], $data['car_no4_province'], $data['car_no4_brand'], $data['car_no4_color'], $data['car_no4_type']],
    [5, $data['car_no5'], $data['car_no5_province'], $data['car_no5_brand'], $data['car_no5_color'], $data['car_no5_type']],
    [6, $data['car_no6'], $data['car_no6_province'], $data['car_no6_brand'], $data['car_no6_color'], $data['car_no6_type']],
    [7, $data['car_no7'], $data['car_no7_province'], $data['car_no7_brand'], $data['car_no7_color'], $data['car_no7_type']]
];

$row = 11;
foreach ($car_data as $car) {
    if (!empty($car[1])) {
        $sheet->fromArray($car, NULL, 'A' . $row);
        $row++;
    }
}

foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$sheet->getStyle('A10:F10')->getFont()->setBold(true);
$sheet->getStyle('A10:F10')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="sticker_' . $house_number . '_' . date('Ymd') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
