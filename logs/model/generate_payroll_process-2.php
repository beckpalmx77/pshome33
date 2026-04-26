<?php
session_start();
error_reporting(0); // ปิดการแสดง error ใน Production environment

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');
include('../util/month_util.php'); // ต้องมีไฟล์นี้สำหรับ $month_arr

header('Content-Type: application/json');

// ตรวจสอบ action ที่ส่งมา (ใช้ $_POST สำหรับ actions ที่มาจาก Form Data เช่น GENERATE)
$action = $_POST["action"] ?? '';

// ======================================================================
// --- START: Action สำหรับ BATCH GENERATE (สร้างข้อมูลเงินเดือนอัตโนมัติ) ---
// ======================================================================
if ($action === 'GENERATE_PAYROLL') {
    $doc_date_raw = $_POST['doc_date'] ?? '';
    $payroll_month = (int)($_POST['payroll_month'] ?? 0);
    $payroll_year = (int)($_POST['payroll_year'] ?? 0);

    // ตรวจสอบความถูกต้องของข้อมูลพื้นฐาน
    if (empty($doc_date_raw) || $payroll_month === 0 || $payroll_year === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields (Doc Date, Month, Year).']);
        exit;
    }

    try {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 1. แปลงวันที่และเตรียม Prefix
        // ต้องตรวจสอบรูปแบบวันที่ก่อน
        $doc_date_db_format = $doc_date_raw;
        if (strpos($doc_date_raw, '/') !== false) {
            list($d, $m, $y) = explode('/', $doc_date_raw);
            $doc_date_db_format = $d . '-' . $m . '-' . $y; // บันทึกในรูปแบบ DD-MM-YYYY
        } else if (strpos($doc_date_raw, '-') !== false) {
            //Assume it's already in DD-MM-YYYY or similar if no slash
        }

        $month_str = str_pad($payroll_month, 2, '0', STR_PAD_LEFT);
        $doc_no_prefix = "PAY" . $payroll_year . $month_str;
        $inserted_count = 0;

        // 2. กำหนด Running Number (PAYYYYYMMXXXX)
        $sql_max_doc = "SELECT MAX(CAST(SUBSTRING(doc_no, 10) AS UNSIGNED)) AS max_running 
                        FROM ims_payroll 
                        WHERE doc_no LIKE ?";
        $stmt_max_doc = $conn->prepare($sql_max_doc);
        $stmt_max_doc->execute(["{$doc_no_prefix}%"]);
        $max_running_row = $stmt_max_doc->fetch(PDO::FETCH_ASSOC);
        $next_running = (int)$max_running_row['max_running'] + 1;

        // 3. ดึงข้อมูลพนักงานที่สถานะเป็น 'Y'
        $sql_emp = "SELECT emp_id, salary_type, salary FROM memployee WHERE status = 'Y'";
        $stmt_emp = $conn->prepare($sql_emp);
        $stmt_emp->execute();
        $employees = $stmt_emp->fetchAll(PDO::FETCH_ASSOC);

        // 4. Batch Insert
        $conn->beginTransaction();

        $sql_insert = "INSERT INTO ims_payroll 
                       (doc_no, doc_date, emp_id, payroll_month, payroll_year, work_day_month, total_amount, payment_method, bank_no, print_slip_status)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        foreach ($employees as $emp) {
            $emp_id = $emp['emp_id'];
            $salary = (float)$emp['salary'];

            // 4a. ตรวจสอบ Key ซ้ำ
            $sql_check = "SELECT COUNT(*) FROM ims_payroll 
                          WHERE emp_id = ? AND payroll_month = ? AND payroll_year = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->execute([$emp_id, $payroll_month, $payroll_year]);
            $count = $stmt_check->fetchColumn();

            if ($count == 0) {
                // 4b. สร้าง Doc No. และ Insert
                $doc_no = $doc_no_prefix . str_pad($next_running, 4, '0', STR_PAD_LEFT);
                $total_amount = $salary; // ใช้ salary เป็น total amount เริ่มต้น
                $work_day_month = 30.00; // ค่า Default

                $stmt_insert->execute([
                    $doc_no, $doc_date_db_format, $emp_id, $payroll_month, $payroll_year,
                    $work_day_month, $total_amount, '-', '-', 'N'
                ]);

                $inserted_count++;
                $next_running++;
            }
        }
        $conn->commit();

        // 5. ส่งผลลัพธ์ JSON
        if ($inserted_count > 0) {
            echo json_encode(['status' => 'success', 'message' => "สร้างข้อมูลเงินเดือนสำเร็จ {$inserted_count} รายการ สำหรับเดือน {$payroll_month} ปี {$payroll_year}!"]);
        } else {
            echo json_encode(['status' => 'info', 'message' => "ไม่มีรายการเงินเดือนที่ถูกสร้างใหม่ สำหรับเดือน {$payroll_month} ปี {$payroll_year} (อาจมีข้อมูลอยู่แล้ว)"]);
        }

    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Database error during batch generation: ' . $e->getMessage()]);
    }
    exit;
}
// --- END: Action สำหรับ BATCH GENERATE ---

