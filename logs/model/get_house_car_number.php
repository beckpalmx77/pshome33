<?php

include '../config/connect_db.php';

// รับ userId จาก POST
$userId = $_POST['userId'] ?? '';

// ตรวจสอบและ query
if (!empty($userId)) {
    /*
        $sql_str = "SELECT lhuser.house_number, lhuser.f_name, lhuser.l_name, h.car_no1, h.car_no2, h.car_no3, h.car_no4, h.car_no5, h.status , h.house_status , h.phone_number
                    FROM ims_house_line_user AS lhuser
                    LEFT JOIN ims_house AS h ON h.house_number = lhuser.house_number
                    WHERE lhuser.line_user_id = ?";
    */

    $sql_str = " SELECT 
    lhuser.house_number, 
    lhuser.f_name, 
    lhuser.l_name, 
    h.car_no1, h.car_no2, h.car_no3, h.car_no4, h.car_no5, h.car_no6, h.car_no7, 
    h.car_no1_province, h.car_no2_province, h.car_no3_province, h.car_no4_province, h.car_no5_province, h.car_no6_province, h.car_no7_province,
    h.car_no1_brand, h.car_no2_brand, h.car_no3_brand, h.car_no4_brand, h.car_no5_brand, h.car_no6_brand, h.car_no7_brand,
    h.car_no1_color, h.car_no2_color, h.car_no3_color, h.car_no4_color, h.car_no5_color, h.car_no6_color, h.car_no7_color,
    h.car_no1_type, h.car_no2_type, h.car_no3_type, h.car_no4_type, h.car_no5_type, h.car_no6_type, h.car_no7_type,
    h.status, 
    h.house_status, 
    h.phone_number,
    h.sticker_receive_status,
    h.sticker_receive_date,
    m_house_master.area_size,
    m_house_master.garbage_collection_fee,
    m_house_master.common_fee
    FROM ims_house_line_user AS lhuser
    LEFT JOIN ims_house AS h ON h.house_number = lhuser.house_number
    LEFT JOIN ims_house_master AS m_house_master ON m_house_master.house_number = lhuser.house_number
    WHERE lhuser.line_user_id = ?";

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
            'l_name' => $row['l_name'],
            'phone_number' => $row['phone_number'],
            'house_status' => $row['house_status'],
            'sticker_receive_status' => $row['sticker_receive_status'] ?? '',
            'sticker_receive_date' => $row['sticker_receive_date'] ?? '',
            'car_no1' => $row['car_no1'],
            'car_no1_province' => $row['car_no1_province'],
            'car_no1_brand' => $row['car_no1_brand'],
            'car_no1_color' => $row['car_no1_color'],
            'car_no1_type' => $row['car_no1_type'],
            'car_no2' => $row['car_no2'],
            'car_no2_province' => $row['car_no2_province'],
            'car_no2_brand' => $row['car_no2_brand'],
            'car_no2_color' => $row['car_no2_color'],
            'car_no2_type' => $row['car_no2_type'],
            'car_no3' => $row['car_no3'],
            'car_no3_province' => $row['car_no3_province'],
            'car_no3_brand' => $row['car_no3_brand'],
            'car_no3_color' => $row['car_no3_color'],
            'car_no3_type' => $row['car_no3_type'],
            'car_no4' => $row['car_no4'],
            'car_no4_province' => $row['car_no4_province'],
            'car_no4_brand' => $row['car_no4_brand'],
            'car_no4_color' => $row['car_no4_color'],
            'car_no4_type' => $row['car_no4_type'],
            'car_no5' => $row['car_no5'],
            'car_no5_province' => $row['car_no5_province'],
            'car_no5_brand' => $row['car_no5_brand'],
            'car_no5_color' => $row['car_no5_color'],
            'car_no5_type' => $row['car_no5_type'],
            'car_no6' => $row['car_no6'] ?? '',
            'car_no6_province' => $row['car_no6_province'] ?? '',
            'car_no6_brand' => $row['car_no6_brand'] ?? '',
            'car_no6_color' => $row['car_no6_color'] ?? '',
            'car_no6_type' => $row['car_no6_type'] ?? '',
            'car_no7' => $row['car_no7'] ?? '',
            'car_no7_province' => $row['car_no7_province'] ?? '',
            'car_no7_brand' => $row['car_no7_brand'] ?? '',
            'car_no7_color' => $row['car_no7_color'] ?? '',
            'car_no7_type' => $row['car_no7_type'] ?? ''

        ]);
    } else {
        echo json_encode([
            'house_number' => '',
            'area_size' => '',
            'garbage_collection_fee' => '',
            'common_fee' => '',
            'f_name' => '',
            'l_name' => '',
            'phone_number' => '',
            'house_status' => '',
            'car_no1' => '',
            'car_no1_province' => '',
            'car_no1_brand' => '',
            'car_no1_color' => '',
            'car_no1_type' => '',
            'car_no2' => '',
            'car_no2_province' => '',
            'car_no2_brand' => '',
            'car_no2_color' => '',
            'car_no2_type' => '',
            'car_no3' => '',
            'car_no3_province' => '',
            'car_no3_brand' => '',
            'car_no3_color' => '',
            'car_no3_type' => '',
            'car_no4' => '',
            'car_no4_province' => '',
            'car_no4_brand' => '',
            'car_no4_color' => '',
            'car_no4_type' => '',
            'car_no5' => '',
            'car_no5_province' => '',
            'car_no5_brand' => '',
            'car_no5_color' => '',
            'car_no5_type' => '',
            'car_no6' => '',
            'car_no6_province' => '',
            'car_no6_brand' => '',
            'car_no6_color' => '',
            'car_no6_type' => '',
            'car_no7' => '',
            'car_no7_province' => '',
            'car_no7_brand' => '',
            'car_no7_color' => '',
            'car_no7_type' => ''
        ]);
    }
} else {
    echo json_encode([
        'house_number' => '',
        'area_size' => '',
        'garbage_collection_fee' => '',
        'common_fee' => '',
        'f_name' => '',
        'l_name' => '',
        'phone_number' => '',
        'house_status' => '',
            'sticker_receive_status' => '',
            'sticker_receive_date' => '',
            'car_no1' => '',
        'car_no1_province' => '',
        'car_no1_brand' => '',
        'car_no1_color' => '',
        'car_no1_type' => '',
        'car_no2' => '',
        'car_no2_province' => '',
        'car_no2_brand' => '',
        'car_no2_color' => '',
        'car_no2_type' => '',
        'car_no3' => '',
        'car_no3_province' => '',
        'car_no3_brand' => '',
        'car_no3_color' => '',
        'car_no3_type' => '',
        'car_no4' => '',
        'car_no4_province' => '',
        'car_no4_brand' => '',
        'car_no4_color' => '',
        'car_no4_type' => '',
        'car_no5' => '',
        'car_no5_province' => '',
        'car_no5_brand' => '',
        'car_no5_color' => '',
        'car_no5_type' => '',
        'car_no6' => '',
        'car_no6_province' => '',
        'car_no6_brand' => '',
        'car_no6_color' => '',
        'car_no6_type' => '',
        'car_no7' => '',
        'car_no7_province' => '',
        'car_no7_brand' => '',
        'car_no7_color' => '',
        'car_no7_type' => ''
    ]);
}