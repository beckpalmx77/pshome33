<?php
/**
 * Re-Process and Rebuild All Revenues and Expenses to GL
 * ล้างข้อมูลบันทึกบัญชีของรายรับ-รายจ่ายเดิม (PV, RV) และประมวลผลผ่านรายการใหม่ทั้งหมดจากฐานข้อมูลหลัก
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
$logs[] = "  RE-PROCESSING ALL REVENUES AND EXPENSES TO GL  ";
$logs[] = "  Started at: " . date('Y-m-d H:i:s');
$logs[] = "==================================================";

try {
    $conn->beginTransaction();

    // 1. Clean Wipe existing PV/RV GL Entries
    $logs[] = "\n1. Cleaning up existing GL Entries (PV and RV)...";
    
    // Deleting headers will automatically delete details via ON DELETE CASCADE constraint
    $stmt_clean = $conn->prepare("DELETE FROM ims_gl_header WHERE source_type IN ('PV', 'RV')");
    $stmt_clean->execute();
    $cleared_count = $stmt_clean->rowCount();
    $logs[] = "   [OK] Cleared $cleared_count old GL Header entries (and their details).";

    // 2. Re-process Expenses (Payment Voucher)
    $logs[] = "\n2. Rebuilding Expense Vouchers (PV)...";
    $sql_pv = "SELECT * FROM ims_payment_voucher WHERE status = 'Active' AND total_amount > 0";
    $stmt_pv = $conn->query($sql_pv);
    $pv_records = $stmt_pv->fetchAll(PDO::FETCH_ASSOC);
    
    $pv_count = 0;
    $pv_total_amt = 0;
    foreach ($pv_records as $v) {
        $exp_acc = GetExpenseAccMapping($v['purpose']);
        $cash_acc = GetCashAccMapping($v['payment_method']);
        
        $entries = [
            ['acc_code' => $exp_acc, 'dr' => (float)$v['total_amount'], 'cr' => 0],
            ['acc_code' => $cash_acc, 'dr' => 0, 'cr' => (float)$v['total_amount']]
        ];
        
        $desc = "จ่ายเงินแด่: " . $v['supplier_name'] . " (" . $v['purpose'] . ")";
        $gl_id = PostToGL($conn, $v['doc_date'], $v['doc_no'], $desc, $entries, 'PV');
        $pv_total_amt += (float)$v['total_amount'];
        $pv_count++;
    }
    $logs[] = "   [OK] Re-posted $pv_count expense entries (Total: " . number_format($pv_total_amt, 2) . " Baht).";

    // 3. Re-process Common Fee Payments
    $logs[] = "\n3. Rebuilding Common Fee Payments (RV)...";
    $sql_fee = "SELECT p.*, v.month_name_start, v.month_name_to 
                FROM ims_house_payment p
                LEFT JOIN v_ims_house_payment v ON p.id = v.id
                WHERE p.payment_status = 'Y' AND p.amount > 0";
    $stmt_fee = $conn->query($sql_fee);
    $fee_records = $stmt_fee->fetchAll(PDO::FETCH_ASSOC);
    
    $fee_count = 0;
    $fee_total_amt = 0;
    foreach ($fee_records as $p) {
        $cash_acc = GetCashAccMapping($p['payment_method']);
        
        $entries = [
            ['acc_code' => $cash_acc, 'dr' => (float)$p['amount'], 'cr' => 0],
            ['acc_code' => '4101', 'dr' => 0, 'cr' => (float)$p['amount']]
        ];
        
        $desc = "รับชำระค่าส่วนกลาง บ้านเลขที่ " . $p['house_number'] . " (งวด " . $p['month_name_start'] . " - " . $p['month_name_to'] . " ปี " . $p['period_year'] . ")";
        $gl_id = PostToGL($conn, $p['payment_date'], $p['doc_id'], $desc, $entries, 'RV');
        $fee_total_amt += (float)$p['amount'];
        $fee_count++;
    }
    $logs[] = "   [OK] Re-posted $fee_count common fee entries (Total: " . number_format($fee_total_amt, 2) . " Baht).";

    // 4. Re-process Other Receipts
    $logs[] = "\n4. Rebuilding Other Receipts (RV)...";
    $sql_other = "SELECT * FROM ims_reciepts WHERE approve_status = 'Y' AND amount > 0";
    $stmt_other = $conn->query($sql_other);
    $other_records = $stmt_other->fetchAll(PDO::FETCH_ASSOC);
    
    $other_count = 0;
    $other_total_amt = 0;
    foreach ($other_records as $r) {
        $cash_acc = GetCashAccMapping($r['payment_method']);
        
        $entries = [
            ['acc_code' => $cash_acc, 'dr' => (float)$r['amount'], 'cr' => 0],
            ['acc_code' => '4103', 'dr' => 0, 'cr' => (float)$r['amount']]
        ];
        
        $desc = "รับเงินจาก " . $r['supplier_name'] . " (" . $r['description'] . ")";
        $gl_id = PostToGL($conn, $r['reciept_date'], $r['doc_id'], $desc, $entries, 'RV');
        $other_total_amt += (float)$r['amount'];
        $other_count++;
    }
    $logs[] = "   [OK] Re-posted $other_count other receipt entries (Total: " . number_format($other_total_amt, 2) . " Baht).";

    $conn->commit();
    $logs[] = "\n==================================================";
    $logs[] = "  STATUS: REBUILD SUCCESSFUL                      ";
    $logs[] = "  Total Rebuilt Transactions: " . ($pv_count + $fee_count + $other_count);
    $logs[] = "==================================================";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $logs[] = "\n[ERROR] Reprocess Failed: " . $e->getMessage();
    $logs[] = "==================================================";
}

if ($is_cli) {
    echo implode("\n", $logs) . "\n";
} else {
    // Beautiful Web Dashboard
    echo '<!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>ล้างข้อมูลและประมวลผลบัญชีแยกประเภทใหม่ (GL Rebuild)</title>
        <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <style>
            body { font-family: "Sarabun", sans-serif; background-color: #f8f9fc; padding: 30px; margin: 0; color: #2e384d; }
            .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
            h1 { color: #e74a3b; font-size: 24px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; }
            h1 i { margin-right: 12px; }
            pre { background-color: #1e1e24; color: #a9b7c6; padding: 20px; border-radius: 6px; font-family: "Courier New", Courier, monospace; font-size: 13px; line-height: 1.5; overflow-x: auto; }
            .btn { display: inline-block; padding: 10px 20px; color: white; background-color: #4e73df; border: none; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; cursor: pointer; transition: background-color 0.2s; }
            .btn:hover { background-color: #2e59d9; }
            .btn-danger { background-color: #e74a3b; }
            .btn-danger:hover { background-color: #be2e21; }
            .button-group { margin-top: 20px; display: flex; gap: 10px; }
            .warning-banner { background-color: #fff3cd; border-left: 4px solid #ffc107; color: #856404; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1><i class="fas fa-trash-restore"></i> ล้างและประมวลผลรายการลงสมุดรายวันทั่วไปใหม่ (GL Full Reprocess)</h1>
            <div class="warning-banner">
                <i class="fas fa-exclamation-triangle"></i> <strong>ข้อควรระวัง:</strong> การประมวลผลใหม่นี้จะล้างรายการรายรับและรายจ่าย (PV, RV) ในแยกประเภททั้งหมดก่อน และสร้างเอกสารลงบัญชีให้ตรงตามฐานข้อมูลหลัก ณ ปัจจุบันโดยอัตโนมัติ
            </div>
            <pre>' . htmlspecialchars(implode("\n", $logs)) . '</pre>
            <div class="button-group">
                <a href="../Dashboard_admin.php" class="btn"><i class="fas fa-tachometer-alt"></i> กลับสู่แดชบอร์ด</a>
                <a href="reprocess_all_to_gl.php" class="btn btn-danger" onclick="return confirm(\'คุณแน่ใจหรือไม่ที่จะทำการล้างข้อมูลและประมวลผลบัญชีใหม่ทั้งหมด?\')"><i class="fas fa-sync-alt"></i> เริ่มประมวลผลใหม่ทั้งหมดอีกครั้ง</a>
            </div>
        </div>
    </body>
    </html>';
}
