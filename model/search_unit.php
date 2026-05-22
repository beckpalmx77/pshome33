<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../config/connect_db.php';

$search = "%" . $_POST['query'] . "%";

$sql_get = "SELECT unit_id, unit_name FROM ims_unit 
                WHERE unit_name LIKE ?
                ORDER BY unit_name ASC 
                LIMIT 10";

$stmt = $conn->prepare($sql_get);
$stmt->execute([$search]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$output = '';
foreach ($results as $row) {
    $output .= '<a href="#" class="list-group-item list-group-item-action unit-item" 
                       data-id="' . $row['unit_id'] . '" 
                       data-unit_name="' . htmlspecialchars($row['unit_name'] ?? '') . '">'
        . htmlspecialchars($row['unit_name'] ?? '') . '</a>';
}

echo $output ?: '<div class="list-group-item">ไม่พบข้อมูล</div>';


