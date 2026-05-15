<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include('../config/connect_db.php');

if (strlen($_SESSION['alogin']) == "") {
    echo json_encode(['status' => 'error', 'message' => 'Session Timeout']);
    exit();
}

$meeting_year = $_POST['meeting_year'] ?? '';
$meeting_date = $_POST['meeting_date'] ?? '';

if (empty($meeting_year) || empty($meeting_date)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุปีและวันที่']);
    exit();
}

try {
    $conn->beginTransaction();

    // 1. ลบ check-in registration (ims_register_meeting)
    $stmt0 = $conn->prepare("DELETE FROM ims_register_meeting WHERE meeting_year = :y AND meeting_date = :d");
    $stmt0->bindParam(':y', $meeting_year);
    $stmt0->bindParam(':d', $meeting_date);
    $stmt0->execute();
    $del_register = $stmt0->rowCount();

    // 2. ลบ detail (ims_house_meeting)
    $stmt1 = $conn->prepare("DELETE FROM ims_house_meeting WHERE meeting_year = :y AND meeting_date = :d");
    $stmt1->bindParam(':y', $meeting_year);
    $stmt1->bindParam(':d', $meeting_date);
    $stmt1->execute();
    $del_house = $stmt1->rowCount();

    // 3. ลบ config (ims_meeting_config)
    $stmt2 = $conn->prepare("DELETE FROM ims_meeting_config WHERE meeting_year = :y AND meeting_date = :d");
    $stmt2->bindParam(':y', $meeting_year);
    $stmt2->bindParam(':d', $meeting_date);
    $stmt2->execute();
    $del_config = $stmt2->rowCount();

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => "ลบข้อมูลเรียบร้อย (register: $del_register, house: $del_house รายการ, config: $del_config รายการ)"
    ]);
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
}
