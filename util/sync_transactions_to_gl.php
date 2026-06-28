<?php
/**
 * Auto-Sync Transactions to General Ledger (GL)
 * สำหรับดึงข้อมูลรายรับ-รายจ่ายที่ยังไม่ได้ลงบัญชี เข้าสู่ GL โดยอัตโนมัติ
 */

include(__DIR__ . '/../config/connect_db.php');
include(__DIR__ . '/gl_util.php');

// Helper mapping function for cash/bank methods
function GetCashAccMapping($method) {
    return ($method === 'เงินสด') ? '1101' : '1102';
}

// Smart mapping helper for expenses based on keywords
function GetExpenseAccMapping($purpose) {
    $purpose = mb_strtolower($purpose, 'UTF-8');
    if (mb_strpos($purpose, 'ไฟ') !== false || mb_strpos($purpose, 'ไฟฟ้า') !== false) {
        return '5102'; // ค่าไฟฟ้า (ส่วนกลาง)
    }
    if (mb_strpos($purpose, 'น้ำ') !== false || mb_strpos($purpose, 'ประปา') !== false) {
        return '5103'; // ค่าน้ำประปา (ส่วนกลาง)
    }
    if (mb_strpos($purpose, 'รปภ') !== false || mb_strpos($purpose, 'รักษาความปลอดภัย') !== false || mb_strpos($purpose, 'ตรวจ') !== false) {
        return '5104'; // ค่าจ้าง รปภ.
    }
    if (mb_strpos($purpose, 'สะอาด') !== false || mb_strpos($purpose, 'กวาด') !== false || mb_strpos($purpose, 'ขยะ') !== false || mb_strpos($purpose, 'หญ้า') !== false || mb_strpos($purpose, 'แม่บ้าน') !== false) {
        return '5105'; // ค่าจ้างรักษาความสะอาด
    }
    return '5101'; // ค่าใช้จ่ายทั่วไป
}

$is_cli = (php_sapi_name() === 'cli');

if (!$is_cli) {
    header('Content-Type: text/html; charset=utf-8');
}

$logs = [];
$logs[] = "==================================================";
$logs[] = "  BATCH PROCESSING: TRANSACTION SYNC TO GL        ";
$logs[] = "  Date/Time: " . date('Y-m-d H:i:s');
$logs[] = "==================================================";

