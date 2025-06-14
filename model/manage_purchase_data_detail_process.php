<?php
header('Content-Type: application/json');
include('../config/connect_db.php');

function writeLog($message) {
    $time = date('[Y-m-d H:i:s] ');
    file_put_contents('debug.log', $time . $message . PHP_EOL, FILE_APPEND);
}

writeLog("DB connection OK");

// รับข้อมูล JSON จาก POST
$data = json_decode(file_get_contents('php://input'), true);

writeLog("Received data: " . json_encode($data));

if (!$data) {
    writeLog("Invalid input data");
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

$action = $data['action'] ?? '';
$doc_no = $data['doc_no'] ?? '';
$doc_date = $data['date'] ?? '';  // รับตรงแบบ DD-MM-YYYY
$requester = $data['requester'] ?? '';
$purpose = $data['purpose'] ?? '';
$details = $data['details'] ?? [];

writeLog("Action: '$action'");

if (!in_array($action, ['ADD', 'UPDATE'])) {
    writeLog("Invalid or missing action");
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing action']);
    exit;
}

if (!$doc_date || !$requester) {
    writeLog("Missing required fields: doc_date or requester");
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

writeLog("Details count: " . count($details));

try {
    $conn->beginTransaction();

    $total_amount = 0;
    foreach ($details as $item) {
        $total_amount += $item['quantity'] * $item['price'];
    }
    writeLog("Calculated total_amount: $total_amount");

    if ($action === 'ADD') {
        // สร้าง doc_no ใหม่ในรูปแบบ PR-MM-YYYY-xxxx
        // ใช้ substring ตัดปีจากวันที่ (DD-MM-YYYY)
        $month = substr($doc_date, 3, 2);  // ตัดเดือน (ตำแหน่ง 3 ถึง 4)
        $year = substr($doc_date, 6, 4);   // ตัดปี (ตำแหน่ง 6 ถึง 9)

        // หาเลข running ล่าสุดในเดือน-ปีเดียวกัน
        $stmtRunNo = $conn->prepare("
            SELECT doc_no FROM ims_purchase_a_master
            WHERE doc_no LIKE ?
            ORDER BY doc_no DESC
            LIMIT 1
        ");
        $like_pattern = "PR-$month-$year-%";
        $stmtRunNo->execute([$like_pattern]);
        $lastDocNo = $stmtRunNo->fetchColumn();

        writeLog("Last doc_no for pattern '$like_pattern': $lastDocNo");

        if ($lastDocNo) {
            $parts = explode('-', $lastDocNo);
            $lastRunNo = intval(end($parts));
            $newRunNo = $lastRunNo + 1;
        } else {
            $newRunNo = 1;
        }

        $doc_no = sprintf("PR-%s-%s-%04d", $month, $year, $newRunNo);
        writeLog("Generated new doc_no: $doc_no");

        // Insert master
        $stmt = $conn->prepare("INSERT INTO ims_purchase_a_master (doc_no, doc_date, requester, purpose, total_amount) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt->execute([$doc_no, $doc_date, $requester, $purpose, $total_amount])) {
            $errorInfo = $stmt->errorInfo();
            writeLog("Insert master failed: " . $errorInfo[2]);
            throw new Exception("Insert master failed: ".$errorInfo[2]);
        }
        writeLog("Insert master successful for doc_no: $doc_no");

        // Insert details
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
                writeLog("Insert detail failed: " . $errorInfo[2]);
                throw new Exception("Insert detail failed: ".$errorInfo[2]);
            }
        }
        writeLog("Insert details successful for doc_no: $doc_no");

    } else if ($action === 'UPDATE') {
        if (empty($doc_no)) {
            writeLog("Missing doc_no for UPDATE");
            echo json_encode(['status' => 'error', 'message' => 'Missing doc_no for update']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE ims_purchase_a_master SET doc_date = ?, requester = ?, purpose = ?, total_amount = ? WHERE doc_no = ?");
        if (!$stmt->execute([$doc_date, $requester, $purpose, $total_amount, $doc_no])) {
            $errorInfo = $stmt->errorInfo();
            writeLog("Update master failed: " . $errorInfo[2]);
            throw new Exception("Update master failed: ".$errorInfo[2]);
        }
        writeLog("Update master successful for doc_no: $doc_no");

        $stmtDelete = $conn->prepare("DELETE FROM ims_purchase_b_detail WHERE doc_no = ?");
        if (!$stmtDelete->execute([$doc_no])) {
            $errorInfo = $stmtDelete->errorInfo();
            writeLog("Delete details failed: " . $errorInfo[2]);
            throw new Exception("Delete details failed: ".$errorInfo[2]);
        }
        writeLog("Deleted existing details for doc_no: $doc_no");

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
                writeLog("Insert detail failed: " . $errorInfo[2]);
                throw new Exception("Insert detail failed: ".$errorInfo[2]);
            }
        }
        writeLog("Insert details successful for doc_no: $doc_no");
    }

    $conn->commit();
    writeLog("Transaction committed successfully for doc_no: $doc_no");
    echo json_encode(['status' => 'success', 'doc_no' => $doc_no]);

} catch (Exception $e) {
    $conn->rollBack();
    writeLog("Transaction rolled back: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
