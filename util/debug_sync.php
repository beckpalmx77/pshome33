<?php
include(__DIR__ . '/../config/connect_db.php');

echo "--- GL Header Samples ---\n";
$stmt = $conn->query("SELECT doc_no, gl_date, source_type FROM ims_gl_header LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- Payment Voucher Samples ---\n";
$stmt = $conn->query("SELECT doc_no, doc_date FROM ims_payment_voucher LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- Source Type Counts ---\n";
$stmt = $conn->query("SELECT source_type, COUNT(*) as count FROM ims_gl_header GROUP BY source_type");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
