<?php
include(__DIR__ . '/../config/connect_db.php');
$doc = 'P-67/125-2026-005';
$stmt = $conn->prepare('SELECT h.gl_id, h.doc_no, d.acc_code, d.dr_amount, d.cr_amount FROM ims_gl_header h JOIN ims_gl_details d ON h.gl_id = d.gl_id WHERE h.doc_no = ?');
$stmt->execute([$doc]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
