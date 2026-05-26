<?php
/**
 * All-in-One Accounting Setup & Sync Script (Version 1.1 - Robust Edition)
 * สำหรับติดตั้งระบบบัญชีและย้ายข้อมูลเดิมเข้าสู่ผังบัญชี (Dr/Cr)
 */

include(__DIR__ . '/../config/connect_db.php');

// --- Helper Functions ---

function AddColumnSafely($conn, $table, $column, $definition) {
    $stmt = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($stmt->rowCount() === 0) {
        $conn->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        return "   [OK] Added column `$column` to `$table`.\n";
    }
    return "   [SKIP] Column `$column` already exists in `$table`.\n";
}

function InternalPostToGL($conn, $gl_date, $doc_no, $description, $entries, $source_type = 'JV') {
    if (strpos($gl_date, '-') !== false) {
        $parts = explode('-', $gl_date);
        if (count($parts) === 3 && strlen($parts[0]) === 2) {
            $gl_date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
    }
    $stmtH = $conn->prepare("INSERT INTO ims_gl_header (gl_date, doc_no, description, source_type) VALUES (?, ?, ?, ?)");
    $stmtH->execute([$gl_date, $doc_no, $description, $source_type]);
    $gl_id = $conn->lastInsertId();
    $stmtD = $conn->prepare("INSERT INTO ims_gl_details (gl_id, acc_code, dr_amount, cr_amount) VALUES (?, ?, ?, ?)");
    foreach ($entries as $e) {
        $stmtD->execute([$gl_id, $e['acc_code'], $e['dr'], $e['cr']]);
    }
    return $gl_id;
}

function InternalGetAccMapping($method) {
    return ($method === 'เงินสด') ? '1101' : '1102';
}

header('Content-Type: text/plain; charset=utf-8');
echo "=== ACCOUNTING SYSTEM FULL SETUP & SYNC (V1.1) ===\n\n";

try {
    $conn->beginTransaction();

    // 1. สร้างตารางฐานข้อมูลหลัก
    echo "1. Creating Main Accounting Tables...\n";
    $sql_tables = "
    CREATE TABLE IF NOT EXISTS `ims_chart_of_accounts` (
      `acc_code` varchar(20) NOT NULL,
      `acc_name` varchar(255) NOT NULL,
      `acc_group` enum('Asset','Liability','Equity','Revenue','Expense') NOT NULL,
      `status` varchar(10) DEFAULT 'Active',
      PRIMARY KEY (`acc_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE IF NOT EXISTS `ims_gl_header` (
      `gl_id` int(11) NOT NULL AUTO_INCREMENT,
      `gl_date` date NOT NULL,
      `doc_no` varchar(50) DEFAULT NULL,
      `description` text,
      `source_type` varchar(10) DEFAULT 'JV',
      `create_date` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`gl_id`),
      KEY `idx_doc_no` (`doc_no`),
      KEY `idx_gl_date` (`gl_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE IF NOT EXISTS `ims_gl_details` (
      `gl_id` int(11) NOT NULL,
      `acc_code` varchar(20) NOT NULL,
      `dr_amount` decimal(15,2) DEFAULT 0.00,
      `cr_amount` decimal(15,2) DEFAULT 0.00,
      KEY `idx_gl_id` (`gl_id`),
      KEY `idx_acc_code` (`acc_code`),
      CONSTRAINT `fk_gl_header_setup` FOREIGN KEY (`gl_id`) REFERENCES `ims_gl_header` (`gl_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    $conn->exec($sql_tables);
    echo "   [OK] Accounting tables checked.\n";

    // 2. เพิ่มคอลัมน์ผูกบัญชี (Mapping Columns)
    echo "2. Adding Mapping Columns to Existing Tables...\n";
    echo AddColumnSafely($conn, 'ims_pgroup', 'acc_code', "VARCHAR(20) DEFAULT NULL");
    echo AddColumnSafely($conn, 'ims_category', 'acc_code', "VARCHAR(20) DEFAULT NULL");

    // 3. เพิ่มข้อมูลผังบัญชีเริ่มต้น
    echo "3. Injecting Default Chart of Accounts...\n";
    $sql_coa = "INSERT IGNORE INTO `ims_chart_of_accounts` (`acc_code`, `acc_name`, `acc_group`) VALUES 
    ('1101', 'เงินสด', 'Asset'), ('1102', 'เงินฝากธนาคาร', 'Asset'), ('1103', 'ลูกหนี้ค่าส่วนกลาง', 'Asset'),
    ('2101', 'เจ้าหนี้การค้า', 'Liability'), ('3101', 'ทุน/เงินกองทุนหมู่บ้าน', 'Equity'),
    ('4101', 'รายได้ค่าส่วนกลาง', 'Revenue'), ('4102', 'รายได้ค่าปรับ', 'Revenue'), ('4103', 'รายได้อื่นๆ', 'Revenue'),
    ('5101', 'ค่าใช้จ่ายทั่วไป', 'Expense'), ('5102', 'ค่าไฟฟ้า (ส่วนกลาง)', 'Expense'),
    ('5103', 'ค่าน้ำประปา (ส่วนกลาง)', 'Expense'), ('5104', 'ค่าจ้าง รปภ.', 'Expense'), ('5105', 'ค่าจ้างรักษาความสะอาด', 'Expense')";
    $conn->exec($sql_coa);
    echo "   [OK] Default accounts injected.\n";

    // 4. Sync รายจ่าย (Expenses)
    echo "4. Synchronizing Expenses (Vouchers)...\n";
    $sql_pv = "SELECT * FROM ims_payment_voucher WHERE status = 'Active' AND total_amount > 0 AND doc_no NOT IN (SELECT doc_no FROM ims_gl_header WHERE source_type = 'PV')";
    $stmt_pv = $conn->query($sql_pv);
    $pv_records = $stmt_pv->fetchAll(PDO::FETCH_ASSOC);
    foreach ($pv_records as $v) {
        $entries = [
            ['acc_code' => '5101', 'dr' => $v['total_amount'], 'cr' => 0],
            ['acc_code' => InternalGetAccMapping($v['payment_method']), 'dr' => 0, 'cr' => $v['total_amount']]
        ];
        InternalPostToGL($conn, $v['doc_date'], $v['doc_no'], "Setup: " . $v['purpose'], $entries, 'PV');
    }
    echo "   [OK] Processed " . count($pv_records) . " expense entries.\n";

    // 5. Sync รายรับ (Income)
    echo "5. Synchronizing Income (Common Fee)...\n";
    $sql_rv = "SELECT p.*, v.month_name_start, v.month_name_to 
               FROM ims_house_payment p
               LEFT JOIN v_ims_house_payment v ON p.id = v.id
               WHERE p.payment_status = 'Y' AND p.amount > 0 AND p.doc_id NOT IN (SELECT doc_no FROM ims_gl_header WHERE source_type = 'RV')";
    $stmt_rv = $conn->query($sql_rv);
    $rv_records = $stmt_rv->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rv_records as $p) {
        $entries = [
            ['acc_code' => InternalGetAccMapping($p['payment_method']), 'dr' => $p['amount'], 'cr' => 0],
            ['acc_code' => '4101', 'dr' => 0, 'cr' => $p['amount']]
        ];
        $desc = "Setup: รับชำระค่าส่วนกลาง " . $p['house_number'] . " (" . $p['month_name_start'] . "-" . $p['month_name_to'] . ")";
        InternalPostToGL($conn, $p['payment_date'], $p['doc_id'], $desc, $entries, 'RV');
    }
    echo "   [OK] Processed " . count($rv_records) . " income entries.\n";

    $conn->commit();
    echo "\n=== ALL DONE! YOUR ACCOUNTING SYSTEM IS FULLY CONFIGURED ===\n";

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo "\n[ERROR] Setup Failed: " . $e->getMessage() . "\n";
}