// ======================================================================
// --- START: Action สำหรับ GET_DATA_BY_DOC_NO (ดึงข้อมูล Master) ---
// ======================================================================
if ($action === 'GET_DATA_BY_DOC_NO') {
    $doc_no = $_POST['doc_no'] ?? '';

    if (empty($doc_no)) {
        echo json_encode(['status' => 'error', 'message' => 'Doc No is required to retrieve data.']);
        exit;
    }

    try {
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


// ======================================================================
// --- START: Action สำหรับ ADD / UPDATE Payroll Master และ Detail (ใช้ JSON input) ---
// ======================================================================
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    // If JSON decode failed, check if it's a GET or POST without JSON (should be handled by $_POST actions above)
    // If no action matched above, and no JSON input, exit.
    if (!isset($_POST["action"])) {
        // Only exit if no valid action was processed above and no JSON input is detected
        // The original code has this line, which is fine for generic error handling if no action is provided at all.
        // echo json_encode(['status' => 'error', 'message' => 'Invalid input data or missing action.']);
        exit;
    }
}

// ถ้า $data มีค่า ให้ดำเนินการต่อในส่วน ADD/UPDATE
if (is_array($data)) {
    $action = $data['action'] ?? '';
    $doc_no = $data['doc_no'] ?? '';
    $doc_date = $data['doc_date'] ?? '';
    $emp_id = $data['emp_id'] ?? '';
    $payment_method = $data['payment_method'] ?? '';
    $bank_no = $data['bank_no'] ?? '';
    $payroll_month = $data['payroll_month'] ?? 0;
    $payroll_year = $data['payroll_year'] ?? 0;
    $work_day_month = $data['work_day_month'] ?? 0.00;
    $details = $data['details'] ?? [];
    $employee_fullname = $data['employee_fullname'] ?? '';
    $file_attach = $data['file_attach'] ?? null;
    $deleted_files_str = $data['deleted_files'] ?? '';


    if (!in_array($action, ['ADD', 'UPDATE'])) {
        // ถ้า action เป็น GENERATE_PAYROLL จะถูกดักที่ด้านบนแล้ว
        if (!empty($action)) {
            // only check if action is explicitly passed in JSON but is invalid for this block
            // the GENERATE_PAYROLL check prevents this from being executed when it should be POST
            if ($action !== 'GENERATE_PAYROLL') {
                echo json_encode(['status' => 'error', 'message' => 'Invalid action: ' . $action]);
                exit;
            }
        }
        // No action to proceed with the complex JSON logic, skip to end
        if (empty($action) && empty($doc_no) && empty($emp_id)) {
            exit;
        }
    }


    if (!$doc_date || !$emp_id || !$payroll_month || !$payroll_year) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required payroll header fields (Doc Date, Employee ID, Month, Year).']);
        exit;
    }

    $conn->beginTransaction();

    try {
        $total_amount_header = 0.00;
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
            $stmt_check_exist = $conn->prepare("SELECT COUNT(*) FROM ims_payroll WHERE emp_id = ? AND payroll_month = ? AND payroll_year = ?");
            $stmt_check_exist->execute([$emp_id, $payroll_month, $payroll_year]);
            $count = $stmt_check_exist->fetchColumn();

            if ($count > 0) {
                $conn->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'มีข้อมูลเงินเดือนของพนักงานรหัส ' . $emp_id . ' สำหรับเดือน ' . $payroll_month . ' ปี ' . $payroll_year . ' อยู่แล้วในระบบ']);
                exit;
            }

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

            $stmt = $conn->prepare("INSERT INTO ims_payroll (doc_no, doc_date, emp_id, payroll_month, payroll_year, work_day_month, total_amount, payment_method, bank_no, file_attach) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt->execute([$doc_no, $doc_date, $emp_id, $payroll_month, $payroll_year, $work_day_month, $total_amount_header, $payment_method, $bank_no, $file_attach])) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Insert master failed: " . $errorInfo[2]);
            }
            $success_message = "บันทึกข้อมูลเงินเดือนใหม่สำเร็จ.";

        } else { // UPDATE
            if (empty($doc_no)) {
                throw new Exception("Document number (doc_no) is required for UPDATE action.");
            }
            $stmt = $conn->prepare("UPDATE ims_payroll SET doc_date = ?, emp_id = ?, payroll_month = ?, payroll_year = ?, work_day_month = ?, total_amount = ?, payment_method = ?, bank_no = ?, file_attach = ? WHERE doc_no = ?");
            if (!$stmt->execute([$doc_date, $emp_id, $payroll_month, $payroll_year, $work_day_month, $total_amount_header, $payment_method, $bank_no, $file_attach, $doc_no])) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Update master failed: " . $errorInfo[2]);
            }

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
                throw new Exception("Missing or invalid payroll detail fields.");
            }
            $detail_total_amount = (float)$item['quantity'] * (float)$item['amount_per_unit'];
            if (!$stmtDetail->execute([
                $doc_no, $doc_date, $emp_id, $payroll_month, $payroll_year,
                $item['icd_type_id'], $item['remark'], (float)$item['quantity'],
                (float)$item['amount_per_unit'], $item['icd_type_sign'], $detail_total_amount
            ])) {
                $errorInfo = $stmtDetail->errorInfo();
                throw new Exception("Insert payroll detail failed: " . $errorInfo[2]);
            }
        }

        /* === START: for insert / update ims_expenses === */
        $month_name = $month_arr[$payroll_month] ?? 'N/A';
        $expense_date = $doc_date;
        $expense_doc_no = $doc_no;
        // การแปลงเดือน/ปี สำหรับ ims_expenses ควรใช้ doc_date เป็นหลัก
        $exp_month = str_pad($payroll_month, 2, '0', STR_PAD_LEFT);
        $exp_year = $payroll_year;
        // พยายามดึงเดือน/ปีจาก doc_date ถ้าเป็นไปได้
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $doc_date, $matches)) {
            $exp_month = $matches[2];
            $exp_year = $matches[3];
        }

        $description = "เงินเดือน/ค่าจ้าง " . $employee_fullname . " เดือน " . $month_name . " " . ($payroll_year + 543);
        $category_id = "P8";
        $unit_id = "U001";
        $total_expense_amount = $total_amount_header;

        $stmt_check_expense = $conn->prepare("SELECT id, doc_id FROM ims_expenses WHERE doc_ref = ?");
        $stmt_check_expense->execute([$expense_doc_no]);
        $existing_expense = $stmt_check_expense->fetch(PDO::FETCH_ASSOC);

        if ($existing_expense) {
            $expense_id = $existing_expense['id'];
            $stmt_update_expense = $conn->prepare("UPDATE ims_expenses SET receipt_name = ?, expense_date = ?, exp_month = ?, exp_year = ?, description = ?, category_id = ?, qty = 1, amount = ?, price_per_unit = ?, total_amount = ?, payment_method = ?, bank_no = ?, unit_id=? WHERE id = ?");
            if (!$stmt_update_expense->execute([$employee_fullname, $expense_date, $exp_month, $exp_year, $description, $category_id, $total_expense_amount, $total_expense_amount, $total_expense_amount, $payment_method, $bank_no, $unit_id, $expense_id])) {
                $errorInfo = $stmt_update_expense->errorInfo();
                throw new Exception("Update ims_expenses failed: " . $errorInfo[2]);
            }
        } else {
            // Logic for calculating new expense doc_id
            $exp_year_for_doc_id = $exp_year;
            $exp_month_for_doc_id = $exp_month;

            $stmt_expense_global_runno = $conn->prepare("SELECT MAX(runno) AS last_runno FROM ims_expenses WHERE exp_year = ?");
            $stmt_expense_global_runno->execute([$exp_year]);
            $last_expense_global_runno = $stmt_expense_global_runno->fetchColumn();
            $next_expense_global_runno = ($last_expense_global_runno ? $last_expense_global_runno + 1 : 1);

            $stmt_max_exp_doc_id = $conn->prepare("SELECT MAX(CAST(SUBSTRING(doc_id, 13) AS UNSIGNED)) FROM ims_expenses WHERE exp_year = ? AND exp_month = ? AND doc_id LIKE 'EXP-%'");
            $stmt_max_exp_doc_id->execute([$exp_year_for_doc_id, $exp_month_for_doc_id]);
            $last_exp_doc_id_sequence = $stmt_max_exp_doc_id->fetchColumn();
            $next_expense_monthly_sequence = ($last_exp_doc_id_sequence ? $last_exp_doc_id_sequence + 1 : 1);
            $exp_doc_id = "EXP-" . $exp_year_for_doc_id . "-" . sprintf("%02d", $exp_month_for_doc_id) . "-" . sprintf("%04d", $next_expense_monthly_sequence);

            $stmt_insert_expense = $conn->prepare("INSERT INTO ims_expenses (doc_id, doc_ref, receipt_name, expense_date, exp_month, exp_year, description, category_id, qty, amount, price_per_unit, total_amount, payment_method, bank_no, unit_id, runno) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt_insert_expense->execute([$exp_doc_id, $expense_doc_no, $employee_fullname, $expense_date, $exp_month, $exp_year, $description, $category_id, $total_expense_amount, $total_expense_amount, $total_expense_amount, $payment_method, $bank_no, $unit_id, $next_expense_global_runno])) {
                $errorInfo = $stmt_insert_expense->errorInfo();
                throw new Exception("Insert ims_expenses failed: " . $errorInfo[2]);
            }
        }
        /* === END: for insert / update ims_expenses === */

        $conn->commit();

        // Handle physical file deletion AFTER successful DB commit
        if (!empty($deleted_files_str)) {
            $files_to_delete = explode(',', $deleted_files_str);
            foreach ($files_to_delete as $filename) {
                $filename = trim($filename);
                // Basic security checks
                if (!empty($filename) && strpos($filename, '..') === false) {
                    $filePath = '../uploads/payroll/' . basename($filename);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
        }

        echo json_encode(['status' => 'success', 'message' => $success_message, 'doc_no' => $doc_no]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
// --- END: Action สำหรับ ADD / UPDATE Payroll Master และ Detail ---
?>