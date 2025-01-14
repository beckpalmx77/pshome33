<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM afront_contact WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "f_name" => $result['f_name'],
            "l_name" => $result['l_name'],
            "email" => $result['email'],
            "phone" => $result['phone'],
            "time_contact" => $result['time_contact'],
            "create_date" => $result['create_date'],
            "update_date" => $result['update_date'],
            "contact_name" => $result['contact_name'],
            "contact_date" => $result['contact_date'],
            "contact_time" => $result['contact_time'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["l_name"] !== '') {

        $l_name = $_POST["l_name"];
        $sql_find = "SELECT * FROM afront_contact WHERE l_name = '" . $l_name . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'UPDATE') {

    //if ($_POST["contact_name"] !== '') {
    $id = $_POST["id"];
    $contact_name = $_POST["contact_name"];
    $contact_date = $_POST["contact_date"];
    $contact_time = $_POST["contact_time"];
    $status = $_POST["status"];

    $date = new DateTime();
    $date->setTimestamp(time());
    $timestamp = $date->format(DateTime::RFC1123);

    $sql_find = "SELECT * FROM afront_contact WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        $sql_update = "UPDATE afront_contact SET contact_name=:contact_name,contact_date=:contact_date
            ,contact_time=:contact_time,status=:status,update_date=:update_date WHERE id = :id";
        $query = $conn->prepare($sql_update);
        $query->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
        $query->bindParam(':contact_date', $contact_date, PDO::PARAM_STR);
        $query->bindParam(':contact_time', $contact_time, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':update_date', $timestamp, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        echo $save_success;
    }

    //}
}


if ($_POST["action"] === 'GET_MESSAGE') {

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
        $searchQuery = " AND (f_name LIKE :f_name or
        l_name LIKE :l_name ) ";
        $searchArray = array(
            'f_name' => "%$searchValue%",
            'l_name' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM afront_contact ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM afront_contact WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records

    $sql_record = "SELECT * FROM afront_contact WHERE 1 " . $searchQuery;
    if ($columnName === 'create_date') {
        $sql_record .= " ORDER BY id DESC " . " LIMIT :limit,:offset";
    } else {
        $sql_record .= " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";
    }

    $stmt = $conn->prepare($sql_record);

    //$txt = $searchQuery . " | " . $columnName . " | " . $columnSortOrder ;
    //$my_file = fopen("msg.txt", "w") or die("Unable to open file!");
    //fwrite($my_file, $txt);
    //fclose($my_file);


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
                "f_name" => $row['f_name'],
                "l_name" => $row['l_name'],
                "email" => $row['email'],
                "phone" => $row['phone'],
                "time_contact" => $row['time_contact'],
                "create_date" => $row['create_date'],
                "update_date" => $row['update_date'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>+</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . $contact_y . "</div>" : "<div class='text-danger'> " . $contact_n . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "f_name" => $row['f_name'],
                "l_name" => $row['l_name'],
                "select" => "<button type='button' name='select' id='" . $row['f_name'] . "@" . $row['l_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
