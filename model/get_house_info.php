<?php
include '../config/connect_db.php';

header('Content-Type: application/json');

if (isset($_GET['house_number'])) {
    $house_number = trim($_GET['house_number']);

    // ลองค้นหาใน v_ims_house ก่อน
    $stmt = $conn->prepare("SELECT common_fee, area_size, contact_name FROM v_ims_house 
    WHERE house_number = :house_number LIMIT 1");
    $stmt->execute(['house_number' => $house_number]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // หากพบข้อมูลใน v_ims_house
        echo json_encode([
            'success' => true,
            'common_fee' => $data['common_fee'],
            'area_size' => $data['area_size'],
            'contact_name' => $data['contact_name']
        ]);
    } else {
        // หากไม่พบข้อมูลใน v_ims_house ให้ค้นหาใน ims_house_master
        $stmt_master = $conn->prepare("SELECT common_fee, area_size, remark FROM ims_house_master 
        WHERE house_number = :house_number LIMIT 1");
        $stmt_master->execute(['house_number' => $house_number]);
        $data_master = $stmt_master->fetch(PDO::FETCH_ASSOC);

        if ($data_master) {
            // หากพบข้อมูลใน ims_house_master
            echo json_encode([
                'success' => true,
                'common_fee' => $data_master['common_fee'],
                'area_size' => $data_master['area_size'],
                'contact_name' => $data_master['remark']
            ]);
        } else {
            // หากไม่พบข้อมูลทั้งสองตาราง
            echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่มีพารามิเตอร์']);
}
?>