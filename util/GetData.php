<?php

function GET_VALUE($conn, $sql)
{
    $ret_value = "";
    $query = $conn->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    if ($query->rowCount() > 0) {
        foreach ($results as $result) {
            $ret_value = $result->data;
        }
    }

    return $ret_value;
}