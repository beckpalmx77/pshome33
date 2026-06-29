<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM ims_petty_cash "
        . " WHERE ims_petty_cash.id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_no" => $result['doc_no'],
            "doc_date" => $result['doc_date'],
            "transaction_type" => $result['transaction_type'],
            "received_from" => $result['received_from'],
            "description" => $result['description'],
            "amount" => $result['amount'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["doc_no"] !== '') {

        $doc_no = $_POST["doc_no"];
        $sql_find = "SELECT * FROM ims_petty_cash WHERE doc_no = '" . $doc_no . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {

    if (!empty($_POST["doc_date"])) {

        // แปลงวันที่
        $doc_date_db = date("Y-m-d", strtotime($_POST["doc_date"]));
        $current_month = date("m", strtotime($doc_date_db));
        $current_year = date("Y", strtotime($doc_date_db));

        // สร้าง doc_no ใหม่
        $prefix = "CASH-" . $current_month . "-" . $current_year . "-";
        $sqlGetLastDocNo = "SELECT MAX(doc_no) FROM ims_petty_cash WHERE doc_no LIKE ?";
        $stmtLastDocNo = $conn->prepare($sqlGetLastDocNo);
        $stmtLastDocNo->execute([$prefix . '%']);
        $lastDocNo = $stmtLastDocNo->fetchColumn();

        $next_seq = 1;
        if ($lastDocNo) {
            $parts = explode('-', $lastDocNo);
            $last_seq = (int)end($parts);
            $next_seq = $last_seq + 1;
        }

        $new_doc_no = $prefix . sprintf('%04d', $next_seq);
        $doc_no = $new_doc_no;
        $doc_date = $doc_date_db; // ✔️ ใช้รูปแบบ YYYY-MM-DD
        $transaction_type = $_POST["transaction_type"];
        $received_from = $_POST["received_from"];
        $description = $_POST["description"];
        $amount = floatval($_POST["amount"]); // ✔️ float
        $status = $_POST["status"];

        // ตรวจสอบซ้ำ
        $sql_find = "SELECT COUNT(*) FROM ims_petty_cash WHERE doc_no = ?";
        $stmtCheck = $conn->prepare($sql_find);
        $stmtCheck->execute([$doc_no]);
        $nRows = $stmtCheck->fetchColumn();

        if ($nRows > 0) {
            echo $dup;
        } else {

            $sql = "INSERT INTO ims_petty_cash(doc_no, doc_date, transaction_type, received_from, description, amount, status)
                    VALUES (:doc_no, :doc_date, :transaction_type, :received_from, :description, :amount, :status)";

            $query = $conn->prepare($sql);
            $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':transaction_type', $transaction_type, PDO::PARAM_STR);
            $query->bindParam(':received_from', $received_from, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':amount', $amount);
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

    if ($_POST["doc_date"] != '') {

        $id = $_POST["id"];
        $doc_no = $_POST["doc_no"];
        $doc_date = $_POST["doc_date"];
        $transaction_type = $_POST["transaction_type"];
        $received_from = $_POST["received_from"];
        $description = $_POST["description"];
        $amount = $_POST["amount"];
        $status = $_POST["status"];

        $sql_find = "SELECT * FROM ims_petty_cash WHERE id = '" . $id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_petty_cash SET doc_no=:doc_no,doc_date=:doc_date,transaction_type=:transaction_type,received_from=:received_from,description=:description,amount=:amount,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
            $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
            $query->bindParam(':transaction_type', $transaction_type, PDO::PARAM_STR);
            $query->bindParam(':received_from', $received_from, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':amount', $amount, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_petty_cash WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_petty_cash WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_CASH') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $validColumns = array('id', 'doc_no', 'doc_date', 'description', 'transaction_type', 'amount', 'status');
    if (!in_array($columnName, $validColumns)) {
        $columnName = 'id';
    }
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
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_petty_cash ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_petty_cash WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_petty_cash WHERE 1 " . $searchQuery
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

            if($row['transaction_type']==='+') {
                $transaction_type = "รับเข้า";
            } else if($row['transaction_type']==='-') {
                $transaction_type = "จ่ายออก";
            }

            if ($row['status']==='N') {
                $status = "รออนุมัติ";
            } else if ($row['status']==='Y') {
                $status = "อนุมัติ";
            }

            $data[] = array(
                "id" => $row['id'],
                "doc_no" => $row['doc_no'],
                "doc_date" => $row['doc_date'],
                "transaction_type" => $transaction_type,
                "received_from" => $row['received_from'],
                "description" => $row['description'],
                "amount" => $row['amount'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . $status . "</div>" : "<div class='text-muted'> " . $status . "</div>"
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
