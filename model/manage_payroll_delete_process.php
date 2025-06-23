<?php
header('Content-Type: application/json');
include('../config/connect_db.php'); // ตรวจสอบเส้นทางให้ถูกต้อง

$data = $_POST; // รับข้อมูลจาก AJAX POST

if (!isset($data['action']) || $data['action'] !== 'DELETE') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action or missing action parameter.']);
    exit;
}

if (!isset($data['id']) || !is_numeric($data['id'])) { // ตรวจสอบว่าเป็นตัวเลข
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid record ID (id) for deletion.']);
    exit;
}

$record_id = $data['id']; // id ที่ส่งมาคือ id (Primary Key) ของ ims_payroll

$conn->beginTransaction(); // เริ่มต้น transaction

try {
    // 1. ค้นหา doc_no จาก ims_payroll โดยใช้ record_id (id ของตาราง ims_payroll)
    $stmt_fetch_doc_no = $conn->prepare("SELECT doc_no FROM ims_payroll WHERE id = ?");
    $stmt_fetch_doc_no->execute([$record_id]);
    $result = $stmt_fetch_doc_no->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        throw new Exception("Payroll record not found for ID: " . $record_id);
    }

    $doc_no = $result['doc_no'];

    // 2. ลบข้อมูลจากตาราง ims_payroll_detail ก่อน โดยใช้ doc_no ที่ได้มา
    $stmt_detail = $conn->prepare("DELETE FROM ims_payroll_detail WHERE doc_no = ?");
    if (!$stmt_detail->execute([$doc_no])) {
        $errorInfo = $stmt_detail->errorInfo();
        throw new Exception("Failed to delete payroll details for doc_no " . $doc_no . ": " . $errorInfo[2]);
    }

    // 3. ลบข้อมูลจากตาราง ims_payroll โดยใช้ record_id (id ของตาราง ims_payroll)
    $stmt_master = $conn->prepare("DELETE FROM ims_payroll WHERE id = ?");
    if (!$stmt_master->execute([$record_id])) {
        $errorInfo = $stmt_master->errorInfo();
        throw new Exception("Failed to delete payroll master record for ID " . $record_id . ": " . $errorInfo[2]);
    }

    $conn->commit(); // Commit transaction หากสำเร็จทั้งหมด
    echo json_encode(['status' => 'success', 'message' => 'Payroll record and its details deleted successfully.']);

} catch (Exception $e) {
    $conn->rollBack(); // Rollback transaction หากเกิดข้อผิดพลาด
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>