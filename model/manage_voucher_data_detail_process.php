<?php
header('Content-Type: application/json');
include('../config/connect_db.php'); // ตรวจสอบให้แน่ใจว่าไฟล์นี้มีการตั้งค่า PDO error mode เป็น ERRMODE_EXCEPTION หรือมีการจัดการ error ที่ดี

// รับข้อมูล JSON จาก POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

$action = $data['action'] ?? '';
$doc_no = $data['doc_no'] ?? ''; // This is ims_payment_voucher's doc_no
$doc_date = $data['date'] ?? ''; // Format from frontend might be D-M-Y or Y-M-D, ensure strtotime can parse it
$requester = $data['requester'] ?? '';
$supplier_id = $data['supplier_id'] ?? '';
$supplier_name = $data['supplier_name'] ?? '';
$purpose = $data['purpose'] ?? '';
$details = $data['details'] ?? [];
$picture_doc = $data['picture_doc'] ?? ''; // NOT NULL in ims_expenses, ensure it's not null/empty string if no file

$payment_method = $data['payment_method'] ?? ''; // NOT NULL in ims_expenses, has default '-'
$create_name = $data['create_name'] ?? '';
$checker_name = $data['checker_name'] ?? '';

//$receipt_name = $data['receipt_name'] ?? ''; // NOT NULL in ims_expenses, has default '-'
$receipt_name = ($supplier_name === null || $supplier_name === '') ? '-' : $supplier_name;

$approve_name = $data['approve_name'] ?? '';
$approve_status = $data['approve_status'] ?? 'N'; // New: Get approve_status from data, default to 'N'

$bank_account = $data['bank_account'] ?? ''; // Bank account number

