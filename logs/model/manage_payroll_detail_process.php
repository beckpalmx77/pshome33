<?php
// manage_payroll_data_detail_process.php
header('Content-Type: application/json');
include('../config/connect_db.php'); // ตรวจสอบให้แน่ใจว่าพาธถูกต้อง

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// กำหนดค่าตัวแปร action และ doc_no ที่นี่
$action = $data['action'] ?? '';
$doc_no = $data['doc_no'] ?? ''; // เลขที่เอกสาร (doc_no) ที่ใช้ค้นหาในตาราง ims_payroll_detail

if ($action === 'GET_DETAIL_DATA') {
    if (empty($doc_no)) {
        echo json_encode(['success' => false, 'message' => 'Doc No is required to retrieve detail data.']);
        exit;
    }

    try {
        // ดึงข้อมูลรายละเอียดจาก ims_payroll_detail
        // โดยใช้ doc_no ที่ส่งมาจาก frontend
        $sql_get_detail = "
            SELECT
                pd.icd_type_id,
                icd.icd_type_desc,
                pd.quantity,
                pd.amount_per_unit,
                (pd.quantity * pd.amount_per_unit) AS amount,
                pd.icd_type_sign,
                pd.remark,
                CASE pd.icd_type_sign
                    WHEN '+' THEN 'รายรับ'
                    WHEN '-' THEN 'รายหัก'
                    ELSE ''
                END AS icd_type_sign_desc
            FROM ims_payroll_detail pd
            LEFT JOIN m_income_deduct_type icd ON pd.icd_type_id = icd.icd_type_id
            WHERE pd.doc_no = ? 
            ORDER BY icd.icd_type_desc ASC 
        ";

        $stmt_details = $conn->prepare($sql_get_detail);

        $stmt_details->execute([$doc_no]);
        $details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

        // ส่งข้อมูลกลับไปในรูปแบบ JSON ที่ frontend คาดหวัง
        // (success: true และ data: { details: [...] })
        echo json_encode(['success' => true, 'data' => ['details' => $details]]);
        exit;

    } catch (PDOException $e) {
        // บันทึกข้อผิดพลาดสำหรับ Debug (คุณอาจใช้ error_log($e->getMessage());)
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}