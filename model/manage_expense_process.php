<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/reorder_record.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_ims_expenses WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "expense_date" => $result['expense_date'],
            "category" => $result['category'],
            "description" => $result['description'],
            "qty" => $result['qty'],
            "amount" => $result['amount'],
            "remark" => $result['remark'],
            "approve_status" => $result['approve_status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["expense_date"] !== '') {

        $expense_date = $_POST["expense_date"];
        $sql_find = "SELECT * FROM ims_expenses WHERE expense_date = '" . $expense_date . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {

    if ($_POST["expense_date"] != '') {

        $expense_date = $_POST["expense_date"];
        $category = $_POST["category"];
        $description = $_POST["description"];
        $approve_status = $_POST["approve_status"];
        $qty = $_POST["qty"];
        $remark = $_POST["remark"];
        $unit_id = $_POST["unit_id"];
        $picture = "product-001.png";
        $sql_find = "SELECT * FROM ims_expenses WHERE expense_date = '" . $expense_date . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO ims_expenses(expense_date,category,description,qty,remark,unit_id,picture,approve_status)
            VALUES (:expense_date,:category,:description,:qty,:remark,:unit_id,:picture,:approve_status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':expense_date', $expense_date, PDO::PARAM_STR);
            $query->bindParam(':category', $category, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':qty', $qty, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);
            $query->bindParam(':picture', $picture, PDO::PARAM_STR);
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

    if ($_POST["expense_date"] != '') {

        $id = $_POST["id"];
        $expense_date = $_POST["expense_date"];
        $category = $_POST["category"];
        $description = $_POST["description"];
        $approve_status = $_POST["approve_status"];
        $qty = $_POST["qty"];
        $remark = $_POST["remark"];
        $unit_id = $_POST["unit_id"];
        $picture = "product-001.png";
        $sql_find = "SELECT * FROM ims_expenses WHERE expense_date = '" . $expense_date . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_expenses SET category=:category,description=:description,approve_status=:approve_status
            ,qty=:qty,remark=:remark,unit_id=:unit_id,picture=:picture
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':category', $category, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':qty', $qty, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);
            $query->bindParam(':picture', $picture, PDO::PARAM_STR);
            $query->bindParam(':approve_status', $approve_status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_expenses WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_expenses WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            Reorder_Record($conn, "ims_expenses");
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_EXPENSE') {

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
        $searchQuery = " AND (category LIKE :category OR
        description LIKE :description OR         
        approve_status LIKE :approve_status ) ";
        $searchArray = array(
            'category' => "%$searchValue%",
            'description' => "%$searchValue%",
            'approve_status' => "%$searchValue%"
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_expenses ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_expenses WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM v_ims_expenses WHERE 1 " . $searchQuery
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
                "expense_date" => $row['expense_date'],
                "category" => $row['category'],
                "description" => $row['description'],
                "qty" => $row['qty'],
                "amount" => $row['amount'],
                "remark" => $row['remark'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "approve_status" => $row['approve_status'] === 'Active' ? "<div class='text-success'>" . $row['approve_status'] . "</div>" : "<div class='text-muted'> " . $row['approve_status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "expense_date" => $row['expense_date'],
                "category" => $row['category'],
                "unit_id" => $row['unit_id'],
                "unit_name" => $row['unit_name'],
                "select" => "<button type='button' name='select' id='" . $row['expense_date'] . "@" . $row['category'] . "@" . $row['unit_id'] . "@" . $row['unit_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
