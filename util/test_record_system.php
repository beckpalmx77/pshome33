<?php
include(__DIR__ . '/../config/connect_db.php');
include(__DIR__ . '/gl_util.php');

/**
 * Test Script: Simulate Saving a new Payment Voucher and verify GL Posting
 */

try {
    $conn->beginTransaction();

    // 1. จำลองข้อมูลที่จะบันทึก
    $test_doc_no = "TEST-PV-" . time();
    $test_date = date('d-m-Y');
    $total_amount = 2500.00;
    $payment_method = 'เงินสด';
    $supplier = 'บริษัท ทดสอบ จำกัด';
    $purpose = 'ทดสอบระบบบันทึกบัญชีอัตโนมัติ';

    echo "--- Start Testing: $test_doc_no ---\n";

    // 2. จำลองการบันทึก Voucher (Header)
    $stmtPV = $conn->prepare("INSERT INTO ims_payment_voucher (doc_no, doc_date, supplier_name, purpose, total_amount, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
    $stmtPV->execute([$test_doc_no, $test_date, $supplier, $purpose, $total_amount, $payment_method]);
    echo "1. Payment Voucher recorded.\n";

    // 3. จำลองการเรียกใช้ PostToGL (เหมือนที่ทำใน Process จริง)
    $gl_entries = [];
    $gl_entries[] = [
        'acc_code' => '5101', // ค่าใช้จ่ายทั่วไป
        'dr' => $total_amount,
        'cr' => 0
    ];
    $payment_acc = GetAccountCodeMapping($conn, $payment_method, 'payment');
    $gl_entries[] = [
        'acc_code' => $payment_acc, // 1101 เงินสด
        'dr' => 0,
        'cr' => $total_amount
    ];

    $gl_desc = "TEST: จ่ายเงินให้ $supplier ($purpose)";
    PostToGL($conn, $test_date, $test_doc_no, $gl_desc, $gl_entries, 'PV');
    echo "2. General Ledger posted automatically.\n";

    // 4. ตรวจสอบข้อมูลใน DB
    echo "3. Verifying Database...\n";
    
    // เช็ค GL Header
    $checkGLH = $conn->prepare("SELECT * FROM ims_gl_header WHERE doc_no = ?");
    $checkGLH->execute([$test_doc_no]);
    $header = $checkGLH->fetch(PDO::FETCH_ASSOC);
    
    if ($header) {
        echo "   [SUCCESS] GL Header created (ID: {$header['gl_id']}, Date: {$header['gl_date']})\n";
        
        // เช็ค GL Details
        $checkGLD = $conn->prepare("SELECT * FROM ims_gl_details WHERE gl_id = ?");
        $checkGLD->execute([$header['gl_id']]);
        $details = $checkGLD->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($details as $d) {
            echo "   [SUCCESS] GL Detail: Acc {$d['acc_code']} | Dr: {$d['dr_amount']} | Cr: {$d['cr_amount']}\n";
        }
    } else {
        echo "   [FAILED] GL Header not found!\n";
    }

    // ย้อนคืนข้อมูลทดสอบ (เพื่อไม่ให้ฐานข้อมูลเปรอะเปื้อน)
    $conn->rollBack();
    echo "--- Test Completed & Data Rolled Back ---\n";

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo "Test Error: " . $e->getMessage() . "\n";
}
