<?php
header('Content-Type: application/json');
include('../config/connect_db.php');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

$action = $data['action'] ?? '';
$doc_no = $data['doc_no'] ?? '';
$doc_date = $data['date'] ?? '';
$requester = $data['requester'] ?? '';
$purpose = $data['purpose'] ?? '';
$details = $data['details'] ?? [];

if (!$doc_no || !$doc_date || !$requester) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

try {
    $conn->beginTransaction();

    if ($action === 'ADD') {
        $stmt = $conn->prepare("INSERT INTO ims_purchase_a_master (doc_no, doc_date, requester, purpose) VALUES (?, ?, ?, ?)");
        $stmt->execute([$doc_no, $doc_date, $requester, $purpose]);

        $line_no = 1;
        $stmtDetail = $conn->prepare("INSERT INTO ims_purchase_b_detail (doc_no, line_no, product_id, product_name, quantity, price, unit_id, unit_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($details as $item) {
            $stmtDetail->execute([
                $doc_no,
                $line_no++,
                $item['product_id'],
                $item['product_name'],
                $item['quantity'],
                $item['price'],
                $item['unit_id'],
                $item['unit_name'],
            ]);
        }
    } else if ($action === 'UPDATE') {
        $stmt = $conn->prepare("UPDATE ims_purchase_a_master SET doc_date = ?, requester = ?, purpose = ? WHERE doc_no = ?");
        $stmt->execute([$doc_date, $requester, $purpose, $doc_no]);

        $stmtDelete = $conn->prepare("DELETE FROM ims_purchase_b_detail WHERE doc_no = ?");
        $stmtDelete->execute([$doc_no]);

        $line_no = 1;
        $stmtDetail = $conn->prepare("INSERT INTO ims_purchase_b_detail (doc_no, line_no, product_id, product_name, quantity, price, unit_id, unit_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($details as $item) {
            $stmtDetail->execute([
                $doc_no,
                $line_no++,
                $item['product_id'],
                $item['product_name'],
                $item['quantity'],
                $item['price'],
                $item['unit_id'],
                $item['unit_name'],
            ]);
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
