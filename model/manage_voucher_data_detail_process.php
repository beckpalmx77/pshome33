<?php
header('Content-Type: application/json');
include('../config/connect_db.php');
include('../util/gl_util.php');

// รับข้อมูล JSON จาก POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

$action = $data['action'] ?? '';
// ... (การรับค่าตัวแปรอื่นๆ เหมือนเดิม) ...
$doc_no = $data['doc_no'] ?? '';
$doc_date = $data['date'] ?? '';
$requester = $data['requester'] ?? '';
$supplier_id = $data['supplier_id'] ?? '';
$supplier_name = $data['supplier_name'] ?? '';
$address = $data['address'] ?? '-';
$supplier_phone = $data['supplier_phone'] ?? '-';
$purpose = $data['purpose'] ?? '';
$details = $data['details'] ?? [];
$picture_doc = $data['picture_doc'] ?? '';
$payment_method = $data['payment_method'] ?? '';
$create_name = $data['create_name'] ?? '';
$checker_name = $data['checker_name'] ?? '';
$approve_name = $data['approve_name'] ?? '';
$approve_status = $data['approve_status'] ?? 'N';
$petty_cash_status = $data['petty_cash_status'] ?? 'N';
$bank_account = $data['bank_account'] ?? '';

// Default values
$receipt_name = ($supplier_name === null || $supplier_name === '') ? '-' : $supplier_name;
$picture_doc = $picture_doc === null ? '' : $picture_doc;
$payment_method = $payment_method === null ? '-' : $payment_method;

