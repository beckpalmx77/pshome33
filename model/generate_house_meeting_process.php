<?php
// model/generate_house_meeting_process.php
session_start();
header('Content-Type: application/json');
include('../config/connect_db.php');

if (strlen($_SESSION['alogin']) == "") {
    echo json_encode(['status' => 'error', 'message' => 'Session Timeout']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$meeting_year = isset($_POST['meeting_year']) ? $_POST['meeting_year'] : '';
$meeting_date = isset($_POST['meeting_date']) ? $_POST['meeting_date'] : '';
$meeting_name = isset($_POST['meeting_name']) ? $_POST['meeting_name'] : '';
$discount_value = isset($_POST['discount_value']) ? floatval($_POST['discount_value']) : 0;
$remark = isset($_POST['remark']) ? $_POST['remark'] : '';
$meeting_time = isset($_POST['meeting_time']) ? $_POST['meeting_time'] : '';
$meeting_location = isset($_POST['meeting_location']) ? $_POST['meeting_location'] : '';

if ($action == 'GENERATE_MEETING') {
    try {
        // 1. ดึงข้อมูลบ้านทั้งหมดจาก ims_house_master ที่ status = 'Y'
        $sql_master = "SELECT house_number FROM ims_house_master WHERE status = 'Y' ORDER BY house_number ASC";
        $stmt_master = $conn->prepare($sql_master);
        $stmt_master->execute();
        $houses = $stmt_master->fetchAll(PDO::FETCH_ASSOC);

        if (count($houses) == 0) {
            echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลบ้าน (Master) ที่มีสถานะ Active']);
            exit();
        }

        $success_count = 0;
        $skip_count = 0;
        $error_count = 0;

        // SQL Check Duplicate (บ้าน + ปี + วันที่)
        $sql_check = "SELECT COUNT(*) FROM ims_house_meeting 
                      WHERE house_number = :house_number 
                      AND meeting_year = :meeting_year
                      AND meeting_date = :meeting_date";
        $stmt_check = $conn->prepare($sql_check);

        // SQL Insert
        $sql_insert = "INSERT INTO ims_house_meeting 
                       (house_number, meeting_year, meeting_date, meeting_name, discount_value, remark, meeting_status, status) 
                       VALUES 
                       (:house_number, :meeting_year, :meeting_date, :meeting_name, :discount_value, :remark, 'N', 'Y')";
        $stmt_insert = $conn->prepare($sql_insert);

        // สร้าง default config ใน ims_meeting_config ถ้ายังไม่มี
        $sql_check_config = "SELECT COUNT(*) FROM ims_meeting_config WHERE meeting_year = :my AND meeting_date = :md";
        $stmt_cc = $conn->prepare($sql_check_config);
        $stmt_cc->bindParam(':my', $meeting_year);
        $stmt_cc->bindParam(':md', $meeting_date);
        $stmt_cc->execute();
        if ($stmt_cc->fetchColumn() == 0) {
            $sql_ins_config = "INSERT INTO ims_meeting_config (meeting_year, meeting_date, topic, meeting_time, meeting_location, updated_at)
                               VALUES (:my, :md, :topic, :time, :loc, NOW())";
            $stmt_ic = $conn->prepare($sql_ins_config);
            $stmt_ic->bindParam(':my', $meeting_year);
            $stmt_ic->bindParam(':md', $meeting_date);
            $stmt_ic->bindParam(':topic', $meeting_name);
            $stmt_ic->bindParam(':time', $meeting_time);
            $stmt_ic->bindParam(':loc', $meeting_location);
            $stmt_ic->execute();
        } else {
            $sql_up_cfg = "UPDATE ims_meeting_config SET topic = :topic, meeting_time = :time, meeting_location = :loc
                           WHERE meeting_year = :my AND meeting_date = :md AND (topic IS NULL OR topic = '')";
            $stmt_up = $conn->prepare($sql_up_cfg);
            $stmt_up->bindParam(':topic', $meeting_name);
            $stmt_up->bindParam(':time', $meeting_time);
            $stmt_up->bindParam(':loc', $meeting_location);
            $stmt_up->bindParam(':my', $meeting_year);
            $stmt_up->bindParam(':md', $meeting_date);
            $stmt_up->execute();
        }

        $conn->beginTransaction();

        foreach ($houses as $row) {
            $house_number = $row['house_number'];

            // Check
            $stmt_check->bindParam(':house_number', $house_number);
            $stmt_check->bindParam(':meeting_year', $meeting_year);
            $stmt_check->bindParam(':meeting_date', $meeting_date);
            $stmt_check->execute();

            if ($stmt_check->fetchColumn() > 0) {
                $skip_count++;
            } else {
                // Insert
                $stmt_insert->bindParam(':house_number', $house_number);
                $stmt_insert->bindParam(':meeting_year', $meeting_year);
                $stmt_insert->bindParam(':meeting_date', $meeting_date);
                $stmt_insert->bindParam(':meeting_name', $meeting_name);
                $stmt_insert->bindParam(':discount_value', $discount_value);
                $stmt_insert->bindParam(':remark', $remark);

                if ($stmt_insert->execute()) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }

        $conn->commit();

        $msg = "สำเร็จ: $success_count, ข้าม(มีแล้ว): $skip_count, ผิดพลาด: $error_count (จากทั้งหมด ".count($houses)." หลัง)";
        echo json_encode(['status' => 'success', 'message' => $msg]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
}