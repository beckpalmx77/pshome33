<?php
session_start();
error_reporting(0); // ปิดการแสดงข้อผิดพลาดเพื่อความปลอดภัยใน production

include('../config/connect_db.php'); // ตรวจสอบเส้นทางให้ถูกต้อง

header('Content-Type: application/json');

// Define a log file path
$debug_log_file = __DIR__ . '/debug_installment_details.json'; // This will place the log file in the same directory as get_installment_details.php

// Function to log data to a file
function log_to_file($filename, $data)
{
    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT), FILE_APPEND);
    file_put_contents($filename, ",\n", FILE_APPEND); // Add a comma and newline for subsequent entries
}

// Check if $conn is a usable PDO object
if (!isset($conn) || !$conn instanceof PDO) {
    $error_message = ['status' => 'error', 'message' => 'Database connection failed. Please check connect_db.php.'];
    //log_to_file($debug_log_file, ['timestamp' => date('Y-m-d H:i:s'), 'type' => 'connection_error', 'data' => $error_message]);
    echo json_encode($error_message);
    exit();
}

try {
    // Get installment_id from GET request
    $doc_installment_id = $_GET['installment_id'] ?? '';

    // Log the received installment_id
    //log_to_file($debug_log_file, ['timestamp' => date('Y-m-d H:i:s'), 'type' => 'request_param', 'installment_id' => $doc_installment_id]);

    if (empty($doc_installment_id)) {
        $error_message = ['status' => 'error', 'message' => 'Missing installment_id parameter.'];
        //log_to_file($debug_log_file, ['timestamp' => date('Y-m-d H:i:s'), 'type' => 'missing_param_error', 'data' => $error_message]);
        echo json_encode($error_message);
        exit();
    }

    // Prepare SQL statement to fetch detail data
    $stmt = $conn->prepare("
        SELECT
            id,
            installment_id,
            installment_number,
            status,
            principal_per_installment,
            amount_paid,
            amount_due,
            payment_date,
            payment_method
        FROM ims_installment_detail
        WHERE installment_id = ?
        ORDER BY id ASC
    ");
    $stmt->execute([$doc_installment_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log the raw database results
    //log_to_file($debug_log_file, ['timestamp' => date('Y-m-d H:i:s'), 'type' => 'db_results', 'installment_id' => $doc_installment_id, 'data' => $results]);


    // Restructure data to match frontend expectations
    $formatted_details = [];
    foreach ($results as $row) {
        $formatted_details[] = [
            'id' => $row['id'],
            'installment_id' => $row['installment_id'],
            'installment_number' => $row['installment_number'],
            'principal_per_installment' => $row['principal_per_installment'] ?? null,
            'amount_paid' => $row['amount_paid'] ?? null,
            'amount_due' => $row['amount_due'] ?? null,
            'payment_date' => $row['payment_date'] ?? '',
            'payment_method' => $row['payment_method'] ?? null,
            'status' => $row['status'],
            'line_no' => null
        ];
    }

    // Log the formatted details before sending
    //log_to_file($debug_log_file, ['timestamp' => date('Y-m-d H:i:s'), 'type' => 'formatted_details', 'installment_id' => $doc_installment_id, 'data' => $formatted_details]);


    echo json_encode(['status' => 'success', 'details' => $formatted_details]);

} catch (PDOException $e) {
    // Log PDO errors
    $error_message = ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
    error_log("PDO Error in get_installment_details.php: " . $e->getMessage());
    //log_to_file($debug_log_file, ['timestamp' => date('Y-m-d H:i:s'), 'type' => 'pdo_exception', 'data' => $error_message]);
    echo json_encode($error_message);
}

exit();
?>