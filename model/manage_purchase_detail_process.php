<?php
session_start();
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];
    $doc_no = $_POST["doc_no"];
    $table_name = $_POST["table_name"];

    $return_arr = array();

    $sql_get = "SELECT * FROM " . $table_name . " WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_no" => $result['doc_no'],
            "doc_date" => $result['doc_date'],
            "product_id" => $result['product_id'],
            "name_t" => $result['product_name'],
            "quantity" => $result['quantity'],
            "price" => $result['price'],
            "total_price" => $result['total_price'],
            "unit_id" => $result['unit_id'],
            "unit_name" => $result['unit_name']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action_detail"] === 'ADD') {
    if ($_POST["doc_date"] !== '') {

        if ($_POST["KeyAddDetail"] !== '') {
            $doc_no = $_POST["KeyAddDetail"];
            $table_name = "ims_purchase_detail_temp";
        } else {
            $doc_no = $_POST["doc_no_detail"];
            $table_name = "ims_purchase_detail";
        }

        $doc_date = $_POST["doc_date_detail"];
        $product_id = $_POST["product_id"];
        $unit_id = $_POST["unit_id"];
        $quantity = $_POST["quantity"];
        $price = $_POST["price"];

        $sql_find = "SELECT count(*) as row FROM " . $table_name . " WHERE doc_no = '" . $doc_no . "'";
        $row = $conn->query($sql_find)->fetch();
        if (empty($row["0"])) {
            $line_no = 1;
        } else {
            $line_no = $row["0"] + 1;
        }
        $sql = "INSERT INTO " . $table_name . " (doc_no,doc_date,product_id,unit_id,quantity,price,line_no) 
            VALUES (:doc_no,:doc_date,:product_id,:unit_id,:quantity,:price,:line_no)";
        $query = $conn->prepare($sql);
        $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
        $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
        $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
        $query->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);
        $query->bindParam(':quantity', $quantity, PDO::PARAM_STR);
        $query->bindParam(':price', $price, PDO::PARAM_STR);
        $query->bindParam(':line_no', $line_no, PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            echo $save_success;
        } else {
            echo $error . " | " . $doc_no . " | " . $line_no . " | " . $product_id . " | " . $quantity . " | " . $unit_id;
        }

    }
}


if ($_POST["action_detail"] === 'UPDATE') {

    if ($_POST["$product_id"] !== '') {

        if ($_POST["KeyAddDetail"] !== '') {
            $doc_no = $_POST["KeyAddDetail"];
            $table_name = "ims_purchase_detail_temp";
        } else {
            $doc_no = $_POST["doc_no_detail"];
            $table_name = "ims_purchase_detail";
        }

        $doc_date = $_POST["doc_date_detail"];
        $id = $_POST["detail_id"];
        $product_id = $_POST["product_id"];
        $quantity = $_POST["quantity"];
        $unit_id = $_POST["unit_id"];
        $price = $_POST["price"];

        $sql_find = "SELECT count(*) as row FROM " . $table_name . " WHERE id = '" . $id . "'";

        $row = $conn->query($sql_find)->fetch();
        if (empty($row["0"])) {
            echo $error;
        } else {
            $sql_update = "UPDATE " . $table_name
                . " SET doc_date=:doc_date,product_id=:product_id,quantity=:quantity "
                . ",price=:price,unit_id=:unit_id "
                . " WHERE id = :id ";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $query->bindParam(':quantity', $quantity, PDO::PARAM_STR);
            $query->bindParam(':price', $price, PDO::PARAM_STR);
            $query->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            if($query->execute()){
                echo $save_success;
            }else{
                echo $error;
            }
        }

    }
}

if ($_POST["action_detail"] === 'DELETE') {

    if ($_POST["$product_id"] !== '') {

        if ($_POST["KeyAddDetail"] !== '') {
            $doc_no = $_POST["KeyAddDetail"];
            $table_name = "ims_purchase_detail_temp";
        } else {
            $doc_no = $_POST["doc_no_detail"];
            $table_name = "ims_purchase_detail";
        }

        $id = $_POST["detail_id"];
        $product_id = $_POST["product_id"];
        $quantity = $_POST["quantity"];
        $unit_id = $_POST["unit_id"];
        $sql_find = "SELECT * FROM " . $table_name . " WHERE id = " . $id;
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            try {
                $sql = "DELETE FROM " . $table_name . " WHERE id = " . $id;
                $query = $conn->prepare($sql);
                $query->execute();

                Reorder_Record_By_DocNO($conn, $table_name, $doc_no);

                echo $del_success;

            } catch (Exception $e) {
                echo 'Message: ' . $e->getMessage();
            }
        }


    }
}

if ($_POST["action"] === 'SAVE_DETAIL') {

    if ($_POST["KeyAddData"] != '') {

        $KeyAddData = $_POST["KeyAddData"];

        $sql_find = "SELECT * FROM ims_purchase_master WHERE KeyAddData = '" . $KeyAddData . "'";
        $statement = $conn->query($sql_find);
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $result) {
            $doc_no = $result['doc_no'];
            $doc_date = $result['doc_date'];
        }

        $sql_find_detail = "SELECT * FROM ims_purchase_detail_temp WHERE doc_no = '" . $KeyAddData . "'";
        $statement = $conn->query($sql_find_detail);
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $result) {

            $sql = "INSERT INTO ims_purchase_detail (doc_no,doc_date,product_id,unit_id,quantity,line_no) 
            VALUES (:doc_no,:doc_date,:product_id,:unit_id,:quantity,:line_no)";
            $query = $conn->prepare($sql);
            $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':product_id', $result['product_id'], PDO::PARAM_STR);
            $query->bindParam(':unit_id', $result['unit_id'], PDO::PARAM_STR);
            $query->bindParam(':quantity', $result['quantity'], PDO::PARAM_STR);
            $query->bindParam(':line_no', $result['line_no'], PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();
        }

        if ($lastInsertId) {
            echo $save_success;
        } else {
            echo $error;
        }

    }

}

if ($_POST["action"] === 'GET_PURCHASE_DETAIL') {

    ## Read value
    $table_name = $_POST['table_name'];
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
        $searchQuery = " AND (doc_no LIKE :doc_no or
        doc_date LIKE :doc_date ) ";
        $searchArray = array(
            'doc_no' => "%$searchValue%",
            'doc_date' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM " . $table_name . " WHERE DOC_NO = '" . $_POST["doc_no"] . "'");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM " . $table_name . " WHERE DOC_NO = '" . $_POST["doc_no"] . "'");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];


    $query_str = "SELECT * FROM " . $table_name . " WHERE doc_no = '" . $_POST["doc_no"] . "'"
        . " ORDER BY line_no ";

    $stmt = $conn->prepare($query_str);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id" => $row['id'],
                "doc_no" => $row['doc_no'],
                "doc_date" => $row['doc_date'],
                "line_no" => $row['line_no'],
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "quantity" => number_format($row['quantity'],2),
                "price" => number_format($row['price'],2),
                "total_price" => number_format($row['total_price'],2),
                "unit_id" => $row['unit_id'],
                "unit_name" => $row['unit_name'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_no" => $row['doc_no'],
                "doc_date" => $row['doc_date'],
                "select" => "<button type='button' name='select' id='" . $row['doc_no'] . "@" . $row['doc_date'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

