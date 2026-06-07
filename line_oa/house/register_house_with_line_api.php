<?php

include('../../config/connect_db.php');
include('../../util/reorder_record.php');
header('Content-Type: application/json');

// รับค่าจาก POST
$lineUserId         = $_POST['lineUserId'] ?? '';
$lineUserName       = $_POST['name'] ?? '';
$linePhone          = $_POST['phone'] ?? '';
$linePictureProfile = $_POST['picture'] ?? '';
$lineStatusProfile  = $_POST['statusMessage'] ?? '';
$f_name             = $_POST['f_name'] ?? '';
$l_name             = $_POST['l_name'] ?? '';
$house_number = preg_replace('/\s+/', '', $_POST['house_number']);
$alley              = $_POST['alley'] ?? '';
$password_raw       = $_POST['password'] ?? 'default_password';
$action             = $_POST['action'] ?? 'register';

// ตรวจสอบว่าลงทะเบียนแล้วหรือยัง
if ($action === 'check') {
    if (!empty($lineUserId)) {
        $sql_check = "SELECT f_name, l_name, house_number, line_phone FROM ims_house_line_user WHERE line_user_id = :lineUserId";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([':lineUserId' => $lineUserId]);
        $userData = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            echo json_encode([
                "success" => true,
                "registered" => true,
                "user" => $userData
            ]);
        } else {
            echo json_encode([
                "success" => true,
                "registered" => false
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "ไม่พบ Line User ID"
        ]);
    }
    exit;
}

// Validate ข้อมูลที่จำเป็น
if (empty($lineUserId) || empty($linePhone)) {
    echo json_encode([
        "success" => false,
        "message" => "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน"
    ]);
    exit;
}

$password = password_hash($password_raw, PASSWORD_DEFAULT);
$role = "user";
$status = "Active";
$account_type = $role;
$picture = $account_type === 'admin' ? "img/icon/admin-001.png" : "img/icon/user-001.png";

$logFile = 'line_house_user_register_log.txt';

// ค้นหาผู้ใช้งานซ้ำ
try {
    $sql_find = "SELECT COUNT(*) FROM ims_house_line_user WHERE line_user_id = :lineUserId OR line_phone = :phone";
    $stmt_find = $conn->prepare($sql_find);
    $stmt_find->execute([':lineUserId' => $lineUserId, ':phone' => $linePhone]);

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

// ลงทะเบียนผู้ใช้ใหม่
try {
    // ตาราง ims_house_line_user
    $sql_insert = "INSERT INTO ims_house_line_user 
                    (line_user_id, line_user_name, line_phone, line_picture_profile, line_status_profile, f_name, l_name, house_number)
                    VALUES (:lineUserId, :lineUserName, :linePhone, :linePictureProfile, :lineStatusProfile, :f_name, :l_name, :house_number)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->execute([
        ':lineUserId' => $lineUserId,
        ':lineUserName' => $lineUserName,
        ':linePhone' => $linePhone,
        ':linePictureProfile' => $linePictureProfile,
        ':lineStatusProfile' => $lineStatusProfile,
        ':f_name' => $f_name,
        ':l_name' => $l_name,
        ':house_number' => $house_number
    ]);

    // ตาราง ims_user (ตรวจสอบก่อนว่ามีหรือยัง)
    $sql_check_ims_user = "SELECT COUNT(*) FROM ims_user WHERE user_id = :user_id";
    $stmt_check_ims_user = $conn->prepare($sql_check_ims_user);
    $stmt_check_ims_user->execute([':user_id' => $linePhone]);
    $userExists = $stmt_check_ims_user->fetchColumn();

    if ($userExists > 0) {
        // ถ้ามีอยู่แล้วให้ UPDATE (เพื่อให้รหัสผ่านใหม่และชื่อล่าสุดใช้งานได้)
        $sql_user = "UPDATE ims_user SET 
                        password = :password, 
                        first_name = :first_name, 
                        last_name = :last_name, 
                        status = :status
                     WHERE user_id = :user_id";
        $query_user = $conn->prepare($sql_user);
        $query_user->execute([
            ':password' => $password,
            ':first_name' => $f_name,
            ':last_name' => $l_name,
            ':status' => $status,
            ':user_id' => $linePhone
        ]);
    } else {
        // ถ้ายังไม่มีให้ INSERT
        $sql_user = "INSERT INTO ims_user(user_id, email, password, first_name, last_name, account_type, role, picture, status)
                     VALUES (:user_id, :email, :password, :first_name, :last_name, :account_type, :role, :picture, :status)";
        $query_user = $conn->prepare($sql_user);
        $query_user->execute([
            ':user_id' => $linePhone,
            ':email' => $linePhone,
            ':password' => $password,
            ':first_name' => $f_name,
            ':last_name' => $l_name,
            ':account_type' => $account_type,
            ':role' => $role,
            ':picture' => $picture,
            ':status' => $status
        ]);
    }

    Reorder_Record($conn, "ims_user");

    // ตรวจสอบว่ามีบ้านหรือยัง
    $sql_check_house = "SELECT COUNT(*) FROM ims_house WHERE house_number = :house_number";
    $stmt_check_house = $conn->prepare($sql_check_house);
    $stmt_check_house->execute([':house_number' => $house_number]);
    $houseExists = $stmt_check_house->fetchColumn();

    if ($houseExists <= 0) {
        // ถ้ายังไม่มีบ้าน เพิ่มเข้าไป
        $sql_house = "INSERT INTO ims_house(house_number, contact_name, phone_number, alley, remark)
                      VALUES (:house_number, :contact_name, :phone_number, :alley, :remark)";
        $query_house = $conn->prepare($sql_house);
        $query_house->execute([
            ':house_number' => $house_number,
            ':contact_name' => $f_name . ' ' . $l_name,
            ':phone_number' => $linePhone,
            ':alley' => $alley,
            ':remark' => ''
        ]);
    }

    $currentDateTime = date("Y-m-d H:i:s");
    $logData = "[SUCCESS] {$currentDateTime} | UserID: {$lineUserId} | Name: {$lineUserName}\n";
    file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);

    // ดึงข้อมูลที่เพิ่งลงทะเบียน
    $sql_get = "SELECT f_name, l_name, house_number, line_phone FROM ims_house_line_user WHERE line_user_id = :lineUserId";
    $stmt_get = $conn->prepare($sql_get);
    $stmt_get->execute([':lineUserId' => $lineUserId]);
    $userData = $stmt_get->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true, 
        "message" => "สมัครสมาชิกสำเร็จ",
        "user" => $userData
    ]);
} catch (PDOException $e) {
    $currentDateTime = date("Y-m-d H:i:s");
    $logData = "[ERROR-INSERT] {$currentDateTime} | Message: {$e->getMessage()} | UserID: {$lineUserId}\n";
    file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);

    echo json_encode([
        "success" => false,
        "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()
    ]);
}