try {
    $conn->beginTransaction();

    // 1. Sync Expenses (Payment Voucher)
    $logs[] = "\n1. Checking Unsynced Expense Vouchers (PV)...";
    $sql_pv = "SELECT * FROM ims_payment_voucher 
               WHERE status = 'Active' AND total_amount > 0 
               AND doc_no NOT IN (SELECT doc_no FROM ims_gl_header WHERE source_type = 'PV')";
    $stmt_pv = $conn->query($sql_pv);
    $pv_records = $stmt_pv->fetchAll(PDO::FETCH_ASSOC);
    
    $pv_count = 0;
    foreach ($pv_records as $v) {
        $exp_acc = GetExpenseAccMapping($v['purpose']);
        $cash_acc = GetCashAccMapping($v['payment_method']);
        
        $entries = [
            ['acc_code' => $exp_acc, 'dr' => (float)$v['total_amount'], 'cr' => 0],
            ['acc_code' => $cash_acc, 'dr' => 0, 'cr' => (float)$v['total_amount']]
        ];
        
        $desc = "จ่ายเงินแด่: " . $v['supplier_name'] . " (" . $v['purpose'] . ")";
        $gl_id = PostToGL($conn, $v['doc_date'], $v['doc_no'], $desc, $entries, 'PV');
        $logs[] = "   [OK] Posted Expense Voucher {$v['doc_no']} (GL ID: #$gl_id) -> Acc: $exp_acc";
        $pv_count++;
    }
    $logs[] = "   -> Processed $pv_count expense voucher(s).";

    // 2. Sync Common Fee Payments
    $logs[] = "\n2. Checking Unsynced Common Fee Payments (RV)...";
    $sql_fee = "SELECT p.*, v.month_name_start, v.month_name_to 
                FROM ims_house_payment p
                LEFT JOIN v_ims_house_payment v ON p.id = v.id
                WHERE p.payment_status = 'Y' AND p.amount > 0 
                AND p.doc_id NOT IN (SELECT doc_no FROM ims_gl_header WHERE source_type = 'RV')";
    $stmt_fee = $conn->query($sql_fee);
    $fee_records = $stmt_fee->fetchAll(PDO::FETCH_ASSOC);
    
    $fee_count = 0;
    foreach ($fee_records as $p) {
        $cash_acc = GetCashAccMapping($p['payment_method']);
        
        $entries = [
            ['acc_code' => $cash_acc, 'dr' => (float)$p['amount'], 'cr' => 0],
            ['acc_code' => '4101', 'dr' => 0, 'cr' => (float)$p['amount']]
        ];
        
        $desc = "รับชำระค่าส่วนกลาง บ้านเลขที่ " . $p['house_number'] . " (งวด " . $p['month_name_start'] . " - " . $p['month_name_to'] . " ปี " . $p['period_year'] . ")";
        $gl_id = PostToGL($conn, $p['payment_date'], $p['doc_id'], $desc, $entries, 'RV');
        $logs[] = "   [OK] Posted Common Fee payment {$p['doc_id']} (GL ID: #$gl_id)";
        $fee_count++;
    }
    $logs[] = "   -> Processed $fee_count common fee payment(s).";

    // 3. Sync Other Receipts (Miscellaneous Income)
    $logs[] = "\n3. Checking Unsynced Other Receipts (RV)...";
    $sql_other = "SELECT * FROM ims_reciepts 
                  WHERE approve_status = 'Y' AND amount > 0 
                  AND doc_id NOT IN (SELECT doc_no FROM ims_gl_header WHERE source_type = 'RV')";
    $stmt_other = $conn->query($sql_other);
    $other_records = $stmt_other->fetchAll(PDO::FETCH_ASSOC);
    
    $other_count = 0;
    foreach ($other_records as $r) {
        $cash_acc = GetCashAccMapping($r['payment_method']);
        
        $entries = [
            ['acc_code' => $cash_acc, 'dr' => (float)$r['amount'], 'cr' => 0],
            ['acc_code' => '4103', 'dr' => 0, 'cr' => (float)$r['amount']]
        ];
        
        $desc = "รับเงินจาก " . $r['supplier_name'] . " (" . $r['description'] . ")";
        $gl_id = PostToGL($conn, $r['reciept_date'], $r['doc_id'], $desc, $entries, 'RV');
        $logs[] = "   [OK] Posted Other Receipt {$r['doc_id']} (GL ID: #$gl_id)";
        $other_count++;
    }
    $logs[] = "   -> Processed $other_count other receipt(s).";

    $conn->commit();
    $logs[] = "\n==================================================";
    $logs[] = "  STATUS: BATCH PROCESS COMPLETED SUCCESSFULLY    ";
    $logs[] = "==================================================";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $logs[] = "\n[ERROR] Sync Failed: " . $e->getMessage();
    $logs[] = "==================================================";
}

if ($is_cli) {
    echo implode("\n", $logs) . "\n";
} else {
    // Beautiful HTML Output
    echo '<!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>บันทึกบัญชีแยกประเภทอัตโนมัติ (GL Batch Sync)</title>
        <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <style>
            body { font-family: "Sarabun", sans-serif; background-color: #f8f9fc; padding: 30px; margin: 0; color: #2e384d; }
            .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
            h1 { color: #4e73df; font-size: 24px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; }
            h1 i { margin-right: 12px; }
            pre { background-color: #1e1e24; color: #a9b7c6; padding: 20px; border-radius: 6px; font-family: "Courier New", Courier, monospace; font-size: 13px; line-height: 1.5; overflow-x: auto; }
            .btn { display: inline-block; padding: 10px 20px; color: white; background-color: #4e73df; border: none; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; cursor: pointer; transition: background-color 0.2s; }
            .btn:hover { background-color: #2e59d9; }
            .btn-secondary { background-color: #858796; }
            .btn-secondary:hover { background-color: #717384; }
            .button-group { margin-top: 20px; display: flex; gap: 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1><i class="fas fa-sync"></i> ประมวลผลและผ่านรายการลงสมุดรายวันทั่วไป (GL Sync Status)</h1>
            <pre>' . htmlspecialchars(implode("\n", $logs)) . '</pre>
            <div class="button-group">
                <a href="../Dashboard_admin.php" class="btn"><i class="fas fa-tachometer-alt"></i> กลับสู่แดชบอร์ด</a>
                <a href="sync_transactions_to_gl.php" class="btn btn-secondary"><i class="fas fa-redo"></i> ประมวลผลอีกครั้ง</a>
            </div>
        </div>
    </body>
    </html>';
}
