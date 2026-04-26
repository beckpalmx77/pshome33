<?php
require '../config/connect_db.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "
    SELECT
        icd_type_id,
        icd_type_desc,
        icd_type_sign,
        remark,
        CASE
            WHEN icd_type_sign = '-' THEN 'รายการหัก'
            WHEN icd_type_sign = '+' THEN 'รายได้'
            ELSE '' -- Default or other cases if needed
        END AS icd_type_sign_desc
    FROM m_income_deduct_type
    WHERE status = 'Y'
    ORDER BY id ASC
";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>