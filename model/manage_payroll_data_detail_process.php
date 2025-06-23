<?php
header('Content-Type: application/json');
include('../config/connect_db.php'); // ตรวจสอบให้แน่ใจว่าไฟล์นี้ตั้งค่า PDO::ERRMODE_EXCEPTION ไว้ด้วย

// รับข้อมูล JSON จาก POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

$action = $data['action'] ?? '';
$doc_no = $data['doc_no'] ?? '';
$doc_date = $data['doc_date'] ?? '';
$emp_id = $data['emp_id'] ?? '';
$payroll_month = $data['payroll_month'] ?? 0;
$payroll_year = $data['payroll_year'] ?? 0;
$work_day_month = $data['work_day_month'] ?? 0.00;
$details = $data['details'] ?? [];

if (!in_array($action, ['ADD', 'UPDATE'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing action']);
    exit;
}

if (!$doc_date || !$emp_id || !$payroll_month || !$payroll_year) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required payroll header fields (Doc Date, Employee ID, Month, Year).']);
    exit;
}

$conn->beginTransaction();

try {
    $total_amount_header = 0.00; // เปลี่ยนชื่อตัวแปรเพื่อให้ชัดเจนว่าเป็น total ของ header
    // Calculate total_amount from details for server-side accuracy
    foreach ($details as $item) {
        $quantity = (float)($item['quantity'] ?? 0);
        $amount_per_unit = (float)($item['amount_per_unit'] ?? 0);
        $icd_type_sign = $item['icd_type_sign'] ?? '';

        if ($icd_type_sign === '+') {
            $total_amount_header += ($quantity * $amount_per_unit);
        } elseif ($icd_type_sign === '-') {
            $total_amount_header -= ($quantity * $amount_per_unit);
        }
    }

    $success_message = ''; // กำหนดตัวแปรสำหรับข้อความสำเร็จ

    if ($action === 'ADD') {
        // *** START: Added check for existing emp_id, payroll_month, payroll_year ***
        $stmt_check_exist = $conn->prepare("SELECT COUNT(*) FROM ims_payroll WHERE emp_id = ? AND payroll_month = ? AND payroll_year = ?");
        $stmt_check_exist->execute([$emp_id, $payroll_month, $payroll_year]);
        $count = $stmt_check_exist->fetchColumn();

        if ($count > 0) {
            echo json_encode(['status' => 'error', 'message' => 'มีข้อมูลเงินเดือนของพนักงานรหัส ' . $emp_id . ' สำหรับเดือน ' . $payroll_month . ' ปี ' . $payroll_year . ' อยู่แล้วในระบบ']);
            exit;
        }
        // *** END: Added check ***

        // Generate new doc_no if adding
        $stmt_last_doc = $conn->prepare("SELECT MAX(doc_no) AS last_doc_no FROM ims_payroll WHERE doc_no LIKE ?");
        // *** START: MODIFIED HERE to use payroll_year and payroll_month ***
        $prefix = "PAY" . $payroll_year . sprintf("%02d", $payroll_month); // Example: PAY202506
        // *** END: MODIFIED HERE ***
        $stmt_last_doc->execute([$prefix . '%']);
        $last_doc_no = $stmt_last_doc->fetchColumn();

        if ($last_doc_no) {
            $num = (int)substr($last_doc_no, -4) + 1;
            $doc_no = $prefix . sprintf("%04d", $num);
        } else {
            $doc_no = $prefix . "0001";
        }

        $stmt = $conn->prepare("INSERT INTO ims_payroll (doc_no, doc_date, emp_id, payroll_month, payroll_year, work_day_month, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
        // หากต้องการให้ catch error ได้ดีขึ้น ควรตั้งค่า PDO::ERRMODE_EXCEPTION ใน connect_db.php
        if (!$stmt->execute([$doc_no, $doc_date, $emp_id, $payroll_month, $payroll_year, $work_day_month, $total_amount_header])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Insert master failed: " . $errorInfo[2]);
        }
        $success_message = "บันทึกข้อมูลเงินเดือนใหม่สำเร็จ."; // กำหนดข้อความสำหรับ ADD
    } else { // UPDATE
        if (empty($doc_no)) {
            throw new Exception("Document number (doc_no) is required for UPDATE action.");
        }
        $stmt = $conn->prepare("UPDATE ims_payroll SET doc_date = ?, emp_id = ?, payroll_month = ?, payroll_year = ?, work_day_month = ?, total_amount = ? WHERE doc_no = ?");
        // หากต้องการให้ catch error ได้ดีขึ้น ควรตั้งค่า PDO::ERRMODE_EXCEPTION ใน connect_db.php
        if (!$stmt->execute([$doc_date, $emp_id, $payroll_month, $payroll_year, $work_day_month, $total_amount_header, $doc_no])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Update master failed: " . $errorInfo[2]);
        }

        // Delete existing details before inserting new ones for UPDATE
        $stmtDelete = $conn->prepare("DELETE FROM ims_payroll_detail WHERE doc_no = ?");
        // หากต้องการให้ catch error ได้ดีขึ้น ควรตั้งค่า PDO::ERRMODE_EXCEPTION ใน connect_db.php
        if (!$stmtDelete->execute([$doc_no])) {
            $errorInfo = $stmtDelete->errorInfo();
            throw new Exception("Delete details failed: " . $errorInfo[2]);
        }
        $success_message = "อัปเดตข้อมูลเงินเดือนสำเร็จ."; // กำหนดข้อความสำหรับ UPDATE
    }

    // Insert payroll details
    // *** MODIFIED: Added 'total_amount' column to the INSERT statement ***
    // ตรวจสอบให้แน่ใจว่าโครงสร้างตาราง ims_payroll_detail รองรับคอลัมน์เหล่านี้
    $stmtDetail = $conn->prepare("INSERT INTO ims_payroll_detail
        (doc_no, doc_date, emp_id, payroll_month, payroll_year, icd_type_id, quantity, amount_per_unit, icd_type_sign, total_amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $line_no = 1; // ตัวแปรนี้ไม่ได้ถูกใช้ในปัจจุบัน สามารถลบออกได้

    foreach ($details as $item) {
        // Validation for detail items
        if (empty($item['icd_type_id']) || !isset($item['quantity']) || !is_numeric($item['quantity']) || !isset($item['amount_per_unit']) || !is_numeric($item['amount_per_unit']) || empty($item['icd_type_sign'])) {
            throw new Exception("Missing or invalid payroll detail fields for item: ICD Type ID, Quantity, Amount Per Unit, or ICD Type Sign.");
        }

        // *** ADDED: Calculate total_amount for the current detail row ***
        $detail_total_amount = (float)$item['quantity'] * (float)$item['amount_per_unit'];

        // หากต้องการให้ catch error ได้ดีขึ้น ควรตั้งค่า PDO::ERRMODE_EXCEPTION ใน connect_db.php
        if (!$stmtDetail->execute([
            $doc_no,
            $doc_date,
            $emp_id,
            $payroll_month,
            $payroll_year,
            $item['icd_type_id'],
            (float)$item['quantity'],
            (float)$item['amount_per_unit'],
            $item['icd_type_sign'],
            $detail_total_amount // *** ADDED: Pass the calculated total_amount ***
        ])) {
            $errorInfo = $stmtDetail->errorInfo();
            throw new Exception("Insert payroll detail failed: " . $errorInfo[2]);
        }
    }

    $conn->commit();
    // เพิ่ม 'message' เข้าไปใน JSON response สำหรับกรณีสำเร็จ
    echo json_encode(['status' => 'success', 'message' => $success_message, 'doc_no' => $doc_no]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>