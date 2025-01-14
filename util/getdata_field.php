<?php

function GetDataValue($conn, $sql_cmd)
{
    $row = $conn->query($sql_cmd)->fetch();
    if (empty($row["0"])) {
        $ret_value = 0;
    } else {
        $ret_value = $row["0"];
    }
    return $ret_value;
}