<?php

include '../config/connect_db.php';

// รับ userId จาก POST
$userId = $_POST['userId'] ?? '';

// ตรวจสอบและ query
if (!empty($userId)) {
    $sql_str = "SELECT lhuser.house_number, lhuser.f_name, lhuser.l_name, h.car_no1, h.car_no2, h.car_no3, h.car_no4, h.car_no5, h.status , h.house_status
                FROM ims_house_line_user AS lhuser
                LEFT JOIN ims_house AS h ON h.house_number = lhuser.house_number
                WHERE lhuser.line_user_id = ?";
    $stmt = $conn->prepare($sql_str);
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode([
            'house_number' => $row['house_number'],
            'f_name'       => $row['f_name'],
            'l_name'       => $row['l_name'],
            'house_status' => $row['house_status'],
            'car_no1'      => $row['car_no1'],
            'car_no2'      => $row['car_no2']
        ]);
    } else {
        echo json_encode([
            'house_number' => '',
            'f_name'       => '',
            'l_name'       => '',
            'house_status' => '',
            'car_no1'       => '',
            'car_no2'       => ''
        ]);
    }
} else {
    echo json_encode([
        'house_number' => '',
        'f_name'       => '',
        'l_name'       => '',
        'house_status' => '',
        'car_no1'       => '',
        'car_no2'       => ''
    ]);
}