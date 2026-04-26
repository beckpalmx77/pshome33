<?php
session_start();
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();
    $sql_get = "SELECT * FROM v_purchase_master WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "expense_date" => $result['expense_date'],
            "supplier_id" => $result['supplier_id'],
            "receipt_name" => $result['receipt_name'],
            "approve_status" => $result['approve_status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["doc_id"] !== '') {

        $doc_id = $_POST["doc_id"];
        $sql_find = "SELECT * FROM ims_purchase_master WHERE doc_id = '" . $doc_id . "'";
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
        $table = "ims_purchase_master";
        $KeyAddData = $_POST["KeyAddData"];
        $doc_year = substr($_POST["expense_date"], 0, 4);
        $field = "doc_runno";
        $doc_type = "-PRH-";
        $doc_runno = LAST_ID_YEAR($conn, $table, $field, $doc_year);
        $doc_id = $doc_year . $doc_type . sprintf('%06s', $doc_runno);
        $supplier_id = $_POST["supplier_id"];
        $expense_date = $_POST["expense_date"];
        $approve_status = $_POST["approve_status"];
        $sql_find = "SELECT * FROM " . $table . " WHERE doc_id = '" . $doc_id . "'";
        $stmt = $conn->query($sql_find);
        $nRows = $stmt->rowCount();

        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO " . $table . " (doc_id,supplier_id,expense_date,doc_year,doc_runno,KeyAddData,approve_status)
                    VALUES (:doc_id,:supplier_id,:expense_date,:doc_year,:doc_runno,:KeyAddData,:approve_status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
            $query->bindParam(':supplier_id', $supplier_id, PDO::PARAM_STR);
            $query->bindParam(':expense_date', $expense_date, PDO::PARAM_STR);
            $query->bindParam(':doc_year', $doc_year, PDO::PARAM_STR);
            $query->bindParam(':doc_runno', $doc_runno, PDO::PARAM_STR);
            $query->bindParam(':KeyAddData', $KeyAddData, PDO::PARAM_STR);
            $query->bindParam(':approve_status', $approve_status, PDO::PARAM_STR);
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

    if ($_POST["doc_id"] != '') {

        $id = $_POST["id"];
        $doc_id = $_POST["doc_id"];
        $supplier_id = $_POST["supplier_id"];
        $approve_status = $_POST["approve_status"];
        $update_date = date('Y-m-d H:i:s');
        $sql_find = "SELECT * FROM ims_purchase_master WHERE doc_id = '" . $doc_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_purchase_master SET supplier_id=:supplier_id,approve_status=:approve_status            
            ,update_date=:update_date WHERE doc_id = :doc_id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':supplier_id', $supplier_id, PDO::PARAM_STR);
            $query->bindParam(':approve_status', $approve_status, PDO::PARAM_STR);
            $query->bindParam(':update_date', $update_date, PDO::PARAM_STR);
            $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
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

    $sql_find = "SELECT * FROM ims_purchase_master WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_purchase_master WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_EXPENSE_MASTER') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    if ($columnName === 'doc_id') {
        $columnSortOrder = "desc";
    }

    $searchArray = array();

## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (doc_id LIKE :doc_id or
        receipt_name LIKE :receipt_name ) ";
        $searchArray = array(
            'doc_id' => "%$searchValue%",
            'receipt_name' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_expenses_master ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_expenses_master WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $query_str = "SELECT * FROM v_ims_expenses_master WHERE 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";

    $stmt = $conn->prepare($query_str);

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

            $approve_n = "รอตรวจสอบ";
            $approve_y = "อนุมัติ";

            $data[] = array(
                "doc_id" => $row['doc_id'],
                "receipt_name" => $row['receipt_name'],
                "expense_date" => $row['expense_date'],
                "exp_year" => $row['exp_year'],
                "exp_month" => $row['exp_month'],
                "inv" => $row['inv'],
                "month_name" => $row['month_name'],
                "payment_method" => $row['payment_method'],
                "total_amount" => $row['total_amount'],
                "approve_status" => $row['approve_status'] === 'Y' ? "<div class='text-success'>" . $approve_y . "</div>" : "<div class='text-muted'> " . $approve_n . "</div>",
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_id" => $row['doc_id'],
                "supplier_id" => $row['supplier_id'],
                "select" => "<button type='button' name='select' id='" . $row['doc_id'] . "@" . $row['supplier_id'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
