<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');

if ($_POST["action"] === 'GET_MESSAGE') {

    $return_arr = array();

    $sql_get = "SELECT * FROM afront_contact ORDER BY id DESC LIMIT 5";
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "f_name" => $result['f_name'],
            "l_name" => $result['l_name'],
            "email" => $result['email'],
            "phone" => $result['phone'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}