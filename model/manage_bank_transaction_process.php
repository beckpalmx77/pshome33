<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');

if ($_POST["action"] === 'GET_DATA') {
    $id = intval($_POST["id"]);
    $return_arr = array();

    $sql_get = "SELECT * FROM ims_bank_transaction WHERE id = :id";
    $statement = $conn->prepare($sql_get);
    $statement->execute(['id' => $id]);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array(
            "id" => $result['id'],
            "bank_account_id" => $result['bank_account_id'],
            "transaction_date" => date("d-m-Y H:i", strtotime($result['transaction_date'])),
            "transaction_date_raw" => date("Y-m-d H:i", strtotime($result['transaction_date'])),
            "transaction_type" => $result['transaction_type'],
            "amount" => $result['amount'],
            "fee" => $result['fee'],
            "ref_no" => $result['ref_no'],
            "picture_slip" => $result['picture_slip'],
            "description" => $result['description']
        );
    }

    echo json_encode($return_arr);
    exit;
}

// Define transaction type labels
$txn_types = [
    'DEPOSIT' => '<span class="badge badge-success">เงินฝาก (Deposit)</span>',
    'WITHDRAW' => '<span class="badge badge-danger">เงินถอน (Withdrawal)</span>',
    'INTEREST' => '<span class="badge badge-info">ดอกเบี้ยรับ (Interest)</span>',
    'FEE' => '<span class="badge badge-warning">ค่าธรรมเนียม (Fee)</span>',
    'ADJUST_ADD' => '<span class="badge badge-primary">ปรับปรุงเพิ่ม (+)</span>',
    'ADJUST_SUB' => '<span class="badge badge-secondary">ปรับปรุงลด (-)</span>'
];

if ($_POST["action"] === 'ADD') {
    $bank_account_id = intval($_POST["bank_account_id"]);
    $transaction_type = $_POST["transaction_type"];
    $amount = floatval($_POST["amount"]);
    $fee = floatval($_POST["fee"] ?? 0);
    $ref_no = $_POST["ref_no"] ?? '';
    $description = $_POST["description"] ?? '';
    
    // Parse datetime from dd-mm-yyyy hh:ii
    $date_parts = explode(' ', $_POST["transaction_date"]);
    $date_part = date("Y-m-d", strtotime($date_parts[0]));
    $time_part = $date_parts[1] ?? '00:00';
    $transaction_date = $date_part . ' ' . $time_part . ':00';

    // File Upload for Slip
    $picture_slip = null;
    if (isset($_FILES['picture_slip']) && $_FILES['picture_slip']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['picture_slip']['tmp_name'];
        $file_name = $_FILES['picture_slip']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
        if (in_array($file_ext, $allowed_exts)) {
            $upload_dir = '../uploads/slip_bank/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_file_name = 'SLIP_' . date('Ymd_His') . '_' . uniqid() . '.' . $file_ext;
            if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                $picture_slip = 'uploads/slip_bank/' . $new_file_name;
            }
        }
    }

    try {
        $conn->beginTransaction();

        // 1. Generate doc_no (Format: TXN-YYYYMM-XXXX)
        $year_month = date("Ym", strtotime($date_part));
        $prefix = "TXN-" . $year_month . "-";
        
        $stmt_doc = $conn->prepare("SELECT MAX(doc_no) FROM ims_bank_transaction WHERE doc_no LIKE ?");
        $stmt_doc->execute([$prefix . '%']);
        $last_doc = $stmt_doc->fetchColumn();
        
        $next_seq = 1;
        if ($last_doc) {
            $parts = explode('-', $last_doc);
            $last_seq = (int)end($parts);
            $next_seq = $last_seq + 1;
        }
        $doc_no = $prefix . sprintf('%04d', $next_seq);

        // 2. Insert transaction
        $sql_insert = "INSERT INTO ims_bank_transaction (bank_account_id, doc_no, transaction_date, transaction_type, amount, fee, ref_no, description, picture_slip, status, created_by) 
                       VALUES (:bank_account_id, :doc_no, :transaction_date, :transaction_type, :amount, :fee, :ref_no, :description, :picture_slip, 'Y', :created_by)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([
            'bank_account_id' => $bank_account_id,
            'doc_no' => $doc_no,
            'transaction_date' => $transaction_date,
            'transaction_type' => $transaction_type,
            'amount' => $amount,
            'fee' => $fee,
            'ref_no' => $ref_no,
            'description' => $description,
            'picture_slip' => $picture_slip,
            'created_by' => $_SESSION['alogin'] ?? 'SYSTEM'
        ]);

        // 3. Update current balance of bank account
        // Calculate net transaction impact:
        // DEPOSIT, INTEREST, ADJUST_ADD: +amount
        // WITHDRAW: -(amount + fee)
        // FEE, ADJUST_SUB: -amount
        $net_impact = 0;
        if (in_array($transaction_type, ['DEPOSIT', 'INTEREST', 'ADJUST_ADD'])) {
            $net_impact = $amount;
        } elseif ($transaction_type === 'WITHDRAW') {
            $net_impact = -($amount + $fee);
        } elseif (in_array($transaction_type, ['FEE', 'ADJUST_SUB'])) {
            $net_impact = -$amount;
        }

        $sql_update_bal = "UPDATE ims_bank_account SET current_balance = current_balance + :net_impact WHERE id = :id";
        $stmt_update_bal = $conn->prepare($sql_update_bal);
        $stmt_update_bal->execute([
            'net_impact' => $net_impact,
            'id' => $bank_account_id
        ]);

        $conn->commit();
        echo "บันทึกรายการธุรกรรมสำเร็จ";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    exit;
}

