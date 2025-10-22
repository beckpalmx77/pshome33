<?php


// connect to the database to get the PDO instance
include('../config/connect_db.php');

$return_arr = array();

$sql = "SELECT * FROM ims_user WHERE id = 8";

// execute a query
$statement = $conn->query($sql);

// fetch all rows
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

// display the publisher name
foreach ($results as $result) {

    //echo $result['email'] . '<br>';
    $return_arr[] = array("id" => $result['id'] ,
        "email" => $result['email'],
        "first_name" => $result['first_name'],
        "last_name" => $result['last_name']);
}

