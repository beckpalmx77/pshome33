<?php

include('../../config/connect_db.php');

$lineUserId         = $_POST['lineUserId'] ?? '';
$lineUserName       = $_POST['name'] ?? '';
$linePhone          = $_POST['phone'] ?? '';
$linePictureProfile = $_POST['picture'] ?? '';
$lineStatusProfile  = $_POST['statusMessage'] ?? '';
$f_name             = $_POST['f_name'] ?? '';
$l_name             = $_POST['l_name'] ?? '';
$house_number       = $_POST['house_number'] ?? '';

$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = "user";
$status = "Y";
$picture = $account_type == 'admin' ? "img/icon/admin-001.png" : "img/icon/user-001.png";


$logFile = 'line_house_user_register_log.txt'; // หรือกำหนด path แบบเต็ม เช่น '../../logs/line_error_log.txt'

if (empty($lineUserId) || empty($linePhone)) {
    echo json_encode([
        "success" => false,
        "message" => "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน"
    ]);
    exit;
}

try {
    if (!empty($lineUserId)) {
        $sql_find = "SELECT COUNT(*) FROM ims_house_line_user WHERE line_user_id = :lineUserId or line_phone = :phone";
        $stmt_find = $conn->prepare($sql_find);
        $stmt_find->execute([':lineUserId' => $lineUserId, ':phone' => $linePhone]);
    } else {
        $sql_find = "SELECT COUNT(*) FROM ims_house_line_user WHERE line_user_id = :lineUserId";
        $stmt_find = $conn->prepare($sql_find);
        $stmt_find->execute([':lineUserId' => $lineUserId]);
    }

    $nRows = $stmt_find->fetchColumn();

    if ($nRows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Line Account - มี User นี้ อยู่ในระบบแล้ว"
        ]);
        exit;
    }
} catch (PDOException $e) {
    $currentDateTime = date("Y-m-d H:i:s");
    $logData = "[ERROR-FIND] {$currentDateTime} | Message: {$e->getMessage()} | UserID: {$lineUserId}\n";
    file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);

    echo json_encode([
        "success" => false,
        "message" => "เกิดข้อผิดพลาดในการตรวจสอบข้อมูล: " . $e->getMessage()
    ]);
    exit;
}

try {
    $sql_insert = "INSERT INTO ims_house_line_user 
                    (line_user_id, line_user_name, line_phone, line_picture_profile, line_status_profile, f_name, l_name,house_number)
                    VALUES (:lineUserId, :lineUserName, :linePhone, :linePictureProfile, :lineStatusProfile, :f_name, :l_name, :house_number)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bindParam(':lineUserId', $lineUserId, PDO::PARAM_STR);
    $stmt_insert->bindParam(':lineUserName', $lineUserName, PDO::PARAM_STR);
    $stmt_insert->bindParam(':linePhone', $linePhone, PDO::PARAM_STR);
    $stmt_insert->bindParam(':linePictureProfile', $linePictureProfile, PDO::PARAM_STR);
    $stmt_insert->bindParam(':lineStatusProfile', $lineStatusProfile, PDO::PARAM_STR);
    $stmt_insert->bindParam(':f_name', $f_name, PDO::PARAM_STR);
    $stmt_insert->bindParam(':l_name', $l_name, PDO::PARAM_STR);
    $stmt_insert->bindParam(':house_number', $house_number, PDO::PARAM_STR);
    $stmt_insert->execute();

    $sql = "INSERT INTO ims_user(user_id,email,password,first_name,last_name,account_type,role,picture,status)
            VALUES (:user_id,:email,:password,:first_name,:last_name,:account_type,:role,:picture,:status)";
    $query = $conn->prepare($sql);
    $query->bindParam(':user_id', $linePhone, PDO::PARAM_STR);
    $query->bindParam(':email', $linePhone, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->bindParam(':first_name', $f_name, PDO::PARAM_STR);
    $query->bindParam(':last_name', $l_name, PDO::PARAM_STR);
    $query->bindParam(':account_type', $role, PDO::PARAM_STR);
    $query->bindParam(':role', $role, PDO::PARAM_STR);
    $query->bindParam(':picture', $picture, PDO::PARAM_STR);
    $query->bindParam(':status', $status, PDO::PARAM_STR);
    $query->execute();

    $sql_find = "SELECT * FROM ims_house WHERE house_number = '" . $house_number . "'";
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows <= 0) {
        $sql_house = "INSERT INTO ims_house(house_number,contact_name,phone_number,alley,remark) 
                    VALUES (:house_number,:contact_name,:phone_number,:alley,:remark)";
        $query_house = $conn->prepare($sql_house);
        $query_house->bindParam(':house_number', $house_number, PDO::PARAM_STR);
        $query_house->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
        $query_house->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
        $query_house->bindParam(':alley', $alley, PDO::PARAM_STR);
        $query_house->bindParam(':remark', $remark, PDO::PARAM_STR);
        $query_house->execute();
    }


    $lastInsertId = $conn->lastInsertId();
    if ($lastInsertId) {
        $currentDateTime = date("Y-m-d H:i:s");
        $logData = "[SUCCESS] {$currentDateTime} | UserID: {$lineUserId} | Name: {$lineUserName}\n";
        file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);

        echo json_encode(["success" => true, "message" => "สมัครสมาชิกสำเร็จ"]);
    } else {
        echo json_encode(["success" => false, "message" => "ไม่สามารถบันทึกข้อมูลได้"]);
    }
} catch (PDOException $e) {
    $currentDateTime = date("Y-m-d H:i:s");
    $logData = "[ERROR-INSERT] {$currentDateTime} | Message: {$e->getMessage()} | UserID: {$lineUserId}\n";
    file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);

    echo json_encode([
        "success" => false,
        "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()
    ]);
}

