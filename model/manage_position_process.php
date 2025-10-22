<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM mposition "
        . " WHERE mposition.id = " . $id;

    //$myfile = fopen("myqeury_1.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_get);
    //fclose($myfile);

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "position_id" => $result['position_id'],
            "position_desc" => $result['position_desc'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["position_id"] !== '') {

        $position_id = $_POST["position_id"];
        $sql_find = "SELECT * FROM mposition WHERE position_id = '" . $position_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["position_desc"] !== '') {
        //$position_id = "D" . sprintf('%03s', LAST_ID($conn, "mposition", 'id'));
        $position_id = $_POST["position_id"];
        $position_desc = $_POST["position_desc"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM mposition WHERE position_id = '" . $position_id . "'";

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO mposition(position_id,position_desc,status) 
                    VALUES (:position_id,:position_desc,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':position_id', $position_id, PDO::PARAM_STR);
            $query->bindParam(':position_desc', $position_desc, PDO::PARAM_STR);
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

    if ($_POST["position_desc"] != '') {

        $id = $_POST["id"];
        $position_id = $_POST["position_id"];
        $position_desc = $_POST["position_desc"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM mposition WHERE id = '" . $id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE mposition SET position_id=:position_id,position_desc=:position_desc,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':position_id', $position_id, PDO::PARAM_STR);
            $query->bindParam(':position_desc', $position_desc, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM mposition WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM mposition WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_POSITION') {

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
        $searchQuery = " AND (position_id LIKE :position_id or
        position_desc LIKE :position_desc ) ";
        $searchArray = array(
            'position_id' => "%$searchValue%",
            'position_desc' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM mposition ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM mposition WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM mposition WHERE 1 " . $searchQuery
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
                "position_id" => $row['position_id'],
                "position_desc" => $row['position_desc'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "position_id" => $row['position_id'],
                "position_desc" => $row['position_desc'],
                "select" => "<button type='button' name='select' id='" . $row['position_id'] . "@" . $row['position_desc'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
