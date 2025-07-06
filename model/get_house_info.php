<?php
include '../config/connect_db.php';

header('Content-Type: application/json');

if (isset($_GET['house_number'])) {
    $house_number = trim($_GET['house_number']);

    $stmt = $conn->prepare("SELECT common_fee, area_size, contact_name FROM v_ims_house 
    WHERE house_number = :house_number LIMIT 1");
    $stmt->execute(['house_number' => $house_number]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode([
            'success' => true,
            'common_fee' => $data['common_fee'],
            'area_size' => $data['area_size'],
            'contact_name' => $data['contact_name']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่มีพารามิเตอร์']);
}