// ตรวจสอบค่าที่จำเป็น
if (!in_array($action, ['ADD', 'UPDATE'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing action']);
    exit;
}

if (!$doc_date || !$requester || !$supplier_id || !$supplier_name || empty($details)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required header or detail fields']);
    exit;
}

// ตรวจสอบและกำหนดค่าเริ่มต้นสำหรับ NOT NULL ที่ไม่มี DEFAULT (หรือต้องการควบคุมค่า)
$picture_doc = $picture_doc === null ? '' : $picture_doc; // Ensure it's an empty string if null, as it's NOT NULL TEXT
$receipt_name = $receipt_name === null ? '-' : $receipt_name; // Ensure it's '-' if null
$payment_method = $payment_method === null ? '-' : $payment_method; // Ensure it's '-' if null


try {
    $conn->beginTransaction();

    $total_amount_header = 0;

    // Fetch next runno for ADD action (for ims_payment_voucher)
    if ($action === 'ADD') {
        // Generate doc_no for ims_payment_voucher
        $stmt_pv_runno = $conn->prepare("SELECT MAX(doc_runno) AS last_runno FROM ims_payment_voucher WHERE doc_year = ?");
        $stmt_pv_runno->execute([date('Y', strtotime($doc_date))]); // Use doc_date year for consistency
        $last_pv_runno = $stmt_pv_runno->fetchColumn();
        $next_pv_runno = ($last_pv_runno ? $last_pv_runno : 0) + 1;
        $doc_no = "PV-" . date('Y', strtotime($doc_date)). date('m', strtotime($doc_date)) . "-" . sprintf('%04d', $next_pv_runno); // Use doc_date year
    }

    // Prepare header statement for ims_payment_voucher
    if ($action === 'ADD') {
        $stmtHeader = $conn->prepare("INSERT INTO ims_payment_voucher (doc_no, doc_date, doc_year, doc_runno, requester, supplier_id, supplier_name, purpose, payment_method, bank_no, total_amount, picture_doc, create_name, checker_name, receipt_name, approve_name, approve_status, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    } else { // UPDATE
        $stmtHeader = $conn->prepare("UPDATE ims_payment_voucher SET doc_date = ?, requester = ?, supplier_id = ?, supplier_name = ?, purpose = ?, payment_method = ?, bank_no = ?, total_amount = ?, picture_doc = ?, create_name = ?, checker_name = ?, receipt_name = ?, approve_name = ?, approve_status = ?
            WHERE doc_no = ?");

        // Delete existing detail items for UPDATE action from ims_payment_voucher_items
        $stmtDeleteDetailPV = $conn->prepare("DELETE FROM ims_payment_voucher_items WHERE doc_no = ?");
        if (!$stmtDeleteDetailPV->execute([$doc_no])) {
            $errorInfo = $stmtDeleteDetailPV->errorInfo();
            throw new Exception("Delete existing ims_payment_voucher_items detail failed: " . $errorInfo[2]);
        }

        // ลบข้อมูล ims_expenses ที่เกี่ยวข้องกับ doc_no ของ payment voucher นี้
        // doc_ref จะเก็บค่า doc_no ของ ims_payment_voucher
        $stmtDeleteExistingExpenses = $conn->prepare("DELETE FROM ims_expenses WHERE doc_ref = ?");
        if (!$stmtDeleteExistingExpenses->execute([$doc_no])) {
            $errorInfo = $stmtDeleteExistingExpenses->errorInfo();
            throw new Exception("Delete existing ims_expenses records failed: " . $errorInfo[2]);
        }
    }

    // Prepare detail statement for ims_payment_voucher_items (Re-added doc_date based on schema)
    $stmtDetailPV = $conn->prepare("INSERT INTO ims_payment_voucher_items (doc_no, doc_date, line_no, product_id, product_name, inv, quantity, price, unit_id, unit_name)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Prepare statements for ims_expenses (INSERT and UPDATE)
    // **สำคัญมาก:** 'doc_ref' ถูกเพิ่มเข้ามาใน schema ของ ims_expenses ตามคำขอ
    // โปรดตรวจสอบให้แน่ใจว่าตาราง 'ims_expenses' ของคุณมีคอลัมน์ 'doc_ref' (เช่น VARCHAR(255)) แล้วในฐานข้อมูล
    // ตัวอย่าง SQL: ALTER TABLE ims_expenses ADD COLUMN doc_ref VARCHAR(255) NULL DEFAULT NULL AFTER doc_id;
    $stmtInsertExpense = $conn->prepare("INSERT INTO ims_expenses (runno, doc_id, doc_ref, receipt_name, expense_date, exp_month, exp_year, inv, category_id, description, qty, unit_id, amount, remark, approve_status, file_attach, payment_method, price_per_unit)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmtUpdateExpense = $conn->prepare("UPDATE ims_expenses SET
        doc_id = ?, doc_ref = ?, receipt_name = ?, expense_date = ?, exp_month = ?, exp_year = ?, inv = ?,
        category_id = ?, description = ?, qty = ?, unit_id = ?, amount = ?,
        remark = ?, approve_status = ?, file_attach = ?, payment_method = ?, price_per_unit = ?
        WHERE id = ?");

    // Prepare statements for ims_products and ims_category
    $stmtCheckProduct = $conn->prepare("SELECT product_name, pgroup_id FROM ims_products WHERE product_id = ?");
    $stmtInsertProduct = $conn->prepare("INSERT INTO ims_products (product_id, product_name, pgroup_id, unit_id, status) VALUES (?, ?, ?, ?, 'Active')");
    $stmtUpdateProduct = $conn->prepare("UPDATE ims_products SET product_name = ?, pgroup_id = ?, unit_id = ? WHERE product_id = ?");

    $stmtGetPgroupName = $conn->prepare("SELECT pgroup_name FROM ims_pgroup WHERE pgroup_id = ?");
    $stmtCheckCategory = $conn->prepare("SELECT category_name FROM ims_category WHERE category_id = ?");
    $stmtInsertCategory = $conn->prepare("INSERT INTO ims_category (category_id, category_name, status) VALUES (?, ?, 'Active')");
    $stmtUpdateCategory = $conn->prepare("UPDATE ims_category SET category_name = ? WHERE category_id = ?");

    // For UPDATE action on ims_expenses, fetch existing records to determine what to update, insert, or delete
    $existing_expenses_map = []; // Map to store existing expenses for easy lookup
    $expense_ids_to_keep = [];   // List of IDs of expenses that are matched/updated

    if ($action === 'UPDATE') {
        // ดึงข้อมูล expenses ที่มีอยู่ซึ่งเชื่อมโยงกับ doc_ref (ซึ่งก็คือ doc_no ของ PV)
        $stmtFetchExistingExpenses = $conn->prepare("SELECT id, inv, doc_id FROM ims_expenses WHERE doc_ref = ?");
        $stmtFetchExistingExpenses->execute([$doc_no]);
        $temp_existing_expenses = $stmtFetchExistingExpenses->fetchAll(PDO::FETCH_ASSOC);

        foreach ($temp_existing_expenses as $exp) {
            // เก็บ id และ doc_id เดิมไว้เพื่อการอัปเดตหรือลบ
            if (!isset($existing_expenses_map[$exp['inv']])) {
                $existing_expenses_map[$exp['inv']] = [];
            }
            $existing_expenses_map[$exp['inv']][] = ['id' => $exp['id'], 'doc_id' => $exp['doc_id']];
        }
    }

    $line_no = 1;

    // Fetch next general runno for ims_expenses (annual scope, used for the 'runno' column)
    $stmt_expense_global_runno = $conn->prepare("SELECT MAX(runno) AS last_runno FROM ims_expenses WHERE exp_year = ?");
    $stmt_expense_global_runno->execute([date('Y', strtotime($doc_date))]);
    $last_expense_global_runno = $stmt_expense_global_runno->fetchColumn();
    $next_expense_global_runno = ($last_expense_global_runno ? $last_expense_global_runno : 0);

    // เตรียมการสร้างเลขที่เอกสาร EXP-YYYY-MM-XXXXX
    $exp_month_for_doc_id = date('m', strtotime($doc_date));
    $exp_year_for_doc_id = date('Y', strtotime($doc_date));
    $next_expense_monthly_sequence = 0;

    // หาเลขรันสูงสุดสำหรับเดือน/ปีปัจจุบันสำหรับ doc_id ของ EXP
    // !!! แก้ไขตรงนี้: เปลี่ยน SUBSTRING(doc_id, 12) เป็น SUBSTRING(doc_id, 13)
    $stmt_max_exp_doc_id = $conn->prepare("SELECT MAX(CAST(SUBSTRING(doc_id, 13) AS UNSIGNED)) FROM ims_expenses WHERE exp_year = ? AND exp_month = ? AND doc_id LIKE 'EXP-%'");
    $stmt_max_exp_doc_id->execute([$exp_year_for_doc_id, $exp_month_for_doc_id]);
    $last_exp_doc_id_sequence = $stmt_max_exp_doc_id->fetchColumn();
    // ถ้าไม่มีเลขรันเดิมในเดือนนั้น ให้เริ่มต้นจาก 0 เพื่อให้ได้ 1 ในครั้งแรก
    $next_expense_monthly_sequence = ($last_exp_doc_id_sequence ? $last_exp_doc_id_sequence : 0);


    foreach ($details as $item) {
        $current_product_id = $item['product_id'] ?? null;
        $current_product_name = $item['product_name'] ?? null;
        $current_unit_id = $item['unit_id'] ?? null;
        $current_unit_name = $item['unit_name'] ?? null;
        $current_inv = $item['inv'] ?? '-'; // Default to '-' if null, per DB schema

        if (empty($current_product_id) || empty($current_product_name) || !isset($item['quantity']) || !is_numeric($item['quantity']) || !isset($item['price']) || !is_numeric($item['price'])) {
            throw new Exception("Missing or invalid product details for line " . $line_no);
        }

        $item_total = (float)$item['quantity'] * (float)$item['price'];
        $total_amount_header += $item_total;

        // --- Handle ims_products table (Create/Update if necessary) ---
        $stmtCheckProduct->execute([$current_product_id]);
        $existingProduct = $stmtCheckProduct->fetch(PDO::FETCH_ASSOC);

        $pgroup_id_for_product = null;

        if ($existingProduct) {
            $pgroup_id_for_product = $existingProduct['pgroup_id'];
            // Update product name, pgroup_id, unit_id if different or missing
            if ($existingProduct['product_name'] !== $current_product_name || empty($existingProduct['pgroup_id']) || empty($current_unit_id)) {
                if (empty($pgroup_id_for_product)) {
                    $pgroup_id_for_product = 'UNKNOWN_PGROUP'; // Fallback if no group found
                }
                if (!$stmtUpdateProduct->execute([$current_product_name, $pgroup_id_for_product, $current_unit_id, $current_product_id])) {
                    $errorInfo = $stmtUpdateProduct->errorInfo();
                    throw new Exception("Update product failed: " . $errorInfo[2]);
                }
            }
        } else {
            // Product does not exist, insert new product
            $pgroup_id_for_product = 'DEFAULT_PGROUP'; // Default for new products
            if (isset($item['pgroup_id']) && !empty($item['pgroup_id'])) {
                $pgroup_id_for_product = $item['pgroup_id'];
            }
            if (!$stmtInsertProduct->execute([$current_product_id, $current_product_name, $pgroup_id_for_product, $current_unit_id])) {
                $errorInfo = $stmtInsertProduct->errorInfo();
                throw new Exception("Insert new product failed: " . $errorInfo[2]);
            }
        }

        // --- Handle ims_category (sync with ims_pgroup) ---
        // Ensure that the category_id for ims_expenses exists in ims_category
        if (!empty($pgroup_id_for_product)) {
            $pgroup_name_from_ims_pgroup = null;
            $stmtGetPgroupName->execute([$pgroup_id_for_product]);
            $pgroup_data = $stmtGetPgroupName->fetch(PDO::FETCH_ASSOC);

            if ($pgroup_data) {
                $pgroup_name_from_ims_pgroup = $pgroup_data['pgroup_name'];
            } else {
                // If pgroup_id is not found in ims_pgroup, use a fallback name
                $pgroup_name_from_ims_pgroup = "Group " . $pgroup_id_for_product;
            }

            // Sync to ims_category
            $stmtCheckCategory->execute([$pgroup_id_for_product]);
            $existingCategory = $stmtCheckCategory->fetch(PDO::FETCH_ASSOC);

            if ($existingCategory) {
                // Category exists, update its name if different
                if ($existingCategory['category_name'] !== $pgroup_name_from_ims_pgroup) {
                    if (!$stmtUpdateCategory->execute([$pgroup_name_from_ims_pgroup, $pgroup_id_for_product])) {
                        $errorInfo = $stmtUpdateCategory->errorInfo();
                        throw new Exception("Update category failed: " . $errorInfo[2]);
                    }
                }
            } else {
                // Category does not exist, insert new category
                if (!$stmtInsertCategory->execute([$pgroup_id_for_product, $pgroup_name_from_ims_pgroup])) {
                    $errorInfo = $stmtInsertCategory->errorInfo();
                    throw new Exception("Insert new category failed: " . $errorInfo[2]);
                }
            }
        }

        // Insert into ims_payment_voucher_items
        if (!$stmtDetailPV->execute([
            $doc_no, // This is the PV doc_no
            date('Y-m-d', strtotime($doc_date)), // Use doc_date from header for detail items
            $line_no++,
            $current_product_id,
            $current_product_name,
            $current_inv,
            (float)$item['quantity'],
            (float)$item['price'],
            $current_unit_id,
            $current_unit_name,
        ])) {
            $errorInfo = $stmtDetailPV->errorInfo();
            throw new Exception("Insert detail into ims_payment_voucher_items failed: " . $errorInfo[2]);
        }

        // --- Handle ims_expenses (UPDATE/INSERT logic) ---
        //$expense_date_formatted = date('Y-m-d', strtotime($doc_date));
        $expense_date_formatted = date('d-m-Y', strtotime($doc_date));
        $exp_month = date('m', strtotime($doc_date));
        $exp_year = date('Y', strtotime($doc_date));
        $expense_description = $current_product_name;
        $expense_remark = $purpose;

        $found_existing_expense_info = null; // Stores ['id' => ..., 'doc_id' => ...]
        $generated_expense_doc_id = null; // Variable to store the new EXP doc_id

        // พยายามหา record expense ที่มีอยู่แล้วโดยใช้ doc_ref (doc_no ของ PV) และ inv
        if ($action === 'UPDATE' && isset($existing_expenses_map[$current_inv]) && !empty($existing_expenses_map[$current_inv])) {
            $found_existing_expense_info = array_shift($existing_expenses_map[$current_inv]); // "Consume" one entry from the map
        }

        if ($found_existing_expense_info) {
            // UPDATE existing ims_expenses record
            // ใช้ doc_id เดิมของ record ที่พบ
            $generated_expense_doc_id = $found_existing_expense_info['doc_id'];

            if (!$stmtUpdateExpense->execute([
                $generated_expense_doc_id,  // EXP-YYYY-MM-XXXXX (ใช้ค่าเดิม)
                $doc_no,                    // PV doc_no สำหรับ doc_ref
                $receipt_name,
                $expense_date_formatted,
                $exp_month,
                $exp_year,
                $current_inv,
                $pgroup_id_for_product,
                $expense_description,
                (float)$item['quantity'],
                $current_unit_id,
                $item_total,
                $expense_remark,
                'N', // Approve status for expense, usually starts as 'N'
                $picture_doc,
                $payment_method,
                (float)$item['price'],
                $found_existing_expense_info['id'] // ID ของ record ที่จะอัปเดต
            ])) {
                $errorInfo = $stmtUpdateExpense->errorInfo();
                throw new Exception("Update ims_expenses failed for ID {$found_existing_expense_info['id']}: " . $errorInfo[2]);
            }
            $expense_ids_to_keep[] = $found_existing_expense_info['id']; // เพิ่ม ID ที่อัปเดตแล้วเข้าไปในรายการที่จะเก็บ

        } else {
            // INSERT new ims_expenses record
            $next_expense_global_runno++; // เพิ่ม runno ประจำปีสำหรับแต่ละรายการ expense
            $next_expense_monthly_sequence++; // เพิ่มเลขรันรายเดือนสำหรับ doc_id ของ EXP
            $generated_expense_doc_id = "EXP-" . $exp_year_for_doc_id . "-" . $exp_month_for_doc_id . "-" . sprintf('%04d', $next_expense_monthly_sequence);

            if (!$stmtInsertExpense->execute([
                $next_expense_global_runno, // Annual runno
                $generated_expense_doc_id,  // EXP-YYYY-MM-XXXXX
                $doc_no,                    // PV doc_no สำหรับ doc_ref
                $receipt_name,
                $expense_date_formatted,
                $exp_month,
                $exp_year,
                $current_inv,
                $pgroup_id_for_product, // ใช้ product group เป็น category_id ใน ims_expenses
                $expense_description,
                (float)$item['quantity'],
                $current_unit_id,
                $item_total, // ยอดรวมสำหรับรายการ expense นี้
                $expense_remark,
                'N', // Default approve_status สำหรับ expense
                $picture_doc, // File attach จาก header
                $payment_method, // Payment method จาก header
                (float)$item['price']
            ])) {
                $errorInfo = $stmtInsertExpense->errorInfo();
                throw new Exception("Insert into ims_expenses failed: " . $errorInfo[2]);
            }
            $expense_ids_to_keep[] = $conn->lastInsertId(); // เพิ่ม ID ที่เพิ่ง insert เข้าไปในรายการที่จะเก็บ
        }
    } // End foreach ($details as $item)

    // --- Delete Orphaned ims_expenses records ---
    if ($action === 'UPDATE') {
        $ids_to_delete = [];
        foreach ($temp_existing_expenses as $exp) {
            if (!in_array($exp['id'], $expense_ids_to_keep)) {
                $ids_to_delete[] = $exp['id'];
            }
        }

        if (!empty($ids_to_delete)) {
            $placeholders = implode(',', array_fill(0, count($ids_to_delete), '?'));
            $stmtDeleteOrphanExpenses = $conn->prepare("DELETE FROM ims_expenses WHERE id IN ($placeholders)");
            if (!$stmtDeleteOrphanExpenses->execute($ids_to_delete)) {
                $errorInfo = $stmtDeleteOrphanExpenses->errorInfo();
                throw new Exception("Delete orphaned ims_expenses failed: " . $errorInfo[2]);
            }
        }
    }

    // Execute header statement for ims_payment_voucher after calculating total_amount
    if ($action === 'ADD') {
        if (!$stmtHeader->execute([
            $doc_no,
            $doc_date,
            date('Y', strtotime($doc_date)), // Use doc_date year for consistency
            $next_pv_runno,
            $requester,
            $supplier_id,
            $supplier_name,
            $purpose,
            $payment_method,
            $bank_account,
            $total_amount_header,
            $picture_doc,
            $create_name,
            $checker_name,
            $receipt_name,
            $approve_name,
            'N', // Default approve_status
            'Active' // Default status
        ])) {
            $errorInfo = $stmtHeader->errorInfo();
            throw new Exception("Insert header into ims_payment_voucher failed: " . $errorInfo[2]);
        }
    } else { // UPDATE
        if (!$stmtHeader->execute([
            $doc_date,
            $requester,
            $supplier_id,
            $supplier_name,
            $purpose,
            $payment_method,
            $bank_account,
            $total_amount_header,
            $picture_doc,
            $create_name,
            $checker_name,
            $receipt_name,
            $approve_name,
            $approve_status, // ใช้ค่า approve_status แบบ dynamic
            $doc_no
        ])) {
            $errorInfo = $stmtHeader->errorInfo();
            throw new Exception("Update header in ims_payment_voucher failed: " . $errorInfo[2]);
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'doc_no' => $doc_no]);

} catch (Exception $e) {
    $conn->rollBack();
    // Return specific error message for debugging
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>