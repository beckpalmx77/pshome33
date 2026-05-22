<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../config/connect_db.php';

$search = "%" . $_POST['query'] . "%";

$sql_get = "SELECT product_id, product_name FROM ims_products 
                WHERE product_name LIKE ?
                ORDER BY product_name ASC 
                LIMIT 10";

$stmt = $conn->prepare($sql_get);
$stmt->execute([$search]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$output = '';
foreach ($results as $row) {
    $output .= '<a href="#" class="list-group-item list-group-item-action product-item" 
                       data-id="' . $row['product_id'] . '" 
                       data-product_name="' . htmlspecialchars($row['product_name'] ?? '') . '">'
        . htmlspecialchars($row['product_name'] ?? '') . '</a>';
}

echo $output ?: '<div class="list-group-item">ไม่พบข้อมูล</div>';


