<?php

include("../config/connect_db.php");

$month_start = $_POST['month_start'];
$month_to = $_POST['month_to'];
$year = $_POST['year'];
$branch = $_POST['branch'];

// Query to fetch data based on the selected criteria
$sql = "SELECT * FROM your_table WHERE month >= :month_start AND month <= :month_to AND year = :year AND branch = :branch";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':month_start', $month_start);
$stmt->bindParam(':month_to', $month_to);
$stmt->bindParam(':year', $year);
$stmt->bindParam(':branch', $branch);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare the response for DataTables
$response = array(
    "draw" => intval($_POST['draw']),
    "recordsTotal" => count($data),
    "recordsFiltered" => count($data),
    "data" => $data
);

echo json_encode($response);


