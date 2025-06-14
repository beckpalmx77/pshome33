<?php
require '../config/connect_db.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "
    SELECT 
        product_id,
        product_name
    FROM ims_products
    WHERE status = 'Active'
    ORDER BY product_id ASC
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


