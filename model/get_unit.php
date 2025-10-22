<?php
require '../config/connect_db.php';

try {
    $sql = " SELECT unit_id, unit_name FROM ims_unit ORDER BY unit_id ASC ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($units);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

