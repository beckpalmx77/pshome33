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
$doc_date = $data['date'] ?? '';  // รับตรงแบบ DD-MM-YYYY
$requester = $data['requester'] ?? '';
$purpose = $data['purpose'] ?? '';
$details = $data['details'] ?? [];

if (!in_array($action, ['ADD', 'UPDATE'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing action']);
    exit;
}

if (!$doc_date || !$requester) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

try {
    $conn->beginTransaction();

    $total_amount = 0;
    foreach ($details as $item) {
        $total_amount += $item['quantity'] * $item['price'];
    }

    if ($action === 'ADD') {
        $month = substr($doc_date, 3, 2);
        $year = substr($doc_date, 6, 4);

        $stmtRunNo = $conn->prepare("
            SELECT doc_no FROM ims_purchase_a_master
            WHERE doc_no LIKE ?
            ORDER BY doc_no DESC
            LIMIT 1
        ");
        $like_pattern = "PR-$month-$year-%";
        $stmtRunNo->execute([$like_pattern]);
        $lastDocNo = $stmtRunNo->fetchColumn();

        if ($lastDocNo) {
            $parts = explode('-', $lastDocNo);
            $lastRunNo = intval(end($parts));
            $newRunNo = $lastRunNo + 1;
        } else {
            $newRunNo = 1;
        }

        $doc_no = sprintf("PR-%s-%s-%04d", $month, $year, $newRunNo);

        $stmt = $conn->prepare("INSERT INTO ims_purchase_a_master (doc_no, doc_date, requester, purpose, total_amount) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt->execute([$doc_no, $doc_date, $requester, $purpose, $total_amount])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Insert master failed: ".$errorInfo[2]);
        }

        $line_no = 1;
        $stmtDetail = $conn->prepare("INSERT INTO ims_purchase_b_detail (doc_no, line_no, product_id, product_name, quantity, price, unit_id, unit_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($details as $item) {
            if (!$stmtDetail->execute([
                $doc_no,
                $line_no++,
                $item['product_id'],
                $item['product_name'],
                $item['quantity'],
                $item['price'],
                $item['unit_id'],
                $item['unit_name'],
            ])) {
                $errorInfo = $stmtDetail->errorInfo();
                throw new Exception("Insert detail failed: ".$errorInfo[2]);
            }
        }

    } else if ($action === 'UPDATE') {
        if (empty($doc_no)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing doc_no for update']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE ims_purchase_a_master SET doc_date = ?, requester = ?, purpose = ?, total_amount = ? WHERE doc_no = ?");
        if (!$stmt->execute([$doc_date, $requester, $purpose, $total_amount, $doc_no])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Update master failed: ".$errorInfo[2]);
        }

        $stmtDelete = $conn->prepare("DELETE FROM ims_purchase_b_detail WHERE doc_no = ?");
        if (!$stmtDelete->execute([$doc_no])) {
            $errorInfo = $stmtDelete->errorInfo();
            throw new Exception("Delete details failed: ".$errorInfo[2]);
        }

        $line_no = 1;
        $stmtDetail = $conn->prepare("INSERT INTO ims_purchase_b_detail (doc_no, line_no, product_id, product_name, quantity, price, unit_id, unit_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($details as $item) {
            if (!$stmtDetail->execute([
                $doc_no,
                $line_no++,
                $item['product_id'],
                $item['product_name'],
                $item['quantity'],
                $item['price'],
                $item['unit_id'],
                $item['unit_name'],
            ])) {
                $errorInfo = $stmtDetail->errorInfo();
                throw new Exception("Insert detail failed: ".$errorInfo[2]);
            }
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'doc_no' => $doc_no]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
