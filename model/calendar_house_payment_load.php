<?php

include('../config/connect_db.php');

// $connect = new PDO('mysql:host=localhost;dbname=testing', 'root', '');

$data = array();

$query = "SELECT * FROM v_ims_house_payment_date ORDER BY payment_date_id ";

$statement = $conn->prepare($query);

$statement->execute();

$result = $statement->fetchAll();

foreach($result as $row)
{
    $data[] = array(
        'id'   => $row["payment_date"],
        'title'   => "จำนวนเงิน " . number_format($row["total_amount"],0) . " บาท",
        'total_amount'   => $row["total_amount"],
        'payment_date'   => $row["payment_date"],
        'start'   => $row["payment_date_start"],
        'end'   => $row["payment_date_end"]
    );
}

echo json_encode($data);