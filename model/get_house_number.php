<?php

include '../config/connect_db.php';

// รับ userId จาก POST
$userId = $_POST['userId'] ?? '';

// ตรวจสอบและ query
if (!empty($userId)) {

    $sql_str = " SELECT 
        ims_house_line_user.house_number,
        ims_house_line_user.f_name,
        ims_house_line_user.l_name,
        ims_house_master.area_size,
        ims_house_master.garbage_collection_fee,
        ims_house_master.common_fee
    FROM ims_house_line_user
    LEFT JOIN ims_house_master 
        ON ims_house_master.house_number = ims_house_line_user.house_number
    WHERE ims_house_line_user.line_user_id = ?";

    $stmt = $conn->prepare($sql_str);
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode([
            'house_number' => $row['house_number'],
            'area_size' => $row['area_size'],
            'garbage_collection_fee' => $row['garbage_collection_fee'],
            'common_fee' => $row['common_fee'],
            'f_name' => $row['f_name'],
            'l_name' => $row['l_name']
        ]);
    } else {
        echo json_encode([
            'house_number' => '',
            'area_size' => '',
            'garbage_collection_fee' => '',
            'common_fee' => '',
            'f_name' => '',
            'l_name' => ''
        ]);
    }
} else {
    echo json_encode([
        'house_number' => '',
        'area_size' => '',
        'garbage_collection_fee' => '',
        'common_fee' => '',
        'f_name' => '',
        'l_name' => ''
    ]);
}