<?php
require '../config/connect_db.php';

$stmt = $pdo->query("SELECT month, month_name FROM ims_month ORDER BY month ASC");

$months = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($months as $m) {
    echo "<option value='{$m['month']}'>{$m['month_name']}</option>";
}