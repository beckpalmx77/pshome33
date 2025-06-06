<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

header('Content-Type: application/json');

if ($_POST["action"] === 'GET_CHECK_IN_OUT') {


    $userId = $_POST['userId'] ?? '';

    if (empty($userId)) {
        echo json_encode(['error' => 'userId is required']);
        exit;
    }

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
        $searchQuery = " AND (display_name LIKE :display_name or
        checkin_time LIKE :checkin_time ) ";
        $searchArray = array(
            'display_name' => "%$searchValue%",
            'checkin_time' => "%$searchValue%",
        );
    }

    /*
        $txt = $where_display_name;
        $my_file = fopen("device_a.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $txt);
        fclose($my_file);
    */

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_checkins_time WHERE user_id = '" . $userId . "' ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_checkins_time WHERE user_id = '" . $userId . "' " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records

    $sql_get_date = "SELECT * FROM v_checkins_time WHERE user_id = '" . $userId . "' ". $searchQuery .  " ORDER BY id DESC  " . " LIMIT :limit,:offset";

    $stmt = $conn->prepare($sql_get_date);

    /*
            $txt = $sql_get_date;
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
                "display_name" => $row['display_name'],
                "emp_name" => $row['emp_name'],
                "line_picture_profile" => $row['line_picture_profile'],
                "checkin_time" => $row['checkin_time'],
                "place_name" => $row['place_name'],
                "latitude" => $row['latitude'],
                "longitude" => $row['longitude'],
                "check_type" => $row['check_type'],
                "photo_path" => $row['photo_path'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "detail" => "<button type='button' name='detail' id='" . $row['id'] . "' class='btn btn-secondary btn-xs detail' data-toggle='tooltip' title='Detail'>Detail</button>",
                "remark" => $row['remark']
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "display_name" => $row['display_name'],
                "checkin_time" => $row['checkin_time'],
                "select" => "<button type='button' name='select' id='" . $row['display_name'] . "@" . $row['checkin_time'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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