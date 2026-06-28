<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');

if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();

    $sql_get = "SELECT * FROM ims_bank_account WHERE id = :id";
    $statement = $conn->prepare($sql_get);
    $statement->execute(['id' => $id]);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array(
            "id" => $result['id'],
            "account_code" => $result['account_code'],
            "account_name" => $result['account_name'],
            "bank_name" => $result['bank_name'],
            "account_no" => $result['account_no'],
            "branch_name" => $result['branch_name'],
            "opening_balance" => $result['opening_balance'],
            "opening_date" => date("d-m-Y", strtotime($result['opening_date'])),
            "status" => $result['status']
        );
    }

    echo json_encode($return_arr);
    exit;
}

if ($_POST["action"] === 'ADD') {
    $account_code = $_POST["account_code"];
    $account_name = $_POST["account_name"];
    $bank_name = $_POST["bank_name"];
    $account_no = $_POST["account_no"];
    $branch_name = $_POST["branch_name"] ?? '';
    $opening_balance = floatval($_POST["opening_balance"] ?? 0);
    $opening_date = date("Y-m-d", strtotime($_POST["opening_date"]));
    $status = $_POST["status"];

    // Check duplicate
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ims_bank_account WHERE account_code = ?");
    $stmt->execute([$account_code]);
    if ($stmt->fetchColumn() > 0) {
        echo "รหัสบัญชีซ้ำในระบบ";
        exit;
    }

    try {
        $conn->beginTransaction();

        // Insert bank account
        $sql = "INSERT INTO ims_bank_account (account_code, account_name, bank_name, account_no, branch_name, opening_balance, opening_date, current_balance, status, created_by) 
                VALUES (:account_code, :account_name, :bank_name, :account_no, :branch_name, :opening_balance, :opening_date, :current_balance, :status, :created_by)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'account_code' => $account_code,
            'account_name' => $account_name,
            'bank_name' => $bank_name,
            'account_no' => $account_no,
            'branch_name' => $branch_name,
            'opening_balance' => $opening_balance,
            'opening_date' => $opening_date,
            'current_balance' => $opening_balance,
            'status' => $status,
            'created_by' => $_SESSION['alogin'] ?? 'SYSTEM'
        ]);

        $bank_account_id = $conn->lastInsertId();

        // If opening balance > 0, insert opening transaction
        if ($opening_balance > 0) {
            $doc_no = "OP-" . date("Ymd", strtotime($opening_date)) . "-" . sprintf("%04d", $bank_account_id);
            $sql_txn = "INSERT INTO ims_bank_transaction (bank_account_id, doc_no, transaction_date, transaction_type, amount, description, status, created_by) 
                        VALUES (:bank_account_id, :doc_no, :transaction_date, 'DEPOSIT', :amount, 'ยอดยกมาเริ่มต้นบัญชี', 'Y', :created_by)";
            $stmt_txn = $conn->prepare($sql_txn);
            $stmt_txn->execute([
                'bank_account_id' => $bank_account_id,
                'doc_no' => $doc_no,
                'transaction_date' => $opening_date . ' 00:00:00',
                'amount' => $opening_balance,
                'created_by' => $_SESSION['alogin'] ?? 'SYSTEM'
            ]);
        }

        $conn->commit();
        echo "บันทึกข้อมูลบัญชีธนาคารเรียบร้อยแล้ว";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    exit;
}

if ($_POST["action"] === 'UPDATE') {
    $id = $_POST["id"];
    $account_name = $_POST["account_name"];
    $bank_name = $_POST["bank_name"];
    $account_no = $_POST["account_no"];
    $branch_name = $_POST["branch_name"] ?? '';
    $status = $_POST["status"];

    try {
        $sql = "UPDATE ims_bank_account SET 
                account_name = :account_name, 
                bank_name = :bank_name, 
                account_no = :account_no, 
                branch_name = :branch_name, 
                status = :status, 
                updated_by = :updated_by 
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'account_name' => $account_name,
            'bank_name' => $bank_name,
            'account_no' => $account_no,
            'branch_name' => $branch_name,
            'status' => $status,
            'updated_by' => $_SESSION['alogin'] ?? 'SYSTEM',
            'id' => $id
        ]);
        echo "แก้ไขข้อมูลบัญชีธนาคารเรียบร้อยแล้ว";
    } catch (Exception $e) {
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    exit;
}

