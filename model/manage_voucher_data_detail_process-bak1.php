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
$doc_date = $data['date'] ?? ''; // Format from frontend might be D-M-Y or Y-M-D
$requester = $data['requester'] ?? '';
$supplier_id = $data['supplier_id'] ?? '';
$supplier_name = $data['supplier_name'] ?? '';
$address = $data['address'] ?? '-';
$supplier_phone = $data['supplier_phone'] ?? '-'; // เพิ่มรับค่าเบอร์โทรศัพท์
$purpose = $data['purpose'] ?? '';
$details = $data['details'] ?? [];
$picture_doc = $data['picture_doc'] ?? '';

$payment_method = $data['payment_method'] ?? '';
$create_name = $data['create_name'] ?? '';
$checker_name = $data['checker_name'] ?? '';

$receipt_name = ($supplier_name === null || $supplier_name === '') ? '-' : $supplier_name;

$approve_name = $data['approve_name'] ?? '';
$approve_status = $data['approve_status'] ?? 'N';

$bank_account = $data['bank_account'] ?? '';

// ตรวจสอบค่าที่จำเป็น
if (!in_array($action, ['ADD', 'UPDATE'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing action']);
    exit;
}

if (!$doc_date || !$supplier_name || empty($details)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required header or detail fields']);
    exit;
}

// ตรวจสอบและกำหนดค่าเริ่มต้นสำหรับ NOT NULL ที่ไม่มี DEFAULT
$picture_doc = $picture_doc === null ? '' : $picture_doc;
$receipt_name = $receipt_name === null ? '-' : $receipt_name;
$payment_method = $payment_method === null ? '-' : $payment_method;


try {
    $conn->beginTransaction();

    // --- Start Supplier Handling (Insert or Update) ---
    // Check if supplier_name exists in ims_supplier
    $stmtCheckSupplier = $conn->prepare("SELECT supplier_id, supplier_name FROM ims_supplier WHERE supplier_name = ?");
    $stmtCheckSupplier->execute([$supplier_name]);
    $existingSupplier = $stmtCheckSupplier->fetch(PDO::FETCH_ASSOC);

    if ($existingSupplier) {
        // Supplier exists: Use ID and UPDATE info (Address/Phone)
        $supplier_id = $existingSupplier['supplier_id'];

        // เพิ่ม: อัปเดตข้อมูล Supplier เดิมให้เป็นปัจจุบัน
        $stmtUpdateSupplier = $conn->prepare("UPDATE ims_supplier SET address = ?, phone = ? WHERE supplier_id = ?");
        if (!$stmtUpdateSupplier->execute([$address, $supplier_phone, $supplier_id])) {
            $errorInfo = $stmtUpdateSupplier->errorInfo();
            throw new Exception("Update existing supplier info failed: " . $errorInfo[2]);
        }

    } else {
        // Supplier does not exist: Create NEW ID and INSERT
        $stmtGetLastSupplierId = $conn->prepare("SELECT MAX(supplier_id) AS last_supplier_id FROM ims_supplier WHERE supplier_id LIKE 'S%'");
        $stmtGetLastSupplierId->execute();
        $last_supplier_id = $stmtGetLastSupplierId->fetchColumn();

        $next_supplier_sequence = 1;
        if ($last_supplier_id) {
            $numeric_part = (int)substr($last_supplier_id, 1);
            $next_supplier_sequence = $numeric_part + 1;
        }
        $new_supplier_id = 'S' . sprintf('%05d', $next_supplier_sequence);

        // Insert new supplier
        $stmtInsertSupplier = $conn->prepare("INSERT INTO ims_supplier (supplier_id, supplier_name, address, phone, status) VALUES (?, ?, ?, ?, 'Active')");

        if (!$stmtInsertSupplier->execute([$new_supplier_id, $supplier_name, $address, $supplier_phone])) {
            $errorInfo = $stmtInsertSupplier->errorInfo();
            throw new Exception("Insert new supplier failed: " . $errorInfo[2]);
        }
        $supplier_id = $new_supplier_id;
    }
    // --- End Supplier Handling ---

    $total_amount_header = 0;

    // Fetch next runno for ADD action (for ims_payment_voucher)
    if ($action === 'ADD') {
        $stmt_pv_runno = $conn->prepare("SELECT MAX(doc_runno) AS last_runno FROM ims_payment_voucher WHERE doc_year = ?");
        $stmt_pv_runno->execute([date('Y', strtotime($doc_date))]);
        $last_pv_runno = $stmt_pv_runno->fetchColumn();
        $next_pv_runno = ($last_pv_runno ? $last_pv_runno : 0) + 1;
        $doc_no = "PV-" . date('Y', strtotime($doc_date)). date('m', strtotime($doc_date)) . "-" . sprintf('%04d', $next_pv_runno);
    }

    // Prepare header statement for ims_payment_voucher
    if ($action === 'ADD') {
        $stmtHeader = $conn->prepare("INSERT INTO ims_payment_voucher (doc_no, doc_date, doc_month, doc_year, doc_runno, requester, supplier_id, supplier_name, purpose, payment_method, bank_no, total_amount, picture_doc, create_name, checker_name, receipt_name, approve_name, approve_status, status, address)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    } else { // UPDATE
        $stmtHeader = $conn->prepare("UPDATE ims_payment_voucher SET doc_date = ?, requester = ?, supplier_id = ?, supplier_name = ?, purpose = ?, payment_method = ?, bank_no = ?, total_amount = ?, picture_doc = ?, create_name = ?, checker_name = ?, receipt_name = ?, approve_name = ?, approve_status = ?, address = ?
            WHERE doc_no = ?");

        // Delete existing detail items for UPDATE action
        $stmtDeleteDetailPV = $conn->prepare("DELETE FROM ims_payment_voucher_items WHERE doc_no = ?");
        if (!$stmtDeleteDetailPV->execute([$doc_no])) {
            $errorInfo = $stmtDeleteDetailPV->errorInfo();
            throw new Exception("Delete existing ims_payment_voucher_items detail failed: " . $errorInfo[2]);
        }

        // Delete existing expenses related to this doc_no
        $stmtDeleteExistingExpenses = $conn->prepare("DELETE FROM ims_expenses WHERE doc_ref = ?");
        if (!$stmtDeleteExistingExpenses->execute([$doc_no])) {
            $errorInfo = $stmtDeleteExistingExpenses->errorInfo();
            throw new Exception("Delete existing ims_expenses records failed: " . $errorInfo[2]);
        }
    }

    // Prepare detail statement
    $stmtDetailPV = $conn->prepare("INSERT INTO ims_payment_voucher_items (doc_no, doc_date, line_no, product_id, product_name, inv, quantity, price, unit_id, unit_name, remark)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Prepare statements for ims_expenses
    $stmtInsertExpense = $conn->prepare("INSERT INTO ims_expenses (runno, doc_id, doc_ref, receipt_name, expense_date, exp_month, exp_year, inv, category_id, description, qty, unit_id, amount, remark, approve_status, file_attach, payment_method, price_per_unit)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmtUpdateExpense = $conn->prepare("UPDATE ims_expenses SET
        doc_id = ?, doc_ref = ?, receipt_name = ?, expense_date = ?, exp_month = ?, exp_year = ?, inv = ?,
        category_id = ?, description = ?, qty = ?, unit_id = ?, amount = ?,
        remark = ?, approve_status = ?, file_attach = ?, payment_method = ?, price_per_unit = ?
        WHERE id = ?");

    // Prepare statements for ims_products and ims_category
    $stmtCheckProductById = $conn->prepare("SELECT product_id, product_name, pgroup_id, unit_id FROM ims_products WHERE product_id = ?");
    $stmtCheckProductByName = $conn->prepare("SELECT product_id, product_name, pgroup_id, unit_id FROM ims_products WHERE product_name = ?");
    $stmtInsertProduct = $conn->prepare("INSERT INTO ims_products (product_id, product_name, pgroup_id, unit_id, status) VALUES (?, ?, ?, ?, 'Active')");
    $stmtUpdateProduct = $conn->prepare("UPDATE ims_products SET product_name = ?, pgroup_id = ?, unit_id = ? WHERE product_id = ?");
    $stmtGetLastProductId = $conn->prepare("SELECT MAX(product_id) AS last_product_id FROM ims_products WHERE product_id LIKE 'P%'");


    $stmtGetPgroupName = $conn->prepare("SELECT pgroup_name FROM ims_pgroup WHERE pgroup_id = ?");
    $stmtCheckCategory = $conn->prepare("SELECT category_name FROM ims_category WHERE category_id = ?");
    $stmtInsertCategory = $conn->prepare("INSERT INTO ims_category (category_id, category_name, status) VALUES (?, ?, 'Active')");
    $stmtUpdateCategory = $conn->prepare("UPDATE ims_category SET category_name = ? WHERE category_id = ?");

    // For UPDATE action on ims_expenses
    $existing_expenses_map = [];
    $expense_ids_to_keep = [];

    if ($action === 'UPDATE') {
        $stmtFetchExistingExpenses = $conn->prepare("SELECT id, inv, doc_id FROM ims_expenses WHERE doc_ref = ?");
        $stmtFetchExistingExpenses->execute([$doc_no]);
        $temp_existing_expenses = $stmtFetchExistingExpenses->fetchAll(PDO::FETCH_ASSOC);

        foreach ($temp_existing_expenses as $exp) {
            if (!isset($existing_expenses_map[$exp['inv']])) {
                $existing_expenses_map[$exp['inv']] = [];
            }
            $existing_expenses_map[$exp['inv']][] = ['id' => $exp['id'], 'doc_id' => $exp['doc_id']];
        }
    }

    $line_no = 1;

    // Fetch next general runno for ims_expenses
    $stmt_expense_global_runno = $conn->prepare("SELECT MAX(runno) AS last_runno FROM ims_expenses WHERE exp_year = ?");
    $stmt_expense_global_runno->execute([date('Y', strtotime($doc_date))]);
    $last_expense_global_runno = $stmt_expense_global_runno->fetchColumn();
    $next_expense_global_runno = ($last_expense_global_runno ? $last_expense_global_runno : 0);

    // Prepare expense doc_id
    $exp_month_for_doc_id = date('m', strtotime($doc_date));
    $exp_year_for_doc_id = date('Y', strtotime($doc_date));
    $next_expense_monthly_sequence = 0;

    $stmt_max_exp_doc_id = $conn->prepare("SELECT MAX(CAST(SUBSTRING(doc_id, 13) AS UNSIGNED)) FROM ims_expenses WHERE exp_year = ? AND exp_month = ? AND doc_id LIKE 'EXP-%'");
    $stmt_max_exp_doc_id->execute([$exp_year_for_doc_id, $exp_month_for_doc_id]);
    $last_exp_doc_id_sequence = $stmt_max_exp_doc_id->fetchColumn();
    $next_expense_monthly_sequence = ($last_exp_doc_id_sequence ? $last_exp_doc_id_sequence : 0);


    foreach ($details as $item) {
        $current_product_id = $item['product_id'] ?? null;
        $current_product_name = $item['product_name'] ?? null;
        $current_unit_id = $item['unit_id'] ?? null;
        $current_unit_name = $item['unit_name'] ?? null;
        $current_inv = $item['inv'] ?? '-';

        if (empty($current_product_name) || !isset($item['quantity']) || !is_numeric($item['quantity']) || !isset($item['price']) || !is_numeric($item['price'])) {
            throw new Exception("Missing or invalid product name, quantity, or price for line " . $line_no);
        }

        $item_total = (float)$item['quantity'] * (float)$item['price'];
        $total_amount_header += $item_total;

        // --- Handle ims_products table ---
        $existingProduct = null;
        if (!empty($current_product_id)) {
            $stmtCheckProductById->execute([$current_product_id]);
            $existingProduct = $stmtCheckProductById->fetch(PDO::FETCH_ASSOC);
        }

        if (!$existingProduct && !empty($current_product_name)) {
            $stmtCheckProductByName->execute([$current_product_name]);
            $existingProduct = $stmtCheckProductByName->fetch(PDO::FETCH_ASSOC);
            if ($existingProduct) {
                $current_product_id = $existingProduct['product_id'];
            }
        }

        $pgroup_id_for_product = null;

        if ($existingProduct) {
            $current_product_id = $existingProduct['product_id'];
            $pgroup_id_for_product = $existingProduct['pgroup_id'];

            if ($existingProduct['product_name'] !== $current_product_name || empty($existingProduct['pgroup_id']) || empty($current_unit_id)) {
                if (empty($pgroup_id_for_product)) {
                    $pgroup_id_for_product = 'UNKNOWN_PGROUP';
                }
                if (!$stmtUpdateProduct->execute([$current_product_name, $pgroup_id_for_product, $current_unit_id, $current_product_id])) {
                    $errorInfo = $stmtUpdateProduct->errorInfo();
                    throw new Exception("Update product failed: " . $errorInfo[2]);
                }
            }
        } else {
            if (empty($current_product_id)) {
                $stmtGetLastProductId->execute();
                $last_product_id = $stmtGetLastProductId->fetchColumn();

                $next_product_sequence = 1;
                if ($last_product_id) {
                    $numeric_part = (int)substr($last_product_id, 1);
                    $next_product_sequence = $numeric_part + 1;
                }
                $current_product_id = 'P' . sprintf('%05d', $next_product_sequence);
            }

            $pgroup_id_for_product = 'DEFAULT_PGROUP';
            if (isset($item['pgroup_id']) && !empty($item['pgroup_id'])) {
                $pgroup_id_for_product = $item['pgroup_id'];
            }
            if (!$stmtInsertProduct->execute([$current_product_id, $current_product_name, $pgroup_id_for_product, $current_unit_id])) {
                $errorInfo = $stmtInsertProduct->errorInfo();
                throw new Exception("Insert new product failed: " . $errorInfo[2]);
            }
        }

        // --- Handle ims_category ---
        if (!empty($pgroup_id_for_product)) {
            $pgroup_name_from_ims_pgroup = null;
            $stmtGetPgroupName->execute([$pgroup_id_for_product]);
            $pgroup_data = $stmtGetPgroupName->fetch(PDO::FETCH_ASSOC);

            if ($pgroup_data) {
                $pgroup_name_from_ims_pgroup = $pgroup_data['pgroup_name'];
            } else {
                $pgroup_name_from_ims_pgroup = "Group " . $pgroup_id_for_product;
            }

            $stmtCheckCategory->execute([$pgroup_id_for_product]);
            $existingCategory = $stmtCheckCategory->fetch(PDO::FETCH_ASSOC);

            if ($existingCategory) {
                if ($existingCategory['category_name'] !== $pgroup_name_from_ims_pgroup) {
                    if (!$stmtUpdateCategory->execute([$pgroup_name_from_ims_pgroup, $pgroup_id_for_product])) {
                        $errorInfo = $stmtUpdateCategory->errorInfo();
                        throw new Exception("Update category failed: " . $errorInfo[2]);
                    }
                }
            } else {
                if (!$stmtInsertCategory->execute([$pgroup_id_for_product, $pgroup_name_from_ims_pgroup])) {
                    $errorInfo = $stmtInsertCategory->errorInfo();
                    throw new Exception("Insert new category failed: " . $errorInfo[2]);
                }
            }
        }

        // Insert into ims_payment_voucher_items
        if (!$stmtDetailPV->execute([
            $doc_no,
            date('Y-m-d', strtotime($doc_date)),
            $line_no++,
            $current_product_id,
            $current_product_name,
            $current_inv,
            (float)$item['quantity'],
            (float)$item['price'],
            $current_unit_id,
            $current_unit_name,
            $item['remark'] ?? '',
        ])) {
            $errorInfo = $stmtDetailPV->errorInfo();
            throw new Exception("Insert detail into ims_payment_voucher_items failed: " . $errorInfo[2]);
        }

        // --- Handle ims_expenses (UPDATE/INSERT logic) ---
        $expense_date_formatted = date('Y-m-d', strtotime($doc_date));
        $exp_month = date('m', strtotime($doc_date));
        $exp_year = date('Y', strtotime($doc_date));
        $expense_description = $current_product_name;
        $expense_remark = $item['remark'] ?? '';

        $found_existing_expense_info = null;
        $generated_expense_doc_id = null;

        if ($action === 'UPDATE' && isset($existing_expenses_map[$current_inv]) && !empty($existing_expenses_map[$current_inv])) {
            $found_existing_expense_info = array_shift($existing_expenses_map[$current_inv]);
        }

        if ($found_existing_expense_info) {
            // UPDATE existing ims_expenses record
            $generated_expense_doc_id = $found_existing_expense_info['doc_id'];

            if (!$stmtUpdateExpense->execute([
                $generated_expense_doc_id,
                $doc_no,
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
                'N',
                $picture_doc,
                $payment_method,
                (float)$item['price'],
                $found_existing_expense_info['id']
            ])) {
                $errorInfo = $stmtUpdateExpense->errorInfo();
                throw new Exception("Update ims_expenses failed for ID {$found_existing_expense_info['id']}: " . $errorInfo[2]);
            }
            $expense_ids_to_keep[] = $found_existing_expense_info['id'];

        } else {
            // INSERT new ims_expenses record
            $next_expense_global_runno++;
            $next_expense_monthly_sequence++;
            $generated_expense_doc_id = "EXP-" . $exp_year_for_doc_id . "-" . $exp_month_for_doc_id . "-" . sprintf('%04d', $next_expense_monthly_sequence);

            if (!$stmtInsertExpense->execute([
                $next_expense_global_runno,
                $generated_expense_doc_id,
                $doc_no,
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
                'N',
                $picture_doc,
                $payment_method,
                (float)$item['price']
            ])) {
                $errorInfo = $stmtInsertExpense->errorInfo();
                throw new Exception("Insert into ims_expenses failed: " . $errorInfo[2]);
            }
            $expense_ids_to_keep[] = $conn->lastInsertId();
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
            date('m', strtotime($doc_date)),
            date('Y', strtotime($doc_date)),
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
            'N',
            'Active',
            $address
        ])) {
            $errorInfo = $stmtHeader->errorInfo();
            throw new Exception("Insert header into ims_payment_voucher failed: " . $errorInfo[2]);
        }
    } else { // UPDATE
        // ** ตรวจสอบลำดับ Address และ Doc_no ที่ถูกต้องแล้ว **
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
            $approve_status,
            $address, // ตำแหน่งที่ถูกต้องสำหรับ SET address = ?
            $doc_no   // ตำแหน่งที่ถูกต้องสำหรับ WHERE doc_no = ?
        ])) {
            $errorInfo = $stmtHeader->errorInfo();
            throw new Exception("Update header in ims_payment_voucher failed: " . $errorInfo[2]);
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'doc_no' => $doc_no]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>