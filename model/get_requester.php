<?php
include('../config/connect_db.php');

if (isset($_POST['query'])) {
    $query = '%' . $_POST['query'] . '%';

    $stmt = $conn->prepare("
        SELECT emp_id, CONCAT(f_name, ' ', l_name) AS requester_name 
        FROM memployee 
        WHERE CONCAT(f_name, ' ', l_name) LIKE ? 
        ORDER BY requester_name 
        LIMIT 10
    ");
    $stmt->execute([$query]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        echo '<a href="#" class="list-group-item list-group-item-action requester-item"
                data-id="' . htmlspecialchars($row['emp_id']) . '"
                data-name="' . htmlspecialchars($row['requester_name']) . '">' .
            htmlspecialchars($row['requester_name']) .
            '</a>';
    }
}