if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];

    // Check if there are transactions associated
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ims_bank_transaction WHERE bank_account_id = ? AND doc_no NOT LIKE 'OP-%'");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        echo "ไม่สามารถลบบัญชีนี้ได้ เนื่องจากมีการบันทึกธุรกรรมในระบบแล้ว";
        exit;
    }

    try {
        $conn->beginTransaction();

        // Delete opening transaction first
        $stmt_del_txn = $conn->prepare("DELETE FROM ims_bank_transaction WHERE bank_account_id = ?");
        $stmt_del_txn->execute([$id]);

        // Delete bank account
        $stmt_del_acc = $conn->prepare("DELETE FROM ims_bank_account WHERE id = ?");
        $stmt_del_acc->execute([$id]);

        $conn->commit();
        echo "ลบข้อมูลบัญชีธนาคารเรียบร้อยแล้ว";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    exit;
}

if ($_POST["action"] === 'GET_ACCOUNTS') {
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length'];
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'] ?? 'id';
    $columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
    $searchValue = $_POST['search']['value'] ?? '';

    $searchQuery = " ";
    $searchArray = array();
    if ($searchValue != '') {
        $searchQuery = " AND (account_code LIKE :search1 OR account_name LIKE :search2 OR bank_name LIKE :search3 OR account_no LIKE :search4) ";
        $searchArray = [
            'search1' => "%$searchValue%",
            'search2' => "%$searchValue%",
            'search3' => "%$searchValue%",
            'search4' => "%$searchValue%"
        ];
    }

    // Filter query validation for column name to prevent SQL Injection
    $validColumns = ['account_code', 'account_name', 'bank_name', 'account_no', 'branch_name', 'opening_balance', 'opening_date', 'current_balance', 'status'];
    if (!in_array($columnName, $validColumns)) {
        $columnName = 'id';
    }

    // Count without filtering
    $sql_count = "SELECT COUNT(*) AS allcount FROM ims_bank_account";
    $stmt = $conn->prepare($sql_count);
    $stmt->execute();
    $totalRecords = $stmt->fetch()['allcount'];

    // Count with filtering
    $sql_count_filter = "SELECT COUNT(*) AS allcount FROM ims_bank_account WHERE 1=1 " . $searchQuery;
    $stmt = $conn->prepare($sql_count_filter);
    $stmt->execute($searchArray);
    $totalRecordwithFilter = $stmt->fetch()['allcount'];

    // Fetch data
    $sql_data = "SELECT * FROM ims_bank_account WHERE 1=1 " . $searchQuery . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit, :offset";
    $stmt = $conn->prepare($sql_data);
    
    foreach ($searchArray as $key => $val) {
        $stmt->bindValue(':' . $key, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array();
    foreach ($records as $record) {
        $status_badge = $record['status'] === 'Y' 
            ? "<span class='badge badge-success'>ใช้งาน (Active)</span>" 
            : "<span class='badge badge-secondary'>ไม่ใช้งาน (Inactive)</span>";

        $data[] = array(
            "account_code" => $record['account_code'],
            "account_name" => $record['account_name'],
            "bank_name" => $record['bank_name'],
            "account_no" => $record['account_no'],
            "branch_name" => $record['branch_name'],
            "opening_balance" => number_format($record['opening_balance'], 2),
            "opening_date" => date("d-m-Y", strtotime($record['opening_date'])),
            "current_balance" => "<b>" . number_format($record['current_balance'], 2) . "</b>",
            "status" => $status_badge,
            "update" => "<button type='button' id='{$record['id']}' class='btn btn-info btn-xs update'><i class='fa fa-pencil'></i> Edit</button>",
            "delete" => "<button type='button' id='{$record['id']}' class='btn btn-danger btn-xs delete'><i class='fa fa-trash'></i> Delete</button>"
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
