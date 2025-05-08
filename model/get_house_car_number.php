<?php

include '../config/connect_db.php';

// รับ userId จาก POST
$userId = $_POST['userId'] ?? '';

// ตรวจสอบและ query
if (!empty($userId)) {
    $stmt = $conn->prepare("SELECT house_number, f_name, l_name FROM ims_house_line_user WHERE line_user_id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode([
            'house_number' => $row['house_number'],
            'f_name'       => $row['f_name'],
            'l_name'       => $row['l_name']
        ]);
    } else {
        echo json_encode([
            'house_number' => '',
            'f_name'       => '',
            'l_name'       => ''
        ]);
    }
} else {
    echo json_encode([
        'house_number' => '',
        'f_name'       => '',
        'l_name'       => ''
    ]);
}