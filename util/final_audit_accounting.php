<?php
include(__DIR__ . '/../config/connect_db.php');

header('Content-Type: text/plain; charset=utf-8');
echo "=== COMPREHENSIVE ACCOUNTING SYSTEM AUDIT ===\n\n";

function checkTable($conn, $tableName) {
    try {
        $stmt = $conn->query("SELECT 1 FROM `$tableName` LIMIT 1");
        return "[OK] Table `$tableName` exists.\n";
    } catch (Exception $e) {
        return "[ERROR] Table `$tableName` is MISSING.\n";
    }
}

function checkColumn($conn, $table, $column) {
    try {
        $stmt = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($stmt->rowCount() > 0) {
            return "   [OK] Column `$column` exists in `$table`.\n";
        } else {
            return "   [ERROR] Column `$column` is MISSING in `$table`.\n";
        }
    } catch (Exception $e) {
        return "   [ERROR] Could not check column `$column` in `$table`.\n";
    }
}

try {
    // 1. Check Tables
    echo "1. Table Existence Check:\n";
    echo checkTable($conn, 'ims_chart_of_accounts');
    echo checkTable($conn, 'ims_gl_header');
    echo checkTable($conn, 'ims_gl_details');
    echo checkTable($conn, 'ims_pgroup');
    echo checkTable($conn, 'ims_category');
    
    // 2. Check Mapping Columns
    echo "\n2. Mapping Column Check:\n";
    echo checkColumn($conn, 'ims_pgroup', 'acc_code');
    echo checkColumn($conn, 'ims_category', 'acc_code');
    
    // 3. Data Integrity & Sync Counts
    echo "\n3. Data Integrity & Sync Counts:\n";
    
    // Chart of Accounts count
    $coa_count = $conn->query("SELECT COUNT(*) FROM ims_chart_of_accounts")->fetchColumn();
    echo "   - Chart of Accounts: $coa_count records.\n";
    
    // Payment Vouchers vs GL
    $pv_total = $conn->query("SELECT COUNT(*) FROM ims_payment_voucher WHERE status = 'Active'")->fetchColumn();
    $pv_gl = $conn->query("SELECT COUNT(*) FROM ims_gl_header WHERE source_type = 'PV'")->fetchColumn();
    echo "   - Payment Vouchers (Active): $pv_total | GL Entries (PV): $pv_gl\n";
    if ($pv_total > $pv_gl) echo "     [!] Warning: Some active PVs are not in GL.\n";
    
    // House Payments vs GL
    $rv_total = $conn->query("SELECT COUNT(*) FROM ims_house_payment WHERE payment_status = 'Y'")->fetchColumn();
    $rv_gl = $conn->query("SELECT COUNT(*) FROM ims_gl_header WHERE source_type = 'RV'")->fetchColumn();
    echo "   - Approved Receipts (Y): $rv_total | GL Entries (RV): $rv_gl\n";
    if ($rv_total > $rv_gl) echo "     [!] Warning: Some approved receipts are not in GL.\n";
    
    // 4. Date Format Validation
    echo "\n4. Date Format Validation (gl_date):\n";
    $invalid_dates = $conn->query("SELECT COUNT(*) FROM ims_gl_header WHERE gl_date = '0000-00-00' OR gl_date IS NULL")->fetchColumn();
    echo "   - GL entries with invalid date: $invalid_dates\n";
    if ($invalid_dates > 0) echo "     [!] Warning: Found $invalid_dates entries with invalid dates.\n";
    
    // 5. Balance Check (Dr = Cr)
    echo "\n5. General Ledger Balance Check:\n";
    $unbalanced = $conn->query("SELECT COUNT(*) FROM (
                                    SELECT gl_id, SUM(dr_amount) as s_dr, SUM(cr_amount) as s_cr 
                                    FROM ims_gl_details 
                                    GROUP BY gl_id 
                                    HAVING ABS(s_dr - s_cr) > 0.01
                                ) as tmp")->fetchColumn();
    echo "   - Unbalanced GL sets: $unbalanced\n";
    if ($unbalanced > 0) echo "     [!] Warning: Found $unbalanced GL sets where Dr != Cr.\n";

    echo "\n=== AUDIT COMPLETED ===\n";

} catch (Exception $e) {
    echo "Audit failed: " . $e->getMessage();
}
