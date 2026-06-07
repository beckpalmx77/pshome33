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
    // 1. ตรวจสอบว่า Line User ID นี้ลงทะเบียนแล้วหรือยัง
    $sql_check_line = "SELECT COUNT(*) FROM ims_house_line_user WHERE line_user_id = :lineUserId";
    $stmt_check_line = $conn->prepare($sql_check_line);
    $stmt_check_line->execute([':lineUserId' => $lineUserId]);
    if ($stmt_check_line->fetchColumn() > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Line Account นี้ได้ลงทะเบียนในระบบเรียบร้อยแล้ว"
        ]);
        exit;
    }

    // 2. ตรวจสอบว่าเบอร์โทรศัพท์นี้มีในระบบแล้วหรือยัง
    $sql_check_phone = "SELECT * FROM ims_house_line_user WHERE line_phone = :phone";
    $stmt_check_phone = $conn->prepare($sql_check_phone);
    $stmt_check_phone->execute([':phone' => $linePhone]);
    $existingUser = $stmt_check_phone->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // กรณีพบเบอร์โทรศัพท์ซ้ำ -> ตรวจสอบรหัสผ่านเพื่อสลับบัญชี LINE
        $sql_verify = "SELECT password FROM ims_user WHERE user_id = :phone";
        $stmt_verify = $conn->prepare($sql_verify);
        $stmt_verify->execute([':phone' => $linePhone]);
        $userAuth = $stmt_verify->fetch(PDO::FETCH_ASSOC);

        if ($userAuth && password_verify($password_raw, $userAuth['password'])) {
            // รหัสผ่านถูกต้อง -> อัปเดตข้อมูล LINE ใหม่เข้าไปแทนที่
            $sql_update = "UPDATE ims_house_line_user SET 
                            line_user_id = :lineUserId,
                            line_user_name = :lineUserName,
                            line_picture_profile = :linePictureProfile,
                            line_status_profile = :lineStatusProfile,
                            f_name = :f_name,
                            l_name = :l_name,
                            house_number = :house_number
                          WHERE line_phone = :phone";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([
                ':lineUserId' => $lineUserId,
                ':lineUserName' => $lineUserName,
                ':linePictureProfile' => $linePictureProfile,
                ':lineStatusProfile' => $lineStatusProfile,
                ':f_name' => $f_name,
                ':l_name' => $l_name,
                ':house_number' => $house_number,
                ':phone' => $linePhone
            ]);

            // อัปเดตข้อมูลใน ims_user ด้วย (เผื่อมีการเปลี่ยนชื่อ-นามสกุล)
            $sql_update_user = "UPDATE ims_user SET 
                                first_name = :f_name,
                                last_name = :l_name
                                WHERE user_id = :phone";
            $stmt_update_user = $conn->prepare($sql_update_user);
            $stmt_update_user->execute([
                ':f_name' => $f_name,
                ':l_name' => $l_name,
                ':phone' => $linePhone
            ]);

            $currentDateTime = date("Y-m-d H:i:s");
            $logData = "[RE-LINK SUCCESS] {$currentDateTime} | Phone: {$linePhone} | New UserID: {$lineUserId}\n";
            file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);

            echo json_encode([
                "success" => true,
                "message" => "เชื่อมต่อบัญชี LINE ใหม่สำเร็จ",
                "user" => [
                    "f_name" => $f_name,
                    "l_name" => $l_name,
                    "house_number" => $house_number,
                    "line_phone" => $linePhone
                ]
            ]);
            exit;
        } else {
            echo json_encode([
                "success" => false,
                "message" => "เบอร์โทรศัพท์นี้ถูกใช้งานแล้ว หากคุณต้องการเปลี่ยนบัญชี LINE กรุณากรอกรหัสผ่านเดิมให้ถูกต้อง"
            ]);
            exit;
        }
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

    // ตาราง ims_user
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