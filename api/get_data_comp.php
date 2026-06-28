<?php
// กำหนด Content-Type เป็น JSON
header('Content-Type: application/json');

// ตั้งค่าการแสดงผลข้อผิดพลาด
ini_set('display_errors', 0);
error_reporting(0);

// นำเข้าไฟล์เชื่อมต่อฐานข้อมูล
include('../config/connect_db.php');

if (!isset($conn)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: $conn object not found'
    ]);
    exit();
}

try {
    // ดึงข้อมูลเปรียบเทียบระหว่าง Receipts กับ GL
    $sql = "
        SELECT 
            r.doc_id,
            r.reciept_date,
            r.description as rec_desc,
            r.amount as rec_amount,
            r.approve_status,
            h.gl_id,
            h.gl_date,
            h.description as gl_desc,
            (SELECT SUM(d.dr_amount) 
             FROM ims_gl_details d 
             WHERE d.gl_id = h.gl_id) as gl_amount
        FROM ims_reciepts r
        LEFT JOIN ims_gl_header h ON r.doc_id = h.doc_no
        ORDER BY r.doc_id DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_docs = count($records);
    $total_rec_amt = 0;
    $total_gl_amt = 0;
    $discrepancy = 0;

    $formatted_data = [];
    foreach ($records as $r) {
        $rec_amt = (float)$r['rec_amount'];
        $gl_amt = $r['gl_amount'] !== null ? (float)$r['gl_amount'] : 0.0;
        $diff = abs($rec_amt - $gl_amt);

        $total_rec_amt += $rec_amt;
        $total_gl_amt += $gl_amt;

        $formatted_data[] = [
            'doc_id' => $r['doc_id'],
            'reciept_date' => $r['reciept_date'],
            'rec_desc' => $r['rec_desc'],
            'rec_amount' => $rec_amt,
            'approve_status' => $r['approve_status'],
            'gl_id' => $r['gl_id'],
            'gl_date' => $r['gl_date'],
            'gl_desc' => $r['gl_desc'],
            'gl_amount' => $gl_amt,
            'diff' => $diff,
            'is_match' => ($diff < 0.01)
        ];
    }

    $discrepancy = abs($total_rec_amt - $total_gl_amt);

    echo json_encode([
        'success' => true,
        'summary' => [
            'total_docs' => $total_docs,
            'total_rec_amount' => $total_rec_amt,
            'total_gl_amount' => $total_gl_amt,
            'discrepancy' => $discrepancy
        ],
        'data' => $formatted_data
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Query error: ' . $e->getMessage()
    ]);
}
?>
