<?php
include(__DIR__ . '/../config/connect_db.php');

header('Content-Type: text/plain; charset=utf-8');
echo "=== GL DATA ACCURACY AUDIT (SAMPLE CHECK) ===\n\n";

try {
    // Sample 1: Expense (PV)
    echo "--- Sample 1: Expense (PV) ---\n";
    $sql_pv = "SELECT doc_no, total_amount, doc_date FROM ims_payment_voucher WHERE status = 'Active' LIMIT 1";
    $pv = $conn->query($sql_pv)->fetch(PDO::FETCH_ASSOC);
    if ($pv) {
        echo "Source (PV): Doc {$pv['doc_no']} | Amount: {$pv['total_amount']} | Date: {$pv['doc_date']}\n";
        
        $sql_gl = "SELECT h.gl_date, d.acc_code, d.dr_amount, d.cr_amount 
                   FROM ims_gl_header h 
                   JOIN ims_gl_details d ON h.gl_id = d.gl_id 
                   WHERE h.doc_no = :doc AND h.source_type = 'PV'";
        $stmt = $conn->prepare($sql_gl);
        $stmt->execute([':doc' => $pv['doc_no']]);
        $gl_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($gl_entries as $entry) {
            echo "GL Record: Date {$entry['gl_date']} | Acc: {$entry['acc_code']} | Dr: {$entry['dr_amount']} | Cr: {$entry['cr_amount']}\n";
        }
    } else {
        echo "No Active Payment Vouchers found.\n";
    }

    echo "\n";

    // Sample 2: Income (RV)
    echo "--- Sample 2: Income (RV) ---\n";
    $sql_rv = "SELECT doc_id, amount, payment_date FROM ims_house_payment WHERE payment_status = 'Y' LIMIT 1";
    $rv = $conn->query($sql_rv)->fetch(PDO::FETCH_ASSOC);
    if ($rv) {
        echo "Source (RV): Doc {$rv['doc_id']} | Amount: {$rv['amount']} | Date: {$rv['payment_date']}\n";
        
        $sql_gl_rv = "SELECT h.gl_date, d.acc_code, d.dr_amount, d.cr_amount 
                      FROM ims_gl_header h 
                      JOIN ims_gl_details d ON h.gl_id = d.gl_id 
                      WHERE h.doc_no = :doc AND h.source_type = 'RV'";
        $stmt_rv = $conn->prepare($sql_gl_rv);
        $stmt_rv->execute([':doc' => $rv['doc_id']]);
        $gl_entries_rv = $stmt_rv->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($gl_entries_rv as $entry) {
            echo "GL Record: Date {$entry['gl_date']} | Acc: {$entry['acc_code']} | Dr: {$entry['dr_amount']} | Cr: {$entry['cr_amount']}\n";
        }
    } else {
        echo "No Approved Receipts found.\n";
    }

} catch (Exception $e) {
    echo "Audit Script Error: " . $e->getMessage();
}
