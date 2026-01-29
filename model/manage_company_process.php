<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM ims_company WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array(
            "id" => $result['id'],
            "company_name" => $result['company_name'],
            "address_1" => $result['address_1'],
            "address_2" => $result['address_2'],
            "state" => $result['state'],
            "zip_code" => $result['zip_code'],
            "phone" => $result['phone'],
            "bank_name" => $result['bank_name'],
            "bank_account_name" => $result['bank_account_name'],
            "bank_account_no" => $result['bank_account_no'],
            "website" => $result['website']
        );
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["company_name"] !== '') {

        $company_name = $_POST["company_name"];
        $sql_find = "SELECT * FROM ims_company WHERE company_name = '" . $company_name . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["company_name"] !== '') {
        // Prepare variables
        $company_name = $_POST["company_name"];
        $address_1 = $_POST["address_1"];
        $address_2 = $_POST["address_2"];
        $state = $_POST["state"];
        $zip_code = $_POST["zip_code"];
        $phone = $_POST["phone"];
        $bank_name = $_POST["bank_name"];
        $bank_account_name = $_POST["bank_account_name"];
        $bank_account_no = $_POST["bank_account_no"];
        $website = $_POST["website"];

        // Check Duplicate
        $sql_find = "SELECT * FROM ims_company WHERE company_name = '" . $company_name . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO ims_company(company_name, address_1, address_2, state, zip_code, phone, bank_name, bank_account_name, bank_account_no, website) 
                    VALUES (:company_name, :address_1, :address_2, :state, :zip_code, :phone, :bank_name, :bank_account_name, :bank_account_no, :website)";
            $query = $conn->prepare($sql);
            $query->bindParam(':company_name', $company_name, PDO::PARAM_STR);
            $query->bindParam(':address_1', $address_1, PDO::PARAM_STR);
            $query->bindParam(':address_2', $address_2, PDO::PARAM_STR);
            $query->bindParam(':state', $state, PDO::PARAM_STR);
            $query->bindParam(':zip_code', $zip_code, PDO::PARAM_STR);
            $query->bindParam(':phone', $phone, PDO::PARAM_STR);
            $query->bindParam(':bank_name', $bank_name, PDO::PARAM_STR);
            $query->bindParam(':bank_account_name', $bank_account_name, PDO::PARAM_STR);
            $query->bindParam(':bank_account_no', $bank_account_no, PDO::PARAM_STR);
            $query->bindParam(':website', $website, PDO::PARAM_STR);

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

    if ($_POST["company_name"] != '') {

        $id = $_POST["id"];
        $company_name = $_POST["company_name"];
        $address_1 = $_POST["address_1"];
        $address_2 = $_POST["address_2"];
        $state = $_POST["state"];
        $zip_code = $_POST["zip_code"];
        $phone = $_POST["phone"];
        $bank_name = $_POST["bank_name"];
        $bank_account_name = $_POST["bank_account_name"];
        $bank_account_no = $_POST["bank_account_no"];
        $website = $_POST["website"];

        // Check exists
        $sql_find = "SELECT * FROM ims_company WHERE id = '" . $id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_company SET 
                            company_name=:company_name,
                            address_1=:address_1,
                            address_2=:address_2,
                            state=:state,
                            zip_code=:zip_code,
                            phone=:phone,
                            bank_name=:bank_name,
                            bank_account_name=:bank_account_name,
                            bank_account_no=:bank_account_no,
                            website=:website
                            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':company_name', $company_name, PDO::PARAM_STR);
            $query->bindParam(':address_1', $address_1, PDO::PARAM_STR);
            $query->bindParam(':address_2', $address_2, PDO::PARAM_STR);
            $query->bindParam(':state', $state, PDO::PARAM_STR);
            $query->bindParam(':zip_code', $zip_code, PDO::PARAM_STR);
            $query->bindParam(':phone', $phone, PDO::PARAM_STR);
            $query->bindParam(':bank_name', $bank_name, PDO::PARAM_STR);
            $query->bindParam(':bank_account_name', $bank_account_name, PDO::PARAM_STR);
            $query->bindParam(':bank_account_no', $bank_account_no, PDO::PARAM_STR);
            $query->bindParam(':website', $website, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_company WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_company WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_COMPANY') { // Renamed from GET_UNIT

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
        $searchQuery = " AND (company_name LIKE :company_name or 
                        phone LIKE :phone or 
                        website LIKE :website) ";
        $searchArray = array(
            'company_name' => "%$searchValue%",
            'phone' => "%$searchValue%",
            'website' => "%$searchValue%"
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_company ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_company WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_company WHERE 1 " . $searchQuery
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
                "id" => $row['id'],
                "company_name" => $row['company_name'],
                "phone" => $row['phone'],
                "website" => $row['website'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "company_name" => $row['company_name'],
                "select" => "<button type='button' name='select' id='" . $row['id'] . "@" . $row['company_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i></button>",
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
?>