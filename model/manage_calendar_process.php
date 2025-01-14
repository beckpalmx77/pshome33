<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'ADD') {
    if ($_POST["title"] !== '') {
        $title = $_POST["title"];
        $start_event = $_POST["date"];
        $end_event = $_POST["date"];

        $sql = "INSERT INTO ims_event(title,start_event,end_event) VALUES (:title,:start_event,:end_event)";
        $query = $conn->prepare($sql);
        $query->bindParam(':title', $title, PDO::PARAM_STR);
        $query->bindParam(':start_event', $start_event, PDO::PARAM_STR);
        $query->bindParam(':end_event', $end_event, PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            echo $save_success;
        } else {
            echo $error;
        }
    }
}

if ($_POST["action"] === 'UPDATE') {
    if ($_POST["title"] !== '') {
        $id = $_POST["id"];
        $title = $_POST["title"];
        $sql = "UPDATE ims_event SET title=:title WHERE id = :id";
        $query = $conn->prepare($sql);
        $query->bindParam(':title', $title, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        if($query->execute()){
            echo $save_success;
        }else{
            echo $error;
        }
    }
}

if ($_POST["action"] === 'DELETE') {
    if ($_POST["id"] !== '') {
        $id = $_POST["id"];
        $sql = "DELETE FROM ims_event WHERE ID = " . $id;
        $query = $conn->prepare($sql);
        if($query->execute()){
            echo $del_success;
        }else{
            echo $error;
        }
    }
}
