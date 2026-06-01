<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $house_number = trim($_POST['house_number'] ?? '');
    $house_status = trim($_POST['house_status'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    // Check sticker_receive_status before update
    $sql_check = "SELECT sticker_receive_status FROM ims_house WHERE house_number = :house_number";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([':house_number' => $house_number]);
    $row = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['sticker_receive_status'] === 'Y') {
        echo "รับสติกเกอร์ไปแล้ว"; // Indicate that stickers are already received
        exit();
    }
    
    $update_fields = [
        "house_status = :house_status",
        "phone_number = :phone_number",
        "update_datae = NOW()",
        "update_by = :update_by"
    ];
    
    $params = [
        ':house_status' => $house_status,
        ':phone_number' => $phone_number,
        ':house_number' => $house_number,
        ':update_by' => 'Resident (LIFF)'
    ];

    for ($i = 1; $i <= 8; $i++) {
        $car_no = trim($_POST['car_no' . $i] ?? '');
        $car_province = trim($_POST['car_no' . $i . '_province'] ?? '');
        $car_brand = trim($_POST['car_no' . $i . '_brand'] ?? '');
        $car_color = trim($_POST['car_no' . $i . '_color'] ?? '');
        $car_type = trim($_POST['car_no' . $i . '_type'] ?? '');

        $update_fields[] = "car_no$i = :car_no$i";
        $update_fields[] = "car_no{$i}_province = :car_no{$i}_province";
        $update_fields[] = "car_no{$i}_brand = :car_no{$i}_brand";
        $update_fields[] = "car_no{$i}_color = :car_no{$i}_color";
        $update_fields[] = "car_no{$i}_type = :car_no{$i}_type";

        $params[":car_no$i"] = $car_no;
        $params[":car_no{$i}_province"] = $car_province;
        $params[":car_no{$i}_brand"] = $car_brand;
        $params[":car_no{$i}_color"] = $car_color;
        $params[":car_no{$i}_type"] = $car_type;
    }

    $sql_update = "UPDATE ims_house SET " . implode(", ", $update_fields) . " WHERE house_number = :house_number";
    $stmt = $conn->prepare($sql_update);
    
    if ($stmt->execute($params)) {
        echo 1;
    } else {
        error_log("SQL Error: " . implode(", ", $stmt->errorInfo()));
        echo 0;
    }
}
