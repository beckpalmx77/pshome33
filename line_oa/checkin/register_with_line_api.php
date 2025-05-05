<?php

include('../../config/connect_db.php');

$lineUserId         = $_POST['lineUserId'] ?? '';
$lineUserName       = $_POST['name'] ?? '';
$linePhone          = $_POST['phone'] ?? '';
$linePictureProfile = $_POST['picture'] ?? '';
$lineStatusProfile  = $_POST['statusMessage'] ?? '';
$emp_id             = $_POST['emp_id'] ?? '';
$emp_name           = $_POST['emp_name'] ?? '';

$logFile = 'line_emp_user_register_log.txt'; // หรือกำหนด path แบบเต็ม เช่น '../../logs/line_error_log.txt'

if (empty($lineUserId) || empty($linePhone)) {
    echo json_encode([
        "success" => false,
        "message" => "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน"
    ]);
    exit;
}

try {
    if (!empty($emp_id)) {
        $sql_find = "SELECT COUNT(*) FROM ims_employee_line_user WHERE line_user_id = :lineUserId OR emp_id = :emp_id";
        $stmt_find = $conn->prepare($sql_find);
        $stmt_find->execute([':lineUserId' => $lineUserId, ':emp_id' => $emp_id]);
    } else {
        $sql_find = "SELECT COUNT(*) FROM ims_employee_line_user WHERE line_user_id = :lineUserId";
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
    $sql_insert = "INSERT INTO ims_employee_line_user 
                    (line_user_id, line_user_name, line_phone, line_picture_profile, line_status_profile, emp_id, emp_name)
                    VALUES (:lineUserId, :lineUserName, :linePhone, :linePictureProfile, :lineStatusProfile, :emp_id, :emp_name)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bindParam(':lineUserId', $lineUserId, PDO::PARAM_STR);
    $stmt_insert->bindParam(':lineUserName', $lineUserName, PDO::PARAM_STR);
    $stmt_insert->bindParam(':linePhone', $linePhone, PDO::PARAM_STR);
    $stmt_insert->bindParam(':linePictureProfile', $linePictureProfile, PDO::PARAM_STR);
    $stmt_insert->bindParam(':lineStatusProfile', $lineStatusProfile, PDO::PARAM_STR);
    $stmt_insert->bindParam(':emp_id', $emp_id, PDO::PARAM_STR);
    $stmt_insert->bindParam(':emp_name', $emp_name, PDO::PARAM_STR);
    $stmt_insert->execute();

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