if ($_POST["action"] === 'DELETE') {
    $id = intval($_POST["id"]);

    try {
        $conn->beginTransaction();

        // 1. Fetch transaction details to adjust balance back
        $stmt_txn = $conn->prepare("SELECT bank_account_id, transaction_type, amount, fee FROM ims_bank_transaction WHERE id = ?");
        $stmt_txn->execute([$id]);
        $txn = $stmt_txn->fetch(PDO::FETCH_ASSOC);

        if ($txn) {
            $bank_account_id = $txn['bank_account_id'];
            $transaction_type = $txn['transaction_type'];
            $amount = floatval($txn['amount']);
            $fee = floatval($txn['fee']);

            // Reverse the impact:
            // DEPOSIT, INTEREST, ADJUST_ADD: -amount
            // WITHDRAW: +(amount + fee)
            // FEE, ADJUST_SUB: +amount
            $reverse_impact = 0;
            if (in_array($transaction_type, ['DEPOSIT', 'INTEREST', 'ADJUST_ADD'])) {
                $reverse_impact = -$amount;
            } elseif ($transaction_type === 'WITHDRAW') {
                $reverse_impact = ($amount + $fee);
            } elseif (in_array($transaction_type, ['FEE', 'ADJUST_SUB'])) {
                $reverse_impact = $amount;
            }

            // Update account balance
            $sql_update_bal = "UPDATE ims_bank_account SET current_balance = current_balance + :reverse_impact WHERE id = :id";
            $stmt_update_bal = $conn->prepare($sql_update_bal);
            $stmt_update_bal->execute([
                'reverse_impact' => $reverse_impact,
                'id' => $bank_account_id
            ]);

            // 2. Delete transaction record
            $stmt_del = $conn->prepare("DELETE FROM ims_bank_transaction WHERE id = ?");
            $stmt_del->execute([$id]);
        }

        $conn->commit();
        echo "ยกเลิกและลบรายการธุรกรรมเรียบร้อยแล้ว";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    exit;
}

if ($_POST["action"] === 'UPDATE') {
    $id = intval($_POST["id"]);
    $bank_account_id = intval($_POST["bank_account_id"]);
    $transaction_type = $_POST["transaction_type"];
    $amount = floatval($_POST["amount"]);
    $fee = floatval($_POST["fee"] ?? 0);
    $ref_no = $_POST["ref_no"] ?? '';
    $description = $_POST["description"] ?? '';
    
    // Parse datetime from dd-mm-yyyy hh:ii
    $date_parts = explode(' ', $_POST["transaction_date"]);
    $date_part = date("Y-m-d", strtotime($date_parts[0]));
    $time_part = $date_parts[1] ?? '00:00';
    $transaction_date = $date_part . ' ' . $time_part . ':00';

    try {
        $conn->beginTransaction();

        // 1. Fetch old transaction details
        $stmt_old = $conn->prepare("SELECT bank_account_id, transaction_type, amount, fee, picture_slip FROM ims_bank_transaction WHERE id = ?");
        $stmt_old->execute([$id]);
        $old_txn = $stmt_old->fetch(PDO::FETCH_ASSOC);

        if (!$old_txn) {
            throw new Exception("ไม่พบรายการธุรกรรมที่ต้องการแก้ไข");
        }

        $old_bank_account_id = $old_txn['bank_account_id'];
        $old_type = $old_txn['transaction_type'];
        $old_amount = floatval($old_txn['amount']);
        $old_fee = floatval($old_txn['fee']);
        $picture_slip = $old_txn['picture_slip'];

        // File Upload for Slip (if new file is uploaded)
        if (isset($_FILES['picture_slip']) && $_FILES['picture_slip']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['picture_slip']['tmp_name'];
            $file_name = $_FILES['picture_slip']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
            if (in_array($file_ext, $allowed_exts)) {
                $upload_dir = '../uploads/slip_bank/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_file_name = 'SLIP_' . date('Ymd_His') . '_' . uniqid() . '.' . $file_ext;
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    $picture_slip = 'uploads/slip_bank/' . $new_file_name;
                }
            }
        }

        // 2. Reverse the old transaction balance impact from the old bank account
        $reverse_impact = 0;
        if (in_array($old_type, ['DEPOSIT', 'INTEREST', 'ADJUST_ADD'])) {
            $reverse_impact = -$old_amount;
        } elseif ($old_type === 'WITHDRAW') {
            $reverse_impact = ($old_amount + $old_fee);
        } elseif (in_array($old_type, ['FEE', 'ADJUST_SUB'])) {
            $reverse_impact = $old_amount;
        }

        $sql_reverse_bal = "UPDATE ims_bank_account SET current_balance = current_balance + :reverse_impact WHERE id = :id";
        $stmt_reverse_bal = $conn->prepare($sql_reverse_bal);
        $stmt_reverse_bal->execute([
            'reverse_impact' => $reverse_impact,
            'id' => $old_bank_account_id
        ]);

        // 3. Apply the new transaction balance impact to the new bank account
        $new_impact = 0;
        if (in_array($transaction_type, ['DEPOSIT', 'INTEREST', 'ADJUST_ADD'])) {
            $new_impact = $amount;
        } elseif ($transaction_type === 'WITHDRAW') {
            $new_impact = -($amount + $fee);
        } elseif (in_array($transaction_type, ['FEE', 'ADJUST_SUB'])) {
            $new_impact = -$amount;
        }

        $sql_new_bal = "UPDATE ims_bank_account SET current_balance = current_balance + :new_impact WHERE id = :id";
        $stmt_new_bal = $conn->prepare($sql_new_bal);
        $stmt_new_bal->execute([
            'new_impact' => $new_impact,
            'id' => $bank_account_id
        ]);

        // 4. Update the transaction record
        $sql_update = "UPDATE ims_bank_transaction SET 
                       bank_account_id = :bank_account_id, 
                       transaction_date = :transaction_date, 
                       transaction_type = :transaction_type, 
                       amount = :amount, 
                       fee = :fee, 
                       ref_no = :ref_no, 
                       description = :description, 
                       picture_slip = :picture_slip, 
                       updated_by = :updated_by 
                       WHERE id = :id";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([
            'bank_account_id' => $bank_account_id,
            'transaction_date' => $transaction_date,
            'transaction_type' => $transaction_type,
            'amount' => $amount,
            'fee' => $fee,
            'ref_no' => $ref_no,
            'description' => $description,
            'picture_slip' => $picture_slip,
            'updated_by' => $_SESSION['alogin'] ?? 'SYSTEM',
            'id' => $id
        ]);

        $conn->commit();
        echo "แก้ไขรายการธุรกรรมสำเร็จ";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    exit;
}

