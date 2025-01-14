<?php

function LAST_ID_YEAR($conn, $table, $field, $doc_year)
{
    $query_str = "select " . $field . " from " . $table
        . " where doc_year = " . $doc_year
        . " order by " . $field . " desc limit 1 ";
    $row = $conn->query($query_str)->fetch();
    if (empty($row["0"])) {
        $ret_value = 1;
    } else {
        $ret_value = $row["0"] + 1;
    }
    return $ret_value;
}

function LAST_ID($conn, $table, $field)
{
    $row = $conn->query("select " . $field . " from " . $table . " order by " . $field . " desc limit 1 ")->fetch();
    if (empty($row["0"])) {
        $ret_value = 1;
    } else {
        $ret_value = $row["0"] + 1;
    }
    return $ret_value;
}

function LAST_DOCUMENT_NUMBER($conn,$field,$table,$cond)
{
    $sql_cmd = "select count(" . $field . ") as record_number from " . $table . " ". $cond;
    $row = $conn->query($sql_cmd)->fetch();
    if (empty($row["0"])) {
        $ret_value = 1;
    } else {
        $ret_value = $row["0"] + 1;
    }
    return $ret_value;
}

function LAST_ID_COND($conn, $table, $cond ,$field)
{
    $row = $conn->query("select count(" . $field . ") as record_number from " . $table
         . " where sub_menu_id like '" . $cond . "%'")->fetch();
    if (empty($row["0"])) {
        $ret_value = 1;
    } else {
        $ret_value = $row["0"] + 1;
    }
    return $ret_value;
}