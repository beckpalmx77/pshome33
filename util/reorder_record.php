<?php

function Reorder_Record($conn,$table) {
    $stmt = $conn->prepare("SELECT * FROM " . $table . " Order By id");
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $loop = 1;
    foreach ($empRecords as $row) {
        Update_Record($conn, $table, $row['id'], $loop);
        $loop++;
    }
}

function Reorder_Record_By_DocNO($conn,$table,$doc_no) {
    $stmt = $conn->prepare("SELECT * FROM " . $table . " where doc_no = '" . $doc_no.  "' Order By id");
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $loop = 1;
    foreach ($empRecords as $row) {
        Update_Record($conn, $table, $row['id'], $loop);
        $loop++;
    }
}

function Update_Record($conn,$table,$id,$line_no) {
    $sql_update = "UPDATE ". $table . " SET line_no = " . $line_no . " WHERE id = " . $id;
    $query = $conn->prepare($sql_update);
    $query->execute();
}

