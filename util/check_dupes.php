<?php
include(__DIR__ . '/../config/connect_db.php');
$doc = 'P-67/125-2026-005';
echo "--- Checking ims_gl_header for doc_no: $doc ---\n";
$stmt = $conn->prepare('SELECT * FROM ims_gl_header WHERE doc_no = ?');
$stmt->execute([$doc]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
