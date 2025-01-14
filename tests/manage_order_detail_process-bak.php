<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();
    $sql_get = "SELECT * FROM v_order_detail WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_no" => $result['doc_no'],
            "doc_date" => $result['doc_date'],
            "product_id" => $result['product_id'],
            "unit_id" => $result['unit_id'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["doc_no"] !== '') {

        $doc_no = $_POST["doc_no"];
        $sql_find = "SELECT * FROM ims_order_detail WHERE doc_no = '" . $doc_no . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["product_id"] !== '') {
        $table = "ims_order_detail";
        $doc_year = substr($_POST["doc_date"], 0, 4);
        $field = "doc_runno";
        $doc_type = "-ORD-";
        $doc_runno = LAST_ID_YEAR($conn, $table, $field, $doc_year);
        $doc_no = $doc_year . $doc_type . sprintf('%06s', $doc_runno);
        $product_id = $_POST["product_id"];
        $doc_date = $_POST["doc_date"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM " . $table . " WHERE doc_no = '" . $doc_no . "'";
        $stmt = $conn->query($sql_find);
        $nRows = $stmt->rowCount();

        echo $doc_runno;

        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO " . $table . " (doc_no,product_id,doc_date,doc_year,doc_runno,status)
                    VALUES (:doc_no,:product_id,:doc_date,:doc_year,:doc_runno,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
            $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':doc_year', $doc_year, PDO::PARAM_STR);
            $query->bindParam(':doc_runno', $doc_runno, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();
            if ($lastInsertId) {
                echo $save_success;
            } else {
                echo $error;
            }
        }
    }
}


if ($_POST["action"] === 'UPDATE') {

    if ($_POST["product_id"] != '') {

        $id = $_POST["id"];
        $doc_no = $_POST["doc_no"];
        $product_id = $_POST["product_id"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM ims_order_detail WHERE doc_no = '" . $doc_no . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_order_detail SET doc_no=:doc_no,product_id=:product_id,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
            $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_order_detail WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_order_detail WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_ORDERDETAIL') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    if ($columnName === 'doc_no') {
        $columnSortOrder = "desc";
    }

    $searchArray = array();

## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (doc_no LIKE :doc_no or
        product_id LIKE :product_id ) ";
        $searchArray = array(
            'doc_no' => "%$searchValue%",
            'product_id' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_order_detail ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_order_detail WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $query_str = "SELECT * FROM v_order_detail WHERE 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";

    $stmt = $conn->prepare("SELECT * FROM v_order_detail WHERE 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset");

// Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "doc_no" => $row['doc_no'],
                "doc_date" => $row['doc_date'],
                "line_no" => $row['line_no'],
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "quantity" => $row['quantity'],
                "unit_id" => $row['unit_id'],
                "unit_name" => $row['unit_name'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_no" => $row['doc_no'],
                "product_id" => $row['product_id'],
                "select" => "<button type='button' name='select' id='" . $row['doc_no'] . "@" . $row['product_id'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
</button>",
            );
        }

    }

## Response Return Value
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);


}
