<?php
include('../config/connect_db.php');

if (isset($_POST['query'])) {
    $query = '%' . $_POST['query'] . '%';

    // เปลี่ยนเป็น table และ field จริงที่คุณใช้
    $stmt = $conn->prepare("SELECT requester_id, requester_name 
    FROM ims_requester WHERE requester_name LIKE ? ORDER BY requester_name LIMIT 10");
    $stmt->execute([$query]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        echo '<a href="#" class="list-group-item list-group-item-action requester-item"
                data-id="' . htmlspecialchars($row['requester_id']) . '"
                data-name="' . htmlspecialchars($row['requester_name']) . '">' .
            htmlspecialchars($row['requester_name']) .
            '</a>';
    }
}
