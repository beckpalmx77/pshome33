<?php

include('../config/connect_db.php');
date_default_timezone_set('Asia/Bangkok');

// รับค่าจาก POST สำหรับซอยเริ่มต้นและซอยสิ้นสุด
$alley_start = isset($_POST["alley_start"]) ? trim($_POST["alley_start"]) : '';
$alley_to = isset($_POST["alley_to"]) ? trim($_POST["alley_to"]) : '';

// ตรวจสอบข้อมูลซอย
if ($alley_start == '' || $alley_to == '') {
    exit("กรุณาเลือก 'หมายเลขซอย เริ่มต้น' และ 'หมายเลขซอย ถึง' ให้ถูกต้อง");
}

// ตั้งชื่อไฟล์
$filename = "house_master-" . $alley_start . "_to_" . $alley_to . "_" . date('Ymd_His') . ".csv";

// Header สำหรับดาวน์โหลด (Content-Type ยังคงเป็น text/csv เพื่อให้เบราว์เซอร์รู้ว่าเป็นข้อมูลที่คั่นด้วยคอมมา)
header('Content-Type: text/csv; charset=TIS-620');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// สร้าง SQL
// ใช้ CAST(alley AS UNSIGNED) เพื่อให้การเปรียบเทียบช่วงซอยทำงานอย่างถูกต้องสำหรับค่าที่เป็นตัวเลข
$sql = "SELECT  *
        FROM ims_house_master 
        WHERE CAST(alley AS UNSIGNED) BETWEEN CAST(:alley_start AS UNSIGNED) AND CAST(:alley_to AS UNSIGNED)
        ORDER BY CAST(alley AS UNSIGNED) ASC, house_number ASC";

$params = [
    ':alley_start' => $alley_start,
    ':alley_to' => $alley_to
];

// เตรียม query
$query = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $query->bindValue($key, $value, PDO::PARAM_STR);
}
$query->execute();

// หัวตาราง Excel (อ้างอิงจากโครงสร้างตาราง ims_house_master)
$header = [
    "บ้านเลขที่",
    "ซอย",
    "ขนาดพื้นที่ (ตร.ว.)",
    "ค่าเก็บขยะ",
    "ค่าส่วนกลาง",
    "หมายเลขโฉนด",
    "หมายเหตุ",
    "สถานะ"
];

// เขียน CSV
$output = fopen('php://output', 'w');
fputcsv($output, array_map(
    fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', is_null($item) ? '' : strval($item)),
    $header
));

// เขียนข้อมูลแต่ละแถว
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $line = [
        $row['house_number'],
        $row['alley'],
        $row['area_size'],
        $row['garbage_collection_fee'],
        $row['common_fee'],
        $row['land_no'],
        $row['remark'],
        ""
    ];

    fputcsv($output, array_map(
        fn($item) => iconv('UTF-8', 'TIS-620//IGNORE', is_null($item) ? '' : strval($item)),
        $line
    ));
}

fclose($output);
exit;