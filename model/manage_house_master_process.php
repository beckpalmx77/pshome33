<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM ims_house_master "
        . " WHERE ims_house_master.id = " . $id;

    //$myfile = fopen("myqeury_1.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_get);
    //fclose($myfile);

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "house_number" => $result['house_number'],
            "area_size" => $result['area_size'],
            "garbage_collection_fee" => $result['garbage_collection_fee'],
            "common_fee" => $result['common_fee'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["house_number"] !== '') {

        $house_number = $_POST["house_number"];
        $sql_find = "SELECT * FROM ims_house_master WHERE house_number = '" . $house_number . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["common_fee"] !== '') {


        $house_number = $_POST["house_number"];
        $area_size = $_POST["area_size"];
        $garbage_collection_fee = $_POST["garbage_collection_fee"];
        $common_fee = $_POST["common_fee"];
        $status = $_POST["status"];

        $sql_find = "SELECT * FROM ims_house_master WHERE house_number = '" . $house_number . "'";

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO ims_house_master(house_number,common_fee,status) 
                    VALUES (:house_number,:common_fee,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':house_number', $house_number, PDO::PARAM_STR);
            $query->bindParam(':area_size', $area_size, PDO::PARAM_STR);
            $query->bindParam(':garbage_collection_fee', $garbage_collection_fee, PDO::PARAM_STR);
            $query->bindParam(':common_fee', $common_fee, PDO::PARAM_STR);
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

    if ($_POST["common_fee"] != '') {

        $id = $_POST["id"];

        $house_number = $_POST["house_number"];
        $area_size = $_POST["area_size"];
        $garbage_collection_fee = $_POST["garbage_collection_fee"];
        $common_fee = $_POST["common_fee"];
        $status = $_POST["status"];

        $sql_find = "SELECT * FROM ims_house_master WHERE id = '" . $id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_house_master SET house_number=:house_number,area_size=:area_size,garbage_collection_fee=:garbage_collection_fee
            ,common_fee=:common_fee,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':house_number', $house_number, PDO::PARAM_STR);
            $query->bindParam(':area_size', $area_size, PDO::PARAM_STR);
            $query->bindParam(':garbage_collection_fee', $garbage_collection_fee, PDO::PARAM_STR);
            $query->bindParam(':common_fee', $common_fee, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_house_master WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_house_master WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_HOUSE_MASTER') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value
/*
    $txt = "DDD " . $columnSortOrder;
    $my_file = fopen("device_a.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/


    $searchArray = array();

## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (house_number LIKE :house_number or
        common_fee LIKE :common_fee ) ";
        $searchArray = array(
            'house_number' => "%$searchValue%",
            'common_fee' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_house_master ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_house_master WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_house_master WHERE 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset");

/*
    $txt = $searchQuery . " | " . $columnName . " | " . $columnSortOrder;
    $my_file = fopen("device_b.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/


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
                "house_number" => $row['house_number'],
                "area_size" => $row['area_size'],
                "garbage_collection_fee" => $row['garbage_collection_fee'],
                "common_fee" => $row['common_fee'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "house_number" => $row['house_number'],
                "common_fee" => $row['common_fee'],
                "select" => "<button type='button' name='select' id='" . $row['house_number'] . "@" . $row['common_fee'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
