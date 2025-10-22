<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

$table_name = $_POST["table_name"];

if ($_POST["action"] === 'GET_COUNT_RECORDS') {
    $return_arr = array();
    $sql_get = "SELECT count(*) as record_counts  FROM " . $table_name;

/*
    $sql_sqlsvr1 .= $sql_get . "\n\r";
    $myfile = fopen($table_name . "-1.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $sql_sqlsvr1);
    fclose($myfile);
*/

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $result) {
        $record = $result['record_counts'];
    }
    echo $record;
}

if ($_POST["action"] === 'GET_COUNT_RECORDS_COND') {
    $cond = $_POST["cond"];
    $return_arr = array();
    $sql_get = "SELECT count(*) as record_counts  FROM " . $table_name . " " . $cond;

/*
    $sql_sqlsvr2 .= $sql_get . "\n\r";
    $myfile = fopen($table_name . "-2.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $sql_sqlsvr2);
    fclose($myfile);
*/

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $result) {
        $record = $result['record_counts'];
    }
    echo $record;
}

if ($_POST["action"] === 'GET_SUM_RESULT_COND') {
    $field = $_POST["field"];
    $cond = $_POST["cond"];
    $return_arr = array();
    $sql_get = "SELECT SUM(" .$field .") as sum_result  FROM " . $table_name . $cond;

/*
    $sql_sqlsvr2 .= $sql_get . "\n\r";
    $myfile = fopen($table_name . "aleave-2.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $sql_sqlsvr2);
    fclose($myfile);
*/
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $result) {
        $sum_result = $result['sum_result'];
    }
    echo $sum_result;
}