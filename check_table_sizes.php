<?php
include('config/connect_db.php');
try {
    $tables = ['ims_house_payment', 'ims_house', 'ims_house_master', 'ims_month', 'ims_user', 'ims_house_line_user'];
    foreach ($tables as $t) {
        $stmt = $conn->query("SELECT COUNT(*) FROM $t");
        echo "Table $t row count: " . $stmt->fetchColumn() . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
