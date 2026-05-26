<?php
include(__DIR__ . '/../config/connect_db.php');

try {
    $conn->beginTransaction();

    // Fetch all PV records to sync
    $sql = "SELECT doc_no, doc_date FROM ims_payment_voucher";
    $stmt = $conn->query($sql);
    $vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtUpdate = $conn->prepare("UPDATE ims_gl_header SET gl_date = ? WHERE doc_no = ? AND source_type = 'PV'");

    $count = 0;
    foreach ($vouchers as $v) {
        $raw_date = $v['doc_date'];
        
        // Convert DD-MM-YYYY to YYYY-MM-DD
        // Example: 28-06-2025 -> 2025-06-28
        $parts = explode('-', $raw_date);
        if (count($parts) === 3) {
            $formatted_date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            $stmtUpdate->execute([$formatted_date, $v['doc_no']]);
            $count += $stmtUpdate->rowCount();
        }
    }

    $conn->commit();
    echo "Successfully synchronized $count GL entries with corrected date format (YYYY-MM-DD).";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Synchronization failed: " . $e->getMessage();
}
