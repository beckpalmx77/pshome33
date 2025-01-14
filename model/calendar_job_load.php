<?php

include('../config/connect_db.php');

// $connect = new PDO('mysql:host=localhost;dbname=testing', 'root', '');

$data = array();

$query = "SELECT * FROM job_payment_daily_total ORDER BY id";

$statement = $conn->prepare($query);

$statement->execute();

$result = $statement->fetchAll();

foreach($result as $row)
{
    $data[] = array(
        'id'   => $row["job_date"],
        'title'   => $row["total_tires"] . " เส้น",
        'description'   => $row["total_job_emp"],
        'job_date'   => $row["job_date"],
        'start'   => $row["job_date_calendar"],
        'end'   => $row["job_date_calendar"]
    );
}

echo json_encode($data);

?>

