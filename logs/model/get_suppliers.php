<?php
include('../config/connect_db.php');

if (isset($_POST['query'])) {
    $query = "%".$_POST['query']."%";

    $stmt = $conn->prepare("SELECT supplier_id,address,supplier_name FROM ims_supplier WHERE supplier_name LIKE ? ORDER BY supplier_name LIMIT 10");
    $stmt->execute([$query]);
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($suppliers as $row) {
        echo '<a href="#" class="list-group-item list-group-item-action supplier-item" 
                 data-id="' . htmlspecialchars($row['supplier_id']) . '" 
                 data-address="' . htmlspecialchars($row['address']) . '" 
                 data-name="' . htmlspecialchars($row['supplier_name']) . '">'
            . htmlspecialchars($row['supplier_name']) .
            '</a>';
    }
}
?>