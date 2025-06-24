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
$doc_date = $data['date'] ?? '';
$requester = $data['requester'] ?? '';
$supplier_id = $data['supplier_id'] ?? '';
$supplier_name = $data['supplier_name'] ?? '';
$purpose = $data['purpose'] ?? '';
$details = $data['details'] ?? [];
$picture_doc = $data['picture_doc'] ?? '';

$payment_method = $data['payment_method'] ?? '';
$create_name = $data['create_name'] ?? '';
$checker_name = $data['checker_name'] ?? '';
$receipt_name = $data['receipt_name'] ?? '';
$approve_name = $data['approve_name'] ?? '';

$bank_account = $data['bank_account'] ?? ''; // Bank account number

if (!in_array($action, ['ADD', 'UPDATE'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing action']);
    exit;
}

// ตรวจสอบความถูกต้องของวันที่ (dd-mm-yyyy) และแปลงเป็นYYYY-mm-dd สำหรับฐานข้อมูล
$dateParts = explode('-', $doc_date);
if (count($dateParts) === 3) {
    $db_doc_date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid date format. Expected dd-mm-yyyy.']);
    exit;
}


if (!$db_doc_date || !$requester) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields (date or requester).']);
    exit;
}

// ตรวจสอบ supplier (ถ้ามี supplier_name ถูกส่งมา)
if (!empty($supplier_name)) {
    $stmtCheckSupplier = $conn->prepare("SELECT supplier_id FROM ims_supplier WHERE supplier_name = ?");
    $stmtCheckSupplier->execute([$supplier_name]);
    $db_supplier_id = $stmtCheckSupplier->fetchColumn();

    if (!$db_supplier_id) {
        // สร้าง supplier_id ใหม่
        $stmtMaxID = $conn->prepare("SELECT supplier_id FROM ims_supplier WHERE supplier_id LIKE 'S%' ORDER BY supplier_id DESC LIMIT 1");
        $stmtMaxID->execute();
        $lastSupplierID = $stmtMaxID->fetchColumn();

        $newNumber = $lastSupplierID ? ((int)substr($lastSupplierID, 1)) + 1 : 1;
        $db_supplier_id = sprintf("S%05d", $newNumber);

        $stmtInsertSupplier = $conn->prepare("INSERT INTO ims_supplier (supplier_id, supplier_name) VALUES (?, ?)");
        if (!$stmtInsertSupplier->execute([$db_supplier_id, $supplier_name])) {
            $errorInfo = $stmtInsertSupplier->errorInfo();
            throw new Exception("Insert supplier failed: " . $errorInfo[2]);
        }
    }
    $supplier_id = $db_supplier_id;
} else {
    // ถ้าไม่มี supplier_name มา ให้กำหนดเป็น null
    $supplier_id = null;
    $supplier_name = null;
}


try {
    $conn->beginTransaction();

    $total_amount = 0;
    foreach ($details as $item) {
        // ตรวจสอบว่า quantity และ price เป็นตัวเลขหรือไม่
        if (!isset($item['quantity']) || !is_numeric($item['quantity'])) {
            throw new Exception("Missing or invalid quantity for detail item.");
        }
        if (!isset($item['price']) || !is_numeric($item['price'])) {
            throw new Exception("Missing or invalid price for detail item.");
        }

        // คำนวณ item_amount จาก quantity * price
        $calculated_item_amount = (float)$item['quantity'] * (float)$item['price'];

        // เพิ่มการตรวจสอบสำหรับค่าที่คำนวณได้
        if ($calculated_item_amount <= 0) {
            throw new Exception("Invalid amount for detail item: Calculated amount must be positive.");
        }

        $total_amount += $calculated_item_amount;
    }

    // Prepare payment method value to be saved
    $final_payment_method = $payment_method;
    if ($payment_method === 'โอนเงิน') {
        $final_payment_method = 'โอนเงิน (' . $bank_account . ')';
    }


    if ($action === 'ADD') {
        $month = substr($db_doc_date, 5, 2);
        $year = substr($db_doc_date, 0, 4);

        $stmtRunNo = $conn->prepare("SELECT doc_no FROM ims_payment_voucher WHERE doc_no LIKE ? ORDER BY doc_no DESC LIMIT 1");
        $stmtRunNo->execute(["PV-$month-$year-%"]);
        $lastDocNo = $stmtRunNo->fetchColumn();

        $newRunNo = $lastDocNo ? intval(substr($lastDocNo, strrpos($lastDocNo, '-') + 1)) + 1 : 1;
        $doc_no = sprintf("PV-%s-%s-%04d", $month, $year, $newRunNo);

        $stmt = $conn->prepare("INSERT INTO ims_payment_voucher
            (doc_no, doc_date, requester, supplier_id, supplier_name, purpose, total_amount, picture_doc, payment_method, create_name, checker_name, approve_name, receipt_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt->execute([$doc_no, $db_doc_date, $requester, $supplier_id, $supplier_name, $purpose, $total_amount, $picture_doc, $final_payment_method, $create_name, $checker_name, $approve_name, $receipt_name])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Insert master failed: " . $errorInfo[2]);
        }

        $stmtDetail = $conn->prepare("INSERT INTO ims_payment_voucher_items
            (doc_no, line_no, product_id, product_name, quantity, inv, price, unit_id, unit_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $line_no = 1;

        // --- Expense insertion preparation (for ADD) ---
        $expense_month = substr($db_doc_date, 5, 2);
        $expense_year = substr($db_doc_date, 0, 4);

        $stmtMaxExpenseRunNo = $conn->prepare("SELECT runno FROM ims_expenses WHERE doc_id LIKE ? ORDER BY runno DESC LIMIT 1");
        $stmtMaxExpenseRunNo->execute(["EXP-$expense_year-$expense_month-%"]);
        $lastExpenseRunNo = $stmtMaxExpenseRunNo->fetchColumn();
        $current_expense_runno = $lastExpenseRunNo ? (int)$lastExpenseRunNo : 0;
        // --- End Expense insertion preparation (for ADD) ---

        foreach ($details as $item) {
            $current_product_id = $item['product_id'] ?? '';
            $current_product_name = $item['product_name'] ?? '';

            // If product_id is empty, try to find or create the product
            if (empty($current_product_id) && !empty($current_product_name)) {
                // Check if product_name already exists in ims_products
                $stmtCheckProduct = $conn->prepare("SELECT product_id FROM ims_products WHERE product_name = ?");
                $stmtCheckProduct->execute([$current_product_name]);
                $found_product_id = $stmtCheckProduct->fetchColumn();

                if ($found_product_id) {
                    $current_product_id = $found_product_id;
                } else {
                    // Generate new product_id
                    $stmtMaxProductID = $conn->prepare("SELECT product_id FROM ims_products WHERE product_id LIKE 'P%' ORDER BY product_id DESC LIMIT 1");
                    $stmtMaxProductID->execute();
                    $lastProductID = $stmtMaxProductID->fetchColumn();

                    $newProductNumber = $lastProductID ? ((int)substr($lastProductID, 1)) + 1 : 1;
                    $current_product_id = sprintf("P%05d", $newProductNumber);

                    // Insert new product into ims_products (ไม่รวม create_date และ NOW())
                    $stmtInsertProduct = $conn->prepare("INSERT INTO ims_products (product_id, product_name) VALUES (?, ?)");
                    if (!$stmtInsertProduct->execute([$current_product_id, $current_product_name])) {
                        $errorInfo = $stmtInsertProduct->errorInfo();
                        throw new Exception("Insert new product failed: " . $errorInfo[2]);
                    }
                }
            }

            // ตรวจสอบความถูกต้องของข้อมูล item ก่อน execute
            if (empty($current_product_id) || empty($current_product_name) || !isset($item['quantity']) || !is_numeric($item['quantity']) || !isset($item['price']) || !is_numeric($item['price'])) {
                throw new Exception("Missing or invalid product details for line " . $line_no);
            }

            if (!$stmtDetail->execute([
                $doc_no,
                $line_no++,
                $current_product_id,
                $current_product_name,
                (float)$item['quantity'],
                $item['inv'] ?? null,
                (float)$item['price'],
                $item['unit_id'] ?? null,
                $item['unit_name'] ?? null,
            ])) {
                $errorInfo = $stmtDetail->errorInfo();
                throw new Exception("Insert detail failed: " . $errorInfo[2]);
            }

            // --- Insert into ims_expenses for each detail item ---
            $current_expense_runno++;
            $expense_doc_id = sprintf("EXP-%s-%s-%04d", $expense_year, $expense_month, $current_expense_runno);

            $stmtExpense = $conn->prepare("INSERT INTO ims_expenses
                (runno, doc_id, receipt_name, expense_date, exp_month, exp_year, inv, category_id, description, qty, unit_id, amount, remark, approve_status, file_attach, payment_method, price_per_unit, doc_ref)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if (!$stmtExpense->execute([
                $current_expense_runno,
                $expense_doc_id,
                $receipt_name,
                $db_doc_date,
                $expense_month,
                $expense_year,
                $item['inv'] ?? '-',
                null, // category_id - assuming NULL, can be changed if a default or input is provided
                $item['product_name'] ?? '',
                (float)$item['quantity'],
                $item['unit_id'] ?? null,
                (float)$item['quantity'] * (float)$item['price'],
                null, // remark
                'N', // approve_status
                $picture_doc,
                $final_payment_method,
                (float)$item['price'],
                $doc_no
            ])) {
                $errorInfo = $stmtExpense->errorInfo();
                throw new Exception("Insert into ims_expenses failed: " . $errorInfo[2]);
            }
            // --- End Insert into ims_expenses ---

        }

    } else if ($action === 'UPDATE') {
        if (empty($doc_no)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing doc_no for update']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE ims_payment_voucher
            SET doc_date = ?, requester = ?, supplier_id = ?, supplier_name = ?, purpose = ?, total_amount = ?, picture_doc = ?, payment_method = ?, create_name = ?, checker_name =?, approve_name =?, receipt_name =?
            WHERE doc_no = ?");
        if (!$stmt->execute([$db_doc_date, $requester, $supplier_id, $supplier_name, $purpose, $total_amount, $picture_doc, $final_payment_method, $create_name, $checker_name, $approve_name, $receipt_name, $doc_no])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Update master failed: " . $errorInfo[2]);
        }

        $stmtDelete = $conn->prepare("DELETE FROM ims_payment_voucher_items WHERE doc_no = ?");
        if (!$stmtDelete->execute([$doc_no])) {
            $errorInfo = $stmtDelete->errorInfo();
            throw new Exception("Delete details failed: " . $errorInfo[2]);
        }

        $stmtDetail = $conn->prepare("INSERT INTO ims_payment_voucher_items
            (doc_no, line_no, product_id, product_name, quantity, inv, price, unit_id, unit_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $line_no = 1;

        // --- Expense insertion preparation (for UPDATE) ---
        // For UPDATE, if existing expenses linked to this voucher should be cleared/updated,
        // that logic would go here. The current request is to "create running next",
        // implying new entries for expenses for each detail item upon update.
        // This might lead to duplicate expense entries if not handled on the front end or by user intent.
        // Assuming the current request implies always creating new expense records for each detail item being processed.
        $expense_month = substr($db_doc_date, 5, 2);
        $expense_year = substr($db_doc_date, 0, 4);

        $stmtMaxExpenseRunNo = $conn->prepare("SELECT runno FROM ims_expenses WHERE doc_id LIKE ? ORDER BY runno DESC LIMIT 1");
        $stmtMaxExpenseRunNo->execute(["EXP-$expense_year-$expense_month-%"]);
        $lastExpenseRunNo = $stmtMaxExpenseRunNo->fetchColumn();
        $current_expense_runno = $lastExpenseRunNo ? (int)$lastExpenseRunNo : 0;
        // --- End Expense insertion preparation (for UPDATE) ---

        foreach ($details as $item) {
            $current_product_id = $item['product_id'] ?? '';
            $current_product_name = $item['product_name'] ?? '';

            // If product_id is empty, try to find or create the product
            if (empty($current_product_id) && !empty($current_product_name)) {
                // Check if product_name already exists in ims_products
                $stmtCheckProduct = $conn->prepare("SELECT product_id FROM ims_products WHERE product_name = ?");
                $stmtCheckProduct->execute([$current_product_name]);
                $found_product_id = $stmtCheckProduct->fetchColumn();

                if ($found_product_id) {
                    $current_product_id = $found_product_id;
                } else {
                    // Generate new product_id
                    $stmtMaxProductID = $conn->prepare("SELECT product_id FROM ims_products WHERE product_id LIKE 'P%' ORDER BY product_id DESC LIMIT 1");
                    $stmtMaxProductID->execute();
                    $lastProductID = $stmtMaxProductID->fetchColumn();

                    $newProductNumber = $lastProductID ? ((int)substr($lastProductID, 1)) + 1 : 1;
                    $current_product_id = sprintf("P%05d", $newProductNumber);

                    // Insert new product into ims_products (ไม่รวม create_date และ NOW())
                    $stmtInsertProduct = $conn->prepare("INSERT INTO ims_products (product_id, product_name) VALUES (?, ?)");
                    if (!$stmtInsertProduct->execute([$current_product_id, $current_product_name])) {
                        $errorInfo = $stmtInsertProduct->errorInfo();
                        throw new Exception("Insert new product failed: " . $errorInfo[2]);
                    }
                }
            }

            // ตรวจสอบความถูกต้องของข้อมูล item ก่อน execute
            if (empty($current_product_id) || empty($current_product_name) || !isset($item['quantity']) || !is_numeric($item['quantity']) || !isset($item['price']) || !is_numeric($item['price'])) {
                throw new Exception("Missing or invalid product details for line " . $line_no);
            }

            if (!$stmtDetail->execute([
                $doc_no,
                $line_no++,
                $current_product_id,
                $current_product_name,
                (float)$item['quantity'],
                $item['inv'] ?? null,
                (float)$item['price'],
                $item['unit_id'] ?? null,
                $item['unit_name'] ?? null,
            ])) {
                $errorInfo = $stmtDetail->errorInfo();
                throw new Exception("Insert detail failed: " . $errorInfo[2]);
            }

            // --- Insert into ims_expenses for each detail item ---
            $current_expense_runno++;
            $expense_doc_id = sprintf("EXP-%s-%s-%04d", $expense_year, $expense_month, $current_expense_runno);

            $stmtExpense = $conn->prepare("INSERT INTO ims_expenses
                (runno, doc_id, receipt_name, expense_date, exp_month, exp_year, inv, category_id, description, qty, unit_id, amount, remark, approve_status, file_attach, payment_method, price_per_unit, doc_ref)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if (!$stmtExpense->execute([
                $current_expense_runno,
                $expense_doc_id,
                $receipt_name,
                $db_doc_date,
                $expense_month,
                $expense_year,
                $item['inv'] ?? '-',
                null, // category_id - assuming NULL, can be changed if a default or input is provided
                $item['product_name'] ?? '',
                (float)$item['quantity'],
                $item['unit_id'] ?? null,
                (float)$item['quantity'] * (float)$item['price'],
                null, // remark
                'N', // approve_status
                $picture_doc,
                $final_payment_method,
                (float)$item['price'],
                $doc_no
            ])) {
                $errorInfo = $stmtExpense->errorInfo();
                throw new Exception("Insert into ims_expenses failed: " . $errorInfo[2]);
            }
            // --- End Insert into ims_expenses ---
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'doc_no' => $doc_no]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}