if ($_POST["action"] === 'GET_TRANSACTIONS') {
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length'];
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'] ?? 'transaction_date';
    $columnSortOrder = $_POST['order'][0]['dir'] ?? 'desc';
    $searchValue = $_POST['search']['value'] ?? '';

    $thai_months = [
        '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน',
        '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม',
        '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
    ];

    // Filters
    $filter_account = $_POST['filter_account'] ?? '';
    $filter_start_date = $_POST['filter_start_date'] ?? '';
    $filter_end_date = $_POST['filter_end_date'] ?? '';

    $whereSQL = " WHERE 1=1 ";
    $params = [];

    if ($filter_account != '') {
        $whereSQL .= " AND t.bank_account_id = :filter_account ";
        $params['filter_account'] = intval($filter_account);
    }
    if ($filter_start_date != '') {
        $whereSQL .= " AND t.transaction_date >= :filter_start_date ";
        $params['filter_start_date'] = date("Y-m-d 00:00:00", strtotime($filter_start_date));
    }
    if ($filter_end_date != '') {
        $whereSQL .= " AND t.transaction_date <= :filter_end_date ";
        $params['filter_end_date'] = date("Y-m-d 23:59:59", strtotime($filter_end_date));
    }

    if ($searchValue != '') {
        $whereSQL .= " AND (t.doc_no LIKE :search1 OR t.ref_no LIKE :search2 OR t.description LIKE :search3 OR a.account_name LIKE :search4) ";
        $params['search1'] = "%$searchValue%";
        $params['search2'] = "%$searchValue%";
        $params['search3'] = "%$searchValue%";
        $params['search4'] = "%$searchValue%";
    }

    // Sort column sanitization
    $validColumns = [
        'transaction_date' => 't.transaction_date',
        'doc_no' => 't.doc_no',
        'ref_no' => 't.ref_no'
    ];
    $sortCol = $validColumns[$columnName] ?? 't.transaction_date';

    // Count Total Records (no filters)
    $stmt_tot = $conn->prepare("SELECT COUNT(*) AS cnt FROM ims_bank_transaction");
    $stmt_tot->execute();
    $totalRecords = $stmt_tot->fetch()['cnt'];

    // Count Filtered Records
    $sql_count_filter = "SELECT COUNT(*) AS cnt FROM ims_bank_transaction t 
                         LEFT JOIN ims_bank_account a ON t.bank_account_id = a.id " . $whereSQL;
    $stmt_filter = $conn->prepare($sql_count_filter);
    $stmt_filter->execute($params);
    $totalRecordwithFilter = $stmt_filter->fetch()['cnt'];

    // Fetch Records with Running Balance Subquery
    // Running balance matches transaction_date and id as tie-breaker
    $sql_data = "SELECT t.*, a.account_name, a.bank_name, a.account_no, a.opening_balance,
                 (a.opening_balance + COALESCE((
                     SELECT SUM(
                         CASE 
                             WHEN t2.transaction_type IN ('DEPOSIT', 'INTEREST', 'ADJUST_ADD') THEN t2.amount 
                             WHEN t2.transaction_type = 'WITHDRAW' THEN -(t2.amount + t2.fee)
                             WHEN t2.transaction_type IN ('FEE', 'ADJUST_SUB') THEN -t2.amount 
                             ELSE 0 
                         END
                     ) 
                     FROM ims_bank_transaction t2 
                     WHERE t2.bank_account_id = t.bank_account_id 
                       AND t2.status = 'Y' 
                       AND (t2.transaction_date < t.transaction_date OR (t2.transaction_date = t.transaction_date AND t2.id <= t.id))
                 ), 0)) AS running_balance
                 FROM ims_bank_transaction t
                 LEFT JOIN ims_bank_account a ON t.bank_account_id = a.id 
                 " . $whereSQL . " 
                 ORDER BY " . $sortCol . " " . $columnSortOrder . " 
                 LIMIT :limit, :offset";

    $stmt_data = $conn->prepare($sql_data);
    foreach ($params as $key => $val) {
        $stmt_data->bindValue(':' . $key, $val);
    }
    $stmt_data->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt_data->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt_data->execute();
    $records = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

    $data = array();
    foreach ($records as $record) {
        $type = $record['transaction_type'];
        
        $deposit_str = '-';
        $withdraw_str = '-';
        
        if (in_array($type, ['DEPOSIT', 'INTEREST', 'ADJUST_ADD'])) {
            $deposit_str = "<span class='text-success'><b>+" . number_format($record['amount'], 2) . "</b></span>";
        } elseif ($type === 'WITHDRAW') {
            $withdraw_str = "<span class='text-danger'><b>-" . number_format($record['amount'], 2) . "</b></span>";
        } elseif (in_array($type, ['FEE', 'ADJUST_SUB'])) {
            $withdraw_str = "<span class='text-danger'><b>-" . number_format($record['amount'], 2) . "</b></span>";
        }

        $fee_str = $record['fee'] > 0 ? number_format($record['fee'], 2) : '-';

        $slip_link = '-';
        if ($record['picture_slip']) {
            $slip_link = "<button type='button' class='btn btn-outline-info btn-xs view-slip' data-url='{$record['picture_slip']}'><i class='fa fa-image'></i> ดูหลักฐาน</button>";
        }

        $data[] = array(
            "transaction_date" => date("d-m-Y H:i", strtotime($record['transaction_date'])),
            "txn_month" => $thai_months[date("m", strtotime($record['transaction_date']))] ?? date("m", strtotime($record['transaction_date'])),
            "txn_year" => intval(date("Y", strtotime($record['transaction_date']))) + 543,
            "doc_no" => $record['doc_no'],
            "bank_account" => "{$record['bank_name']} <br><small class='text-muted'>{$record['account_no']} ({$record['account_name']})</small>",
            "transaction_type" => $txn_types[$type] ?? $type,
            "deposit_amount" => $deposit_str,
            "withdraw_amount" => $withdraw_str,
            "fee" => $fee_str,
            "running_balance" => "<b>" . number_format($record['running_balance'], 2) . "</b>",
            "ref_no" => $record['ref_no'] ?: '-',
            "picture_slip" => $slip_link,
            "description" => $record['description'] ?: '-',
            "update" => "<button type='button' id='{$record['id']}' class='btn btn-info btn-xs update'><i class='fa fa-pencil'></i> Edit</button>",
            "delete" => "<button type='button' id='{$record['id']}' class='btn btn-danger btn-xs delete'><i class='fa fa-times'></i> Cancel</button>"
        );
    }

    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
    exit;
}
