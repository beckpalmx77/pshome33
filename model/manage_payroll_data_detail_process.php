<?php
session_start();
error_reporting(0); // ปิดการแสดง error ใน Production environment

include('../config/connect_db.php');
include('../config/lang.php'); // น่าจะใช้สำหรับข้อความภาษาต่างๆ
include('../util/record_util.php'); // อาจจะจำเป็นสำหรับฟังก์ชันบางอย่าง (ถ้ามี)
include('../util/reorder_record.php'); // อาจจะจำเป็นสำหรับฟังก์ชันบางอย่าง (ถ้ามี)
include('../util/month_util.php'); // ต้องมีไฟล์นี้สำหรับ $month_arr

header('Content-Type: application/json');

// ตรวจสอบ action ที่ส่งมา
$action = $_POST["action"] ?? ($data['action'] ?? ''); // รองรับทั้ง POST ปกติและ JSON POST

// --- START: Action สำหรับ GET_DATA (ดึงข้อมูล Master โดย ID) ---
// (ส่วนนี้มาจาก snippet ที่คุณเคยให้มาใน manage_payroll_process.php)
if ($action === 'GET_DATA') {
    $id = $_POST["id"] ?? 0;

    try {
        $stmt = $conn->prepare("SELECT * FROM v_ims_payroll WHERE id = ?");
        $stmt->execute([$id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $return_arr = [];
        foreach ($results as $result) {
            $return_arr[] = array(
                "id" => $result['id'],
                "doc_no" => $result['doc_no'],
                "doc_date" => $result['doc_date'],
                "emp_id" => $result['emp_id'],
                "f_name" => $result['f_name'],
                "l_name" => $result['l_name'],
                "payroll_month" => $result['payroll_month'],
                "payroll_year" => $result['payroll_year'],
                "salary_type" => $result['salary_type'],
                "salary" => $result['salary'],
                // เพิ่มข้อมูลอื่นๆ ที่ต้องการส่งกลับ
            );
        }
        echo json_encode($return_arr);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
// --- END: Action สำหรับ GET_DATA ---

// --- START: Action สำหรับ GET_DATA_BY_DOC_NO (ดึงข้อมูล Payroll Master สำหรับการแก้ไข) ---
if ($action === 'GET_DATA_BY_DOC_NO') {
    $doc_no = $_POST['doc_no'] ?? ''; // รับ doc_no จาก AJAX POST

    if (empty($doc_no)) {
        echo json_encode(['status' => 'error', 'message' => 'Doc No is required to retrieve data.']);
        exit;
    }

    try {
        // เพิ่ม payment_method และ bank_no ในการ SELECT
        $stmt_payroll = $conn->prepare("SELECT ip.*, CONCAT(ie.f_name, ' ', ie.l_name) AS emp_fullname, ie.salary_type, ie.salary
                                FROM ims_payroll ip
                                LEFT JOIN memployee ie ON ip.emp_id = ie.emp_id
                                WHERE ip.doc_no = :doc_no");
        $stmt_payroll->bindParam(':doc_no', $doc_no);
        $stmt_payroll->execute();
        $payroll_master = $stmt_payroll->fetch(PDO::FETCH_ASSOC);

        if ($payroll_master) {
            echo json_encode(['status' => 'success', 'data' => $payroll_master]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No payroll data found for Doc No: ' . $doc_no]);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
// --- END: Action สำหรับ GET_DATA_BY_DOC_NO ---


// --- START: Action สำหรับ ADD / UPDATE Payroll Master และ Detail ---
// รับข้อมูล JSON จาก POST (สำหรับ ADD/UPDATE)
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

$action = $data['action'] ?? ''; // ต้องดึง action จาก $data อีกครั้ง เนื่องจากมีการ decode json
$doc_no = $data['doc_no'] ?? '';
$doc_date = $data['doc_date'] ?? '';
$emp_id = $data['emp_id'] ?? '';
$payment_method = $data['payment_method'] ?? '';
$bank_no = $data['bank_no'] ?? '';
$payroll_month = $data['payroll_month'] ?? 0;
$payroll_year = $data['payroll_year'] ?? 0;
$work_day_month = $data['work_day_month'] ?? 0.00;
$details = $data['details'] ?? [];
$employee_fullname = $data['employee_fullname'] ?? ''; // เพิ่มตัวแปรนี้เพื่อส่งไป ims_expenses

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
    $total_amount_header = 0.00;
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

    $success_message = '';

    if ($action === 'ADD') {
        // Check for existing emp_id, payroll_month, payroll_year
        $stmt_check_exist = $conn->prepare("SELECT COUNT(*) FROM ims_payroll WHERE emp_id = ? AND payroll_month = ? AND payroll_year = ?");
        $stmt_check_exist->execute([$emp_id, $payroll_month, $payroll_year]);
        $count = $stmt_check_exist->fetchColumn();

        if ($count > 0) {
            echo json_encode(['status' => 'error', 'message' => 'มีข้อมูลเงินเดือนของพนักงานรหัส ' . $emp_id . ' สำหรับเดือน ' . $payroll_month . ' ปี ' . $payroll_year . ' อยู่แล้วในระบบ']);
            exit;
        }

        // Generate new doc_no if adding
        $stmt_last_doc = $conn->prepare("SELECT MAX(doc_no) AS last_doc_no FROM ims_payroll WHERE doc_no LIKE ?");
        $prefix = "PAY" . $payroll_year . sprintf("%02d", $payroll_month);
        $stmt_last_doc->execute([$prefix . '%']);
        $last_doc_no = $stmt_last_doc->fetchColumn();

        if ($last_doc_no) {
            $num = (int)substr($last_doc_no, -4) + 1;
            $doc_no = $prefix . sprintf("%04d", $num);
        } else {
            $doc_no = $prefix . "0001";
        }

        $stmt = $conn->prepare("INSERT INTO ims_payroll (doc_no, doc_date, emp_id, payroll_month, payroll_year, work_day_month, total_amount, payment_method, bank_no) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt->execute([$doc_no, $doc_date, $emp_id, $payroll_month, $payroll_year, $work_day_month, $total_amount_header, $payment_method, $bank_no])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Insert master failed: " . $errorInfo[2]);
        }
        $success_message = "บันทึกข้อมูลเงินเดือนใหม่สำเร็จ.";
    } else { // UPDATE
        if (empty($doc_no)) {
            throw new Exception("Document number (doc_no) is required for UPDATE action.");
        }
        $stmt = $conn->prepare("UPDATE ims_payroll SET doc_date = ?, emp_id = ?, payroll_month = ?, payroll_year = ?, work_day_month = ?, total_amount = ?, payment_method = ?, bank_no = ? WHERE doc_no = ?");
        if (!$stmt->execute([$doc_date, $emp_id, $payroll_month, $payroll_year, $work_day_month, $total_amount_header, $payment_method, $bank_no, $doc_no])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Update master failed: " . $errorInfo[2]);
        }

        // Delete existing details before inserting new ones for UPDATE
        $stmtDelete = $conn->prepare("DELETE FROM ims_payroll_detail WHERE doc_no = ?");
        if (!$stmtDelete->execute([$doc_no])) {
            $errorInfo = $stmtDelete->errorInfo();
            throw new Exception("Delete details failed: " . $errorInfo[2]);
        }
        $success_message = "อัปเดตข้อมูลเงินเดือนสำเร็จ.";
    }

    // Insert payroll details
    $stmtDetail = $conn->prepare("INSERT INTO ims_payroll_detail
        (doc_no, doc_date, emp_id, payroll_month, payroll_year, icd_type_id, remark, quantity, amount_per_unit, icd_type_sign, total_amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($details as $item) {
        if (empty($item['icd_type_id']) || !isset($item['quantity']) || !is_numeric($item['quantity']) || !isset($item['amount_per_unit']) || !is_numeric($item['amount_per_unit']) || empty($item['icd_type_sign'])) {
            throw new Exception("Missing or invalid payroll detail fields for item: ICD Type ID, Quantity, Amount Per Unit, or ICD Type Sign.");
        }

        $detail_total_amount = (float)$item['quantity'] * (float)$item['amount_per_unit'];

        if (!$stmtDetail->execute([
            $doc_no,
            $doc_date,
            $emp_id,
            $payroll_month,
            $payroll_year,
            $item['icd_type_id'],
            $item['remark'],
            (float)$item['quantity'],
            (float)$item['amount_per_unit'],
            $item['icd_type_sign'],
            $detail_total_amount
        ])) {
            $errorInfo = $stmtDetail->errorInfo();
            throw new Exception("Insert payroll detail failed: " . $errorInfo[2]);
        }
    }

    /* === START: for insert / update ims_expenses === */
    $month_name = $month_arr[$payroll_month]; // ต้องแน่ใจว่า $month_arr ถูก include และมีข้อมูล
    $expense_date = $doc_date;
    $expense_doc_no = $doc_no; // ใช้ doc_no ของ payroll เป็น reference
    $exp_month = date('m', strtotime($doc_date));
    $exp_year = date('Y', strtotime($doc_date));
    $description = "เงินเดือน/ค่าจ้าง " . $employee_fullname . " เดือน " . $month_name . " " . ($payroll_year + 543);
    $category_id = "P8"; // สมมติว่า "P8" คือ Category ID สำหรับเงินเดือน
    $unit_id = "U001";
    $total_expense_amount = $total_amount_header; // ใช้ยอดรวมจาก payroll header

    // Prepare variables for doc_id generation
    $exp_year_for_doc_id = date('Y', strtotime($expense_date));
    $exp_month_for_doc_id = date('m', strtotime($expense_date));

    // Check if an expense record already exists for this payroll doc_no
    // *** สำคัญ: ควรมีคอลัมน์ doc_ref ในตาราง ims_expenses เพื่อใช้อ้างอิงถึง doc_no ของ Payroll ***
    $stmt_check_expense = $conn->prepare("SELECT id, doc_id FROM ims_expenses WHERE doc_ref = ? ");
    $stmt_check_expense->execute([$expense_doc_no]);
    $existing_expense = $stmt_check_expense->fetch(PDO::FETCH_ASSOC);

    if ($existing_expense) {
        // UPDATE existing expense record
        $expense_id = $existing_expense['id'];
        $exp_doc_id = $existing_expense['doc_id']; // Keep the existing doc_id for update

        $stmt_update_expense = $conn->prepare("
            UPDATE ims_expenses SET
                receipt_name = ?,
                expense_date = ?,
                exp_month = ?,
                exp_year = ?,
                description = ?,
                category_id = ?,
                qty = 1,
                amount = ?,
                price_per_unit = ?,
                total_amount = ?,
                payment_method = ?,
                bank_no = ?,
                unit_id=?
                
            WHERE id = ?
        ");

        if (!$stmt_update_expense->execute([
            $employee_fullname, // ใช้ employee_fullname เป็น receipt_name
            $expense_date,
            $exp_month,
            $exp_year,
            $description,
            $category_id,
            $total_expense_amount,
            $total_expense_amount, // price_per_unit as total_amount (assuming 1 quantity)
            $total_expense_amount,
            $payment_method,
            $bank_no,
            $unit_id,
            $expense_id
        ])) {
            $errorInfo = $stmt_update_expense->errorInfo();
            throw new Exception("Update ims_expenses failed: " . $errorInfo[2]);
        }
    } else {
        // INSERT new expense record

        // Fetch next general runno for ims_expenses (annual scope, used for the 'runno' column)
        $stmt_expense_global_runno = $conn->prepare("SELECT MAX(runno) AS last_runno FROM ims_expenses WHERE exp_year = ?");
        $stmt_expense_global_runno->execute([date('Y', strtotime($doc_date))]);
        $last_expense_global_runno = $stmt_expense_global_runno->fetchColumn();
        $next_expense_global_runno = ($last_expense_global_runno ? $last_expense_global_runno : 0);
        $next_expense_global_runno++;

        // Generate new doc_id for ims_expenses
        $stmt_max_exp_doc_id = $conn->prepare("SELECT MAX(CAST(SUBSTRING(doc_id, 13) AS UNSIGNED)) FROM ims_expenses WHERE exp_year = ? AND exp_month = ? AND doc_id LIKE 'EXP-%'");
        $stmt_max_exp_doc_id->execute([$exp_year_for_doc_id, $exp_month_for_doc_id]);
        $last_exp_doc_id_sequence = $stmt_max_exp_doc_id->fetchColumn();

        $next_expense_monthly_sequence = ($last_exp_doc_id_sequence ? $last_exp_doc_id_sequence + 1 : 1);
        $exp_doc_id = "EXP-" . $exp_year_for_doc_id . "-" . sprintf("%02d", $exp_month_for_doc_id) . "-" . sprintf("%04d", $next_expense_monthly_sequence);

        $stmt_insert_expense = $conn->prepare("
            INSERT INTO ims_expenses (
                doc_id, doc_ref, receipt_name, expense_date, exp_month, exp_year,
                description, category_id, qty, amount, price_per_unit, total_amount,
                payment_method, bank_no, unit_id, runno 
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?
            )
        ");

        if (!$stmt_insert_expense->execute([
            $exp_doc_id,
            $expense_doc_no, // Reference to payroll doc_no
            $employee_fullname, // ใช้ employee_fullname เป็น receipt_name
            $expense_date,
            $exp_month,
            $exp_year,
            $description,
            $category_id,
            $total_expense_amount,
            $total_expense_amount, // price_per_unit as total_amount (assuming 1 quantity)
            $total_expense_amount,
            $payment_method,
            $bank_no,
            $unit_id,
            $next_expense_global_runno
        ])) {
            $errorInfo = $stmt_insert_expense->errorInfo();
            throw new Exception("Insert ims_expenses failed: " . $errorInfo[2]);
        }
    }
    /* === END: for insert / update ims_expenses === */


    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => $success_message, 'doc_no' => $doc_no]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>