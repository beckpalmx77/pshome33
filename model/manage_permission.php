<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'INIT') {

    $table_main_name = $_POST["table_main_name"];
    $table_sub_name = $_POST["table_sub_name"];

    $sql_get_main = "SELECT * FROM " . $table_main_name . " ORDER BY sort " ;
    $statement = $conn->query($sql_get_main);
    $main_results = $statement->fetchAll(PDO::FETCH_ASSOC);
    $checkbox = "<ul>";
    foreach ($main_results as $main_result) {

        $checkbox .= "<input type='checkbox' id='" . $main_result['main_menu_id'] . "' name='menu_main' value='" . $main_result['main_menu_id'] . "'>" . " " . "<b>". $main_result['main_menu_id'] . " " . $main_result['label'].  "</b><br/>" ;

        $sql_get_sub = "SELECT * FROM " . $table_sub_name . " WHERE main_menu_id = '" . $main_result['main_menu_id'] . "' ORDER BY main_menu_id,sub_menu_id " ;
        $statement = $conn->query($sql_get_sub);
        $sub_results = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($sub_results as $sub_result) {

            $checkbox .= "&nbsp;&nbsp;&nbsp;" . "<input type='checkbox' id='" . $sub_result['sub_menu_id'] . "' name='menu_sub' value='" . $sub_result['sub_menu_id'] . "'>" . " " . $sub_result['sub_menu_id'] . " " . $sub_result['label'] . "<br/>";

        }
    }
    $checkbox .= "</ul>";

    echo $checkbox;
}

if ($_POST["action"] === 'LOAD_PERMISSION') {
    $permission_id = $_POST["permission_id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM ims_permission WHERE permission_id = '" . $permission_id . "'";
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "main_menu" => $result['main_menu'],
            "sub_menu" => $result['sub_menu'],
            "dashboard_page" => $result['dashboard_page'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'CHECK_DUP') {

    $permission_id = $_POST["permission_id"];
    $sql_find = "SELECT * FROM ims_permission WHERE permission_id = '" . $permission_id . "'";
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        echo "have";
    } else {
        echo "none";
    }

}

if ($_POST["action"] === 'SAVE') {

    $permission_id = $_POST["permission_id"];
    $permission_detail = $_POST["permission_detail"];
    $dashboard_page = $_POST["dashboard_page"];
    $main_list_value = $_POST["main_list_value"];
    $sub_list_value = $_POST["sub_list_value"];

    //$myfile = fopen("permission-param.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $permission_id  . " | " . $permission_detail . " | " . $dashboard_page
        //. " | " . $main_list_value . " | " . $sub_list_value );
    //fclose($myfile);

    if ($permission_id !=="" && $permission_detail !=="" && $main_list_value !=="" && $sub_list_value !=="") {
        $sql_find = "SELECT * FROM ims_permission WHERE permission_id = '" . $permission_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows <= 0) {
            $sql = "INSERT INTO ims_permission(permission_id,permission_detail,dashboard_page,main_menu,sub_menu) 
                    VALUES (:permission_id,:permission_detail,:dashboard_page,:main_list_value,:sub_list_value)";
            $query = $conn->prepare($sql);
            $query->bindParam(':permission_id', $permission_id, PDO::PARAM_STR);
            $query->bindParam(':permission_detail', $permission_detail, PDO::PARAM_STR);
            $query->bindParam(':dashboard_page', $dashboard_page, PDO::PARAM_STR);
            $query->bindParam(':main_list_value', $main_list_value, PDO::PARAM_STR);
            $query->bindParam(':sub_list_value', $sub_list_value, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();

            if ($lastInsertId) {
                echo json_encode(array("statusCode"=>200));
            } else {
                echo json_encode(array("statusCode"=>201));
            }

        } else {
            $sql_update = "UPDATE ims_permission SET permission_detail=:permission_detail,dashboard_page=:dashboard_page
            ,main_menu=:main_list_value,sub_menu=:sub_list_value            
            WHERE permission_id = :permission_id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':permission_detail', $permission_detail, PDO::PARAM_STR);
            $query->bindParam(':dashboard_page', $dashboard_page, PDO::PARAM_STR);
            $query->bindParam(':main_list_value', $main_list_value, PDO::PARAM_STR);
            $query->bindParam(':sub_list_value', $sub_list_value, PDO::PARAM_STR);
            $query->bindParam(':permission_id', $permission_id, PDO::PARAM_STR);
            $query->execute();
            echo json_encode(array("statusCode"=>200));
        }
    }
    else {
        echo json_encode(array("statusCode"=>201));
    }


}


if ($_POST["action"] === 'GET_PERMISSION') {
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
        $searchQuery = " AND (permission_id LIKE :permission_id or
        permission_detail LIKE :permission_detail) ";
        $searchArray = array(
            'permission_id' => "%$searchValue%",
            'permission_detail' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_permission ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_permission WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_permission WHERE 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset");

// Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

/*
    $myfile = fopen("job-getdata_permission.txt", "w") or die("Unable to open file!");
    fwrite($myfile, " Row Record = " . $row . " Row Record Per Page = " . $rowperpage);
    fclose($myfile);
*/

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id" => $row['id'],
                "permission_id" => $row['permission_id'],
                "permission_detail" => $row['permission_detail'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Active' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "permission_id" => $row['permission_id'],
                "permission_detail" => $row['permission_detail'],
                "select" => "<button type='button' name='select' id='" . $row['permission_id'] . "@" . $row['permission_detail'] ."' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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


