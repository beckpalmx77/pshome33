<?php
// Include the database config file
include_once '../config/connect_db.php';

if (!empty($_POST["province_id"])) {

    $sql1 = "SELECT * FROM amphures WHERE province_id =  " . $_POST['province_id'] . " ORDER BY name_th";
    $query1 = $conn->prepare($sql1);
    $query1->execute();
    $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
    if ($query1->rowCount() > 0) {
        foreach ($results1 as $result1) {
            echo '<option value="' . $result1->amphure_id . '">' . $result1->name_th . '</option>';
        }
    }
} elseif (!empty($_POST["amphure_id"])) {
    $sql1 = "SELECT * FROM districts WHERE amphure_id =  " . $_POST['amphure_id'] . " ORDER BY district_id";
    $query1 = $conn->prepare($sql1);
    $query1->execute();
    $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
    if ($query1->rowCount() > 0) {
        foreach ($results1 as $result1) {
            echo '<option value="' . $result1->district_id . '">' . $result1->name_th . '</option>';
        }
    }
} elseif (!empty($_POST["tumbol_id"])) {
    $sql1 = "SELECT zipcode FROM districts WHERE district_id =  " . $_POST['tumbol_id'] . " ORDER BY district_id";
    $query1 = $conn->prepare($sql1);
    $query1->execute();
    $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
    if ($query1->rowCount() > 0) {
        foreach ($results1 as $result1) {
            echo $result1->zipcode;
        }
    }

}

?>