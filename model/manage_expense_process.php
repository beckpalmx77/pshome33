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
            "inv" => $result['inv'],
            "category_id" => $result['category_id'],
            "category_name" => $result['category_name'],
            "description" => $result['description'],
            "qty" => $result['qty'],
            "unit_id" => $result['unit_id'],
            "unit_name" => $result['unit_name'],
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

    if (!empty($_POST["expense_date"])) {

        $expense_date = $_POST["expense_date"];
        $exp_month = substr($_POST["expense_date"], 3, 2);
        $exp_year = substr($_POST["expense_date"], 6, 4);
        $category_id = $_POST["category_id"];
        $description = $_POST["description"];
        $approve_status = $_POST["approve_status"];
        $qty = $_POST["qty"];
        $inv = $_POST["inv"];
        $unit_id = $_POST["unit_id"];
        $amount = $_POST["amount"];
        $remark = $_POST["remark"];

/*
        $sql_data = $expense_date . " | " . $category_id . " | " . $description . " | " . $approve_status . " | " . $qty . " | " . $unit_id . " | " . $amount . " | " . $remark;
        $myfile = fopen("ft2.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $sql_data);
        fclose($myfile);
*/

        $sql = "INSERT INTO ims_expenses(expense_date,exp_month,exp_year,category_id,description,qty,unit_id,amount,remark,inv)
            VALUES (:expense_date,:exp_month,:exp_year,:category_id,:description,:qty,:unit_id,:amount,:remark,:inv)";
        $query = $conn->prepare($sql);
        $query->bindParam(':expense_date', $expense_date, PDO::PARAM_STR);
        $query->bindParam(':exp_month', $exp_month, PDO::PARAM_STR);
        $query->bindParam(':exp_year', $exp_year, PDO::PARAM_STR);
        $query->bindParam(':category_id', $category_id, PDO::PARAM_STR);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':qty', $qty, PDO::PARAM_STR);
        $query->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);
        $query->bindParam(':amount', $amount, PDO::PARAM_STR);
        $query->bindParam(':remark', $remark, PDO::PARAM_STR);
        $query->bindParam(':inv', $inv, PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $conn->lastInsertId();
        if ($lastInsertId) {
            echo $save_success;
        } else {
            echo $error;
        }
    }
}


if ($_POST["action"] === 'UPDATE') {

    if (!empty($_POST["expense_date"])) {

        $id = $_POST["id"];
        $expense_date = $_POST["expense_date"];
        $exp_month = substr($_POST["expense_date"], 3, 2);
        $exp_year = substr($_POST["expense_date"], 6, 4);
        $category_id = $_POST["category_id"];
        $description = $_POST["description"];
        $approve_status = $_POST["approve_status"];
        $qty = $_POST["qty"];
        $unit_id = $_POST["unit_id"];
        $amount = $_POST["amount"];
        $remark = $_POST["remark"];
        $inv = $_POST["inv"];

        $sql_update = "UPDATE ims_expenses SET expense_date=:expense_date,exp_month=:exp_month,exp_year=:exp_year
            ,category_id=:category_id,description=:description
            ,qty=:qty,unit_id=:unit_id,amount=:amount,remark=:remark,approve_status=:approve_status,inv=:inv
            WHERE id = :id";
        $query = $conn->prepare($sql_update);
        $query->bindParam(':expense_date', $expense_date, PDO::PARAM_STR);
        $query->bindParam(':exp_month', $exp_month, PDO::PARAM_STR);
        $query->bindParam(':exp_year', $exp_year, PDO::PARAM_STR);
        $query->bindParam(':category_id', $category_id, PDO::PARAM_STR);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':qty', $qty, PDO::PARAM_STR);
        $query->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);
        $query->bindParam(':amount', $amount, PDO::PARAM_STR);
        $query->bindParam(':remark', $remark, PDO::PARAM_STR);
        $query->bindParam(':approve_status', $approve_status, PDO::PARAM_STR);
        $query->bindParam(':inv', $inv, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        echo $save_success;

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

            $approve_n = "ยังไม่ยืนยัน (รอตรวจสอบ)";
            $approve_y = "ยืนยันรายการ (อนุมัติ)";

            $data[] = array(
                "expense_date" => $row['expense_date'],
                "exp_month" => $row['exp_month'],
                "month_name" => $row['month_name'],
                "exp_year" => $row['exp_year'],
                "category_id" => $row['category_id'],
                "category_name" => $row['category_name'],
                "description" => $row['description'],
                "qty" => $row['qty'],
                "unit_id" => $row['unit_id'],
                "unit_name" => $row['unit_name'],
                "inv" => $row['inv'],
                "amount" => $row['amount'],
                "remark" => $row['remark'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "approve_status" => $row['approve_status'] === 'Y' ? "<div class='text-success'>" . $approve_y . "</div>" : "<div class='text-muted'> " . $approve_n . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "expense_date" => $row['expense_date'],
                "category_id" => $row['category_id'],
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