// Validations
if (!in_array($action, ['ADD', 'UPDATE'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing action']);
    exit;
}
if (!$doc_date || !$supplier_name || empty($details)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required header or detail fields']);
    exit;
}

try {
    $conn->beginTransaction();

    // 1. Supplier Management
    $stmtCheckSupplier = $conn->prepare("SELECT supplier_id FROM ims_supplier WHERE supplier_name = ?");
    $stmtCheckSupplier->execute([$supplier_name]);
    $existingSupplier = $stmtCheckSupplier->fetch(PDO::FETCH_ASSOC);

    if ($existingSupplier) {
        $supplier_id = $existingSupplier['supplier_id'];
        $stmtUpdateSupplier = $conn->prepare("UPDATE ims_supplier SET address = ?, phone = ? WHERE supplier_id = ?");
        $stmtUpdateSupplier->execute([$address, $supplier_phone, $supplier_id]);
    } else {
        $stmtGetLastSupplierId = $conn->prepare("SELECT MAX(supplier_id) AS last_supplier_id FROM ims_supplier WHERE supplier_id LIKE 'S%'");
        $stmtGetLastSupplierId->execute();
        $last_supplier_id = $stmtGetLastSupplierId->fetchColumn();

        $next_seq = 1;
        if ($last_supplier_id) {
            $next_seq = (int)substr($last_supplier_id, 1) + 1;
        }
        $new_supplier_id = 'S' . sprintf('%05d', $next_seq);

        $stmtInsertSupplier = $conn->prepare("INSERT INTO ims_supplier (supplier_id, supplier_name, address, phone, status) VALUES (?, ?, ?, ?, 'Active')");
        $stmtInsertSupplier->execute([$new_supplier_id, $supplier_name, $address, $supplier_phone]);
        $supplier_id = $new_supplier_id;
    }

    // 2. Document Number Generation (ADD)
    if ($action === 'ADD') {
        $stmt_pv_runno = $conn->prepare("SELECT MAX(doc_runno) AS last_runno FROM ims_payment_voucher WHERE doc_year = ?");
        $stmt_pv_runno->execute([date('Y', strtotime($doc_date))]);
        $last_pv_runno = $stmt_pv_runno->fetchColumn();
        $next_pv_runno = ($last_pv_runno ? $last_pv_runno : 0) + 1;
        $doc_no = "PV-" . date('Y', strtotime($doc_date)). date('m', strtotime($doc_date)) . "-" . sprintf('%04d', $next_pv_runno);
    }

    // 3. Prepare Header Insert/Update Statements
    // แก้ไข: ย้ายการ Insert Header ของ ADD มาทำก่อน เพื่อป้องกัน FK Error
    if ($action === 'ADD') {
        $stmtInsertHeader = $conn->prepare("INSERT INTO ims_payment_voucher (doc_no, doc_date, doc_month, doc_year, doc_runno, requester, supplier_id, supplier_name, purpose, payment_method, bank_no, total_amount, picture_doc, create_name, checker_name, receipt_name, approve_name, approve_status, status, address, petty_cash_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Insert Header ก่อนด้วยยอดเงิน 0 (จะ Update ทีหลัง)
        $stmtInsertHeader->execute([
            $doc_no, $doc_date, date('m', strtotime($doc_date)), date('Y', strtotime($doc_date)),
            $next_pv_runno, $requester, $supplier_id, $supplier_name, $purpose,
            $payment_method, $bank_account, 0, $picture_doc, $create_name,
            $checker_name, $receipt_name, $approve_name, 'N', 'Active', $address, $petty_cash_status
        ]);

    } else { // UPDATE
        // ลบ Items เดิมทิ้งก่อน (เฉพาะ Items)
        $stmtDeleteDetailPV = $conn->prepare("DELETE FROM ims_payment_voucher_items WHERE doc_no = ?");
        $stmtDeleteDetailPV->execute([$doc_no]);

        // !!! แก้ไขจุดสำคัญ: ห้ามลบ ims_expenses ทั้งหมดตรงนี้ เพราะจะทำให้ Logic การ Update expense ด้านล่างพัง !!!
        // เราจะใช้ Logic Orphan delete ตอนท้ายแทน
    }

    // 4. Prepare Statements for Loop
    $stmtDetailPV = $conn->prepare("INSERT INTO ims_payment_voucher_items (doc_no, doc_date, line_no, product_id, product_name, inv, quantity, price, unit_id, unit_name, remark) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Product & Category Statements
    $stmtCheckProductByName = $conn->prepare("SELECT product_id, product_name, pgroup_id, unit_id FROM ims_products WHERE product_name = ?");
    $stmtInsertProduct = $conn->prepare("INSERT INTO ims_products (product_id, product_name, pgroup_id, unit_id, status) VALUES (?, ?, ?, ?, 'Active')");
    $stmtUpdateProduct = $conn->prepare("UPDATE ims_products SET pgroup_id = ?, unit_id = ? WHERE product_id = ?"); // แก้ไข Query ให้กระชับ
    $stmtGetLastProductId = $conn->prepare("SELECT MAX(product_id) FROM ims_products WHERE product_id LIKE 'P%'");

    // Category Statements
    $stmtCheckCategory = $conn->prepare("SELECT category_name FROM ims_category WHERE category_id = ?");
    $stmtInsertCategory = $conn->prepare("INSERT INTO ims_category (category_id, category_name, status) VALUES (?, ?, 'Active')");

    // Expense Statements
    $stmtInsertExpense = $conn->prepare("INSERT INTO ims_expenses (runno, doc_id, doc_ref, receipt_name, expense_date, exp_month, exp_year, inv, category_id, description, qty, unit_id, amount, remark, approve_status, file_attach, payment_method, price_per_unit, petty_cash_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtUpdateExpense = $conn->prepare("UPDATE ims_expenses SET doc_id=?, receipt_name=?, expense_date=?, exp_month=?, exp_year=?, inv=?, category_id=?, description=?, qty=?, unit_id=?, amount=?, remark=?, approve_status=?, file_attach=?, payment_method=?, price_per_unit=?, petty_cash_status=? WHERE id=?");

    // 5. Setup Variables for Loop
    $total_amount_header = 0;
    $line_no = 1;

    // เตรียมข้อมูล Expense เดิมสำหรับเคส UPDATE
    $existing_expenses_map = [];
    $expense_ids_to_keep = []; // เก็บ ID ที่ถูกใช้งาน เพื่อลบตัวที่เหลือทิ้ง

    if ($action === 'UPDATE') {
        $stmtFetchExpenses = $conn->prepare("SELECT id, inv, doc_id FROM ims_expenses WHERE doc_ref = ?");
        $stmtFetchExpenses->execute([$doc_no]);
        $temp_expenses = $stmtFetchExpenses->fetchAll(PDO::FETCH_ASSOC);
        foreach ($temp_expenses as $exp) {
            // ใช้ inv เป็น Key ในการ Map (ถ้า inv ซ้ำ อาจต้อง Logic เพิ่มเติม แต่เบื้องต้นตามโค้ดเดิม)
            if (!isset($existing_expenses_map[$exp['inv']])) {
                $existing_expenses_map[$exp['inv']] = [];
            }
            $existing_expenses_map[$exp['inv']][] = $exp;
        }
    }

    // Runno setup for Expenses
    $stmt_expense_runno = $conn->prepare("SELECT MAX(runno) FROM ims_expenses WHERE exp_year = ?");
    $stmt_expense_runno->execute([date('Y', strtotime($doc_date))]);
    $next_expense_global_runno = (int)$stmt_expense_runno->fetchColumn();

    $stmt_exp_doc_seq = $conn->prepare("SELECT MAX(CAST(SUBSTRING(doc_id, 13) AS UNSIGNED)) FROM ims_expenses WHERE exp_year = ? AND exp_month = ? AND doc_id LIKE 'EXP-%'");
    $stmt_exp_doc_seq->execute([date('Y', strtotime($doc_date)), date('m', strtotime($doc_date))]);
    $next_expense_monthly_sequence = (int)$stmt_exp_doc_seq->fetchColumn();

    // 6. PROCESS DETAILS LOOP
    foreach ($details as $item) {
        $current_product_name = $item['product_name'] ?? null;
        $current_unit_id = $item['unit_id'] ?? null;
        $qty = (float)($item['quantity'] ?? 0);
        $price = (float)($item['price'] ?? 0);
        $current_inv = $item['inv'] ?? '-';

        if (empty($current_product_name) || $qty <= 0) {
            throw new Exception("Invalid Detail Data at line $line_no");
        }

        $item_total = $qty * $price;
        $total_amount_header += $item_total;

        // --- Product & Category Handling ---
        $current_product_id = $item['product_id'] ?? null;
        $pgroup_id = $item['pgroup_id'] ?? 'DEFAULT_PGROUP';

        // Product Logic (Simplified)
        if (empty($current_product_id)) {
            // Search by name
            $stmtCheckProductByName->execute([$current_product_name]);
            $existProd = $stmtCheckProductByName->fetch(PDO::FETCH_ASSOC);
            if ($existProd) {
                $current_product_id = $existProd['product_id'];
                // Update info if needed
                $stmtUpdateProduct->execute([$pgroup_id, $current_unit_id, $current_product_id]);
            } else {
                // Generate New ID
                $stmtGetLastProductId->execute();
                $last_pid = $stmtGetLastProductId->fetchColumn();
                $next_pid_seq = $last_pid ? (int)substr($last_pid, 1) + 1 : 1;
                $current_product_id = 'P' . sprintf('%05d', $next_pid_seq);

                $stmtInsertProduct->execute([$current_product_id, $current_product_name, $pgroup_id, $current_unit_id]);
            }
        } else {
            // Have ID, Update info
            $stmtUpdateProduct->execute([$pgroup_id, $current_unit_id, $current_product_id]);
        }

        // Category Logic
        $stmtCheckCategory->execute([$pgroup_id]);
        if (!$stmtCheckCategory->fetch()) {
            $pgroup_name = "Group " . $pgroup_id; // หรือดึงจาก ims_pgroup ถ้ามี
            $stmtInsertCategory->execute([$pgroup_id, $pgroup_name]);
        }

        // --- Insert Detail Item ---
        $stmtDetailPV->execute([
            $doc_no, date('Y-m-d', strtotime($doc_date)), $line_no++,
            $current_product_id, $current_product_name, $current_inv,
            $qty, $price, $current_unit_id, $item['unit_name'] ?? '', $item['remark'] ?? ''
        ]);

        // --- Expenses Handling ---
        $exp_date = date('Y-m-d', strtotime($doc_date));
        $exp_m = date('m', strtotime($doc_date));
        $exp_y = date('Y', strtotime($doc_date));

        $target_expense_id = null;
        $target_expense_doc_id = null;

        // Try to match existing expense (FIFO from map)
        if ($action === 'UPDATE' && !empty($existing_expenses_map[$current_inv])) {
            $matched_exp = array_shift($existing_expenses_map[$current_inv]);
            $target_expense_id = $matched_exp['id'];
            $target_expense_doc_id = $matched_exp['doc_id'];
        }

        if ($target_expense_id) {
            // UPDATE Existing Expense
            $stmtUpdateExpense->execute([
                $target_expense_doc_id, $receipt_name, $exp_date, $exp_m, $exp_y,
                $current_inv, $pgroup_id, $current_product_name, $qty, $current_unit_id,
                $item_total, $item['remark'] ?? '', 'Y', $picture_doc, $payment_method, $price,
                $petty_cash_status,
                $target_expense_id
            ]);
            $expense_ids_to_keep[] = $target_expense_id;
        } else {
            // INSERT New Expense
            $next_expense_global_runno++;
            $next_expense_monthly_sequence++;
            $target_expense_doc_id = "EXP-" . $exp_y . "-" . $exp_m . "-" . sprintf('%04d', $next_expense_monthly_sequence);

            $stmtInsertExpense->execute([
                $next_expense_global_runno, $target_expense_doc_id, $doc_no, $receipt_name,
                $exp_date, $exp_m, $exp_y, $current_inv, $pgroup_id, $current_product_name,
                $qty, $current_unit_id, $item_total, $item['remark'] ?? '', 'Y', $picture_doc, $payment_method, $price,
                $petty_cash_status
            ]);
            $expense_ids_to_keep[] = $conn->lastInsertId();
        }

    } // End Loop

    // 7. Cleanup Orphaned Expenses (UPDATE only)
    if ($action === 'UPDATE') {
        $stmtFetchAllExp = $conn->prepare("SELECT id FROM ims_expenses WHERE doc_ref = ?");
        $stmtFetchAllExp->execute([$doc_no]);
        $all_related_expenses = $stmtFetchAllExp->fetchAll(PDO::FETCH_COLUMN);

        $ids_to_delete = array_diff($all_related_expenses, $expense_ids_to_keep);

        if (!empty($ids_to_delete)) {
            $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
            $stmtDeleteOrphan = $conn->prepare("DELETE FROM ims_expenses WHERE id IN ($placeholders)");
            $stmtDeleteOrphan->execute(array_values($ids_to_delete));
        }
    }

    // 8. Update Header Total Amount (For both ADD and UPDATE)
    // สำหรับ ADD: เรา Insert ไปแล้วแต่ยอด 0, ตอนนี้มา Update ยอดจริง
    // สำหรับ UPDATE: เรา Update ข้อมูลอื่นๆ + ยอดเงินใหม่
    $stmtUpdateHeaderFinal = $conn->prepare("UPDATE ims_payment_voucher SET 
        doc_date = ?, requester = ?, supplier_id = ?, supplier_name = ?, purpose = ?,
        payment_method = ?, bank_no = ?, total_amount = ?, picture_doc = ?, 
        create_name = ?, checker_name = ?, receipt_name = ?, approve_name = ?, 
        approve_status = ?, address = ?, petty_cash_status = ?
        WHERE doc_no = ?");

    $stmtUpdateHeaderFinal->execute([
        $doc_date, $requester, $supplier_id, $supplier_name, $purpose,
        $payment_method, $bank_account, $total_amount_header, $picture_doc,
        $create_name, $checker_name, $receipt_name, $approve_name,
        $approve_status, $address, $petty_cash_status, $doc_no
    ]);

    // 9. Accounting Posting (GL)
    // สำหรับ UPDATE ให้ลบของเก่าออกก่อน
    if ($action === 'UPDATE') {
        RemoveGLByDocNo($conn, $doc_no);
    }

    // เตรียมรายการบัญชี (Dr/Cr)
    $gl_entries = [];
    
    // ฝั่ง Debit (ค่าใช้จ่าย) - ลงตามรายรายการ (หรือจะรวมยอดก็ได้)
    // ในที่นี้เราใช้รหัสบัญชี 5101 เป็นหลัก (สามารถปรับให้ดึงตาม pgroup_id ได้ในอนาคต)
    $gl_entries[] = [
        'acc_code' => '5101', 
        'dr' => $total_amount_header,
        'cr' => 0
    ];

    // ฝั่ง Credit (เงินที่จ่ายออก) - ตรวจสอบว่าจ่ายด้วยอะไร
    $payment_acc = GetAccountCodeMapping($conn, $payment_method, 'payment');
    $gl_entries[] = [
        'acc_code' => $payment_acc,
        'dr' => 0,
        'cr' => $total_amount_header
    ];

    $gl_desc = "จ่ายเงินให้ " . $supplier_name . " (" . $purpose . ") ตามเอกสาร " . $doc_no;
    PostToGL($conn, $doc_date, $doc_no, $gl_desc, $gl_entries, 'PV');

    $conn->commit();
    echo json_encode(['status' => 'success', 'doc_no' => $doc_no]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    // Log error หรือส่งรายละเอียด error เฉพาะตอน dev
    echo json_encode(['status' => 'error', 'message' => $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine()]);
}