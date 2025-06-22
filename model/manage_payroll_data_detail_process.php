<?php
header('Content-Type: application/json');
include('../config/connect_db.php');

// รับข้อมูล JSON จาก POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

$action = $data['action'] ?? '';
$doc_no = $data['doc_no'] ?? '';
$doc_date = $data['doc_date'] ?? ''; // Changed from 'date' to 'doc_date' for payroll
$emp_id = $data['emp_id'] ?? ''; // Changed from 'requester' to 'emp_id' for payroll
$payroll_month = $data['payroll_month'] ?? 0; // Added for payroll
$payroll_year = $data['payroll_year'] ?? 0; // Added for payroll
$work_day_month = $data['work_day_month'] ?? 0.00; // Added for payroll
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
    $total_amount = 0.00;
    // Calculate total_amount from details for server-side accuracy
    foreach ($details as $item) {
        $quantity = (float)($item['quantity'] ?? 0);
        $amount_per_unit = (float)($item['amount_per_unit'] ?? 0); // Changed from 'price' to 'amount_per_unit'
        $icd_type_sign = $item['icd_type_sign'] ?? ''; // Added for calculation

        if ($icd_type_sign === '+') {
            $total_amount += ($quantity * $amount_per_unit);
        } elseif ($icd_type_sign === '-') {
            $total_amount -= ($quantity * $amount_per_unit);
        }
    }

    if ($action === 'ADD') {
        // *** START: Added check for existing emp_id, payroll_month, payroll_year ***
        $stmt_check_exist = $conn->prepare("SELECT COUNT(*) FROM ims_payroll WHERE emp_id = ? AND payroll_month = ? AND payroll_year = ?");
        $stmt_check_exist->execute([$emp_id, $payroll_month, $payroll_year]);
        $count = $stmt_check_exist->fetchColumn();

        if ($count > 0) {
            echo json_encode(['status' => 'error', 'message' => 'มีข้อมูลเงินเดือนของพนักงานรหัส ' . $emp_id . ' สำหรับเดือน ' . $payroll_month . ' ปี ' . $payroll_year . ' อยู่แล้วในระบบ']);
            exit; // Exit if data already exists
        }
        // *** END: Added check ***

        // Generate new doc_no if adding
        $stmt_last_doc = $conn->prepare("SELECT MAX(doc_no) AS last_doc_no FROM ims_payroll WHERE doc_no LIKE ?");
        $prefix = "PAY" . date("Ym"); // Example: PAY202506
        $stmt_last_doc->execute([$prefix . '%']);
        $last_doc_no = $stmt_last_doc->fetchColumn();

        if ($last_doc_no) {
            $num = (int)substr($last_doc_no, -4) + 1;
            $doc_no = $prefix . sprintf("%04d", $num);
        } else {
            $doc_no = $prefix . "0001";
        }

        $stmt = $conn->prepare("INSERT INTO ims_payroll (doc_no, doc_date, emp_id, payroll_month, payroll_year, work_day_month, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt->execute([$doc_no, $doc_date, $emp_id, $payroll_month, $payroll_year, $work_day_month, $total_amount])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Insert master failed: " . $errorInfo[2]);
        }
    } else { // UPDATE
        if (empty($doc_no)) {
            throw new Exception("Document number (doc_no) is required for UPDATE action.");
        }
        $stmt = $conn->prepare("UPDATE ims_payroll SET doc_date = ?, emp_id = ?, payroll_month = ?, payroll_year = ?, work_day_month = ?, total_amount = ? WHERE doc_no = ?");
        if (!$stmt->execute([$doc_date, $emp_id, $payroll_month, $payroll_year, $work_day_month, $total_amount, $doc_no])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Update master failed: " . $errorInfo[2]);
        }

        // Delete existing details before inserting new ones for UPDATE
        $stmtDelete = $conn->prepare("DELETE FROM ims_payroll_detail WHERE doc_id = ?"); // doc_id is the foreign key
        if (!$stmtDelete->execute([$doc_no])) {
            $errorInfo = $stmtDelete->errorInfo();
            throw new Exception("Delete details failed: " . $errorInfo[2]);
        }
    }

    // Insert payroll details
    $stmtDetail = $conn->prepare("INSERT INTO ims_payroll_detail
        (doc_id, doc_date, emp_id, payroll_month, payroll_year, icd_type_id, quantity, amount_per_unit, icd_type_sign)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"); // Added doc_date, emp_id, payroll_month, payroll_year, icd_type_sign
    $line_no = 1; // ims_payroll_detail does not have line_no in the SQL, but if it did, it would be used here. Assuming it is not needed.

    foreach ($details as $item) {
        // Validation for detail items
        if (empty($item['icd_type_id']) || !isset($item['quantity']) || !is_numeric($item['quantity']) || !isset($item['amount_per_unit']) || !is_numeric($item['amount_per_unit']) || empty($item['icd_type_sign'])) {
            throw new Exception("Missing or invalid payroll detail fields for item: ICD Type ID, Quantity, Amount Per Unit, or ICD Type Sign.");
        }

        if (!$stmtDetail->execute([
            $doc_no,
            $doc_date, // Added
            $emp_id, // Added
            $payroll_month, // Added
            $payroll_year, // Added
            $item['icd_type_id'],
            (float)$item['quantity'],
            (float)$item['amount_per_unit'],
            $item['icd_type_sign'] // Added
        ])) {
            $errorInfo = $stmtDetail->errorInfo();
            throw new Exception("Insert payroll detail failed: " . $errorInfo[2]);
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'doc_no' => $doc_no]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>