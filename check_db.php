<?php
include('config/connect_db.php');
$stmt = $conn->prepare("DESCRIBE ims_house");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode("\n", $columns);
?>
