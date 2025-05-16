<?php
include '../config/connect_db.php';

// รับค่า userId จาก POST แบบปลอดภัย
$userId = $_POST['userId'] ?? '';

// เตรียม array สำหรับ response เริ่มต้น
$response = [
    'house_number' => '',
    'f_name'       => '',
    'l_name'       => '',
    'line_phone'   => '',
    'house_status' => '',
    'car_no1'      => '',
    'car_no2'      => ''
];

// ตรวจสอบว่า userId ไม่ว่าง และดำเนินการ query
if (!empty($userId)) {
    $sql_str = "
        SELECT 
            lhuser.house_number, 
            lhuser.f_name, 
            lhuser.l_name, 
            h.car_no1, 
            h.car_no2, 
            h.car_no3, 
            h.car_no4, 
            h.car_no5, 
            h.status, 
            h.house_status, 
            lhuser.line_phone
        FROM ims_house_line_user AS lhuser
        LEFT JOIN ims_house AS h 
            ON h.house_number = lhuser.house_number
        WHERE lhuser.line_user_id = ?
    ";

    $stmt = $conn->prepare($sql_str);
    $stmt->execute([$userId]);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // ใช้ array_merge เพื่อ override ค่า default เฉพาะที่ได้จาก DB
        $response = array_merge($response, [
            'house_number' => $row['house_number'] ?? '',
            'f_name'       => $row['f_name'] ?? '',
            'l_name'       => $row['l_name'] ?? '',
            'line_phone'   => $row['line_phone'] ?? '',
            'house_status' => $row['house_status'] ?? '',
            'car_no1'      => $row['car_no1'] ?? '',
            'car_no2'      => $row['car_no2'] ?? ''
        ]);
    }
}

// ส่งค่ากลับเป็น JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
