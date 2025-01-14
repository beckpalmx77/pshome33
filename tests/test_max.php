<?php

include('../config/connect_db.php');

$sql_get = "SELECT MAX(sort) AS last_sort FROM menu_main ";
$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $result) {
    $last_sort = intval($result['last_sort']) + 1 ;
    echo $last_sort;
}
