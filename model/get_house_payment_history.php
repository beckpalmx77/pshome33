<?php
header('Content-Type: application/json');
include('../config/connect_db.php');

$house_number = isset($_GET['house_number']) ? $_GET['house_number'] : '';

if (empty($house_number)) {
    echo json_encode(['status' => 'error', 'message' => 'House number is required']);
    exit;
}

try {
    $sql = "SELECT * FROM v_ims_house_payment 
            WHERE house_number = :hn AND payment_status = 'Y'
            ORDER BY period_year DESC, period_month_start DESC";
    $query = $conn->prepare($sql);
    $query->execute([':hn' => $house_number]);
    $results = $query->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($results as $row) {
        $data[] = [
            'payment_date' => $row['payment_date'],
            'period' => $row['month_name_start'] . ' - ' . $row['month_name_to'] . ' ' . ($row['period_year'] + 543),
            'amount' => (float)$row['amount'],
            'payment_type' => $row['payment_type']
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
