<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Bangkok");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();
    $sql_get = "SELECT * FROM v_ims_purchase_a_master WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_no" => $result['doc_no'],
            "doc_date" => $result['doc_date'],
            "supplier_id" => $result['supplier_id'],
            "supplier_name" => $result['supplier_name'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["doc_no"] !== '') {

        $doc_no = $_POST["doc_no"];
        $sql_find = "SELECT * FROM ims_purchase_a_master WHERE doc_no = '" . $doc_no . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["supplier_id"] !== '') {
        $table = "ims_purchase_a_master";
        $KeyAddData = $_POST["KeyAddData"];
        $doc_year = substr($_POST["doc_date"], 0, 4);
        $field = "doc_runno";
        $doc_type = "-PRH-";
        $doc_runno = LAST_ID_YEAR($conn, $table, $field, $doc_year);
        $doc_no = $doc_year . $doc_type . sprintf('%06s', $doc_runno);
        $supplier_id = $_POST["supplier_id"];
        $doc_date = $_POST["doc_date"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM " . $table . " WHERE doc_no = '" . $doc_no . "'";
        $stmt = $conn->query($sql_find);
        $nRows = $stmt->rowCount();

        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO " . $table . " (doc_no,supplier_id,doc_date,doc_year,doc_runno,KeyAddData,status)
                    VALUES (:doc_no,:supplier_id,:doc_date,:doc_year,:doc_runno,:KeyAddData,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
            $query->bindParam(':supplier_id', $supplier_id, PDO::PARAM_STR);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':doc_year', $doc_year, PDO::PARAM_STR);
            $query->bindParam(':doc_runno', $doc_runno, PDO::PARAM_STR);
            $query->bindParam(':KeyAddData', $KeyAddData, PDO::PARAM_STR);
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

    if ($_POST["doc_no"] != '') {

        $id = $_POST["id"];
        $doc_no = $_POST["doc_no"];
        $supplier_id = $_POST["supplier_id"];
        $status = $_POST["status"];
        $update_date = date('Y-m-d H:i:s');
        $sql_find = "SELECT * FROM ims_purchase_a_master WHERE doc_no = '" . $doc_no . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_purchase_a_master SET supplier_id=:supplier_id,status=:status            
            ,update_date=:update_date WHERE doc_no = :doc_no";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':supplier_id', $supplier_id, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':update_date', $update_date, PDO::PARAM_STR);
            $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
            if ($query->execute()) {
                echo $save_success;
            } else {
                echo $error;
            }
        }

    }
}


if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_purchase_a_master WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_purchase_a_master WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_PURCHASE') {

## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    $searchArray = array();

    ## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (doc_no LIKE :doc_no OR supplier_name LIKE :supplier_name)";
        $searchArray = array(
            'doc_no' => "%$searchValue%",
            'supplier_name' => "%$searchValue%",
        );
    }

    ## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_purchase_a_master WHERE 1=1");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    ## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_purchase_a_master WHERE 1=1 $searchQuery");
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    ## Fetch records
    $query_str = "SELECT * FROM v_ims_purchase_a_master WHERE 1=1 $searchQuery
                  ORDER BY $columnName $columnSortOrder LIMIT :length OFFSET :start";

    $stmt = $conn->prepare($query_str);

    // Bind search params
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {
        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "doc_no" => $row['doc_no'],
                "supplier_id" => $row['supplier_id'],
                "supplier_name" => $row['supplier_name'],
                "doc_date" => $row['doc_date'],
                "status" => $row['status'] === 'Active'
                    ? "<div class='text-success'>{$row['status']}</div>"
                    : "<div class='text-muted'>{$row['status']}</div>",
                "update" => "<button type='button' name='update' id='{$row['id']}' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='{$row['id']}' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_no" => $row['doc_no'],
                "supplier_id" => $row['supplier_id'],
                "select" => "<button type='button' name='select' id='{$row['doc_no']}@{$row['supplier_id']}' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='Select'>Select <i class='fa fa-check' aria-hidden='true'></i></button>",
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

