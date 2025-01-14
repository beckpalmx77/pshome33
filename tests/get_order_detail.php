<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_ORDERDETAIL') {

    $draw = 1;
    $query = "SELECT * FROM v_order_detail WHERE doc_no = '" . $_POST["doc_no"] . "'";

    $stmt = $conn->prepare($query);
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    $stmt = $conn->prepare($query);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    $stmt = $conn->prepare($query);
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
