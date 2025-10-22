<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM m_config WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "config_id" => $result['config_id'],
            "description" => $result['description'],
            "config_value1" => $result['config_value1'],
            "config_value2" => $result['config_value2'],
            "config_value3" => $result['config_value3'],
            "config_value4" => $result['config_value4'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["description"] !== '') {

        $description = $_POST["description"];
        $sql_find = "SELECT * FROM m_config WHERE description = '" . $description . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["description"] !== '') {
        $config_id = "CONF-" . sprintf('%04s', LAST_ID($conn, "m_config", 'id'));
        $description = $_POST["description"];
        $config_value1 = $_POST["config_value1"];
        $config_value2 = $_POST["config_value2"];
        $config_value3 = $_POST["config_value3"];
        $config_value4 = $_POST["config_value4"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM m_config WHERE description = '" . $description . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO m_config(config_id,description,config_value1,config_value2
            ,config_value3,config_value4,status) 
            VALUES (:config_id,:description,:config_value1,:config_value2
            ,:config_value3,:config_value4,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':config_id', $config_id, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':config_value1', $config_value1, PDO::PARAM_STR);
            $query->bindParam(':config_value2', $config_value2, PDO::PARAM_STR);
            $query->bindParam(':config_value3', $config_value3, PDO::PARAM_STR);
            $query->bindParam(':config_value4', $config_value4, PDO::PARAM_STR);
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

    if ($_POST["description"] != '') {

        $id = $_POST["id"];
        $config_id = $_POST["config_id"];
        $description = $_POST["description"];
        $config_value1 = $_POST["config_value1"];
        $config_value2 = $_POST["config_value2"];
        $config_value3 = $_POST["config_value3"];
        $config_value4 = $_POST["config_value4"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM m_config WHERE config_id = '" . $config_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE m_config SET config_id=:config_id,description=:description
            ,config_value1=:config_value1,config_value2=:config_value2
            ,config_value3=:config_value3,config_value4=:config_value4
            ,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':config_id', $config_id, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':config_value1', $config_value1, PDO::PARAM_STR);
            $query->bindParam(':config_value2', $config_value2, PDO::PARAM_STR);
            $query->bindParam(':config_value3', $config_value3, PDO::PARAM_STR);
            $query->bindParam(':config_value4', $config_value4, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM m_config WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM m_config WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_CONFIG') {

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
        $searchQuery = " AND (config_id LIKE :config_id or
        description LIKE :description ) ";
        $searchArray = array(
            'config_id' => "%$searchValue%",
            'description' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM m_config ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM m_config WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM m_config WHERE 1 " . $searchQuery
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
                "config_id" => $row['config_id'],
                "description" => $row['description'],
                "config_value1" => $row['config_value1'],
                "config_value2" => $row['config_value2'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Active' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "config_id" => $row['config_id'],
                "description" => $row['description'],
                "select" => "<button type='button' name='select' id='" . $row['config_id'] . "@" . $row['description'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
