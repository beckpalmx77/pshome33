<?php

include('../config/connect_db.php');

$name = $_POST["name"]; //grabing the data from headers
$address = $_POST["address"];
$class = $_POST["class"];
$rollno = $_POST["rollno"];

$val_data = $name . " | " . $address . " | " . $class . " | " . $rollno;

$myfile = fopen("save_file.txt", "w") or die("Unable to open file!");
fwrite($myfile, $val_data);
fclose($myfile);

if ($_POST["rollno"] !== '') {

    $sql = "INSERT INTO ims_customer(customer_id,f_name,l_name,address,phone) 
            VALUES (:customer_id,:f_name,:l_name,:address,:phone)";
    $query = $conn->prepare($sql);
    $query->bindParam(':customer_id', $rollno, PDO::PARAM_STR);
    $query->bindParam(':f_name', $name, PDO::PARAM_STR);
    $query->bindParam(':l_name', $name, PDO::PARAM_STR);
    $query->bindParam(':address', $address, PDO::PARAM_STR);
    $query->bindParam(':phone', $class, PDO::PARAM_STR);
    $query->execute();
    $lastInsertId = $conn->lastInsertId();

    if ($lastInsertId) {
        $return["success"] = true;
        $return["message"] = "Success";
    } else {
        $return["error"] = true;
        $return["message"] = "Database error";
    }

} else {
    $return["error"] = true;
    $return["message"] = "Data Not Complete";
}

header('Content-Type: application/json');

$customer_data = json_encode($return);
file_put_contents("customer_data.json", $customer_data);

// tell browser that its a json data
echo json_encode($return);
//converting array to JSON string
?>