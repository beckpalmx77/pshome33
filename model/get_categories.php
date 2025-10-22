<?php
// get_categories.php
require_once '../config/connect_db.php'; // เชื่อมต่อฐานข้อมูลของคุณ

$sql = "SELECT category_id, category_name FROM m_category WHERE status = 'Y'";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => $row['category_id'],
        "text" => $row['category_name']
    ];
}

echo json_encode($data);