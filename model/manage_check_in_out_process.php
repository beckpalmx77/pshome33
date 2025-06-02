<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_checkins "
        . " WHERE v_checkins.id = " . $id;

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "display_name" => $result['display_name'],
            "checkin_time" => $result['checkin_time'],
            "place_name" => $result['place_name'],
            "latitude" => $result['latitude'],
            "longitude" => $result['longitude'],
            "check_type" => $result['check_type'],
            "images" => $result['photo_path'],
            "remark" => $result['remark']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["display_name"] !== '') {

        $display_name = $_POST["display_name"];
        $sql_find = "SELECT * FROM v_checkins WHERE display_name = '" . $display_name . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'UPDATE') {

    if ($_POST["checkin_time"] != '') {

        $id = $_POST["id"];
        $display_name = $_POST["display_name"];
        $checkin_time = $_POST["checkin_time"];
        $remark = $_POST["remark"];
        $sql_find = "SELECT * FROM v_checkins WHERE id = '" . $id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE v_checkins SET display_name=:display_name,checkin_time=:checkin_time,alley=:alley,phone_number=:phone_number,remark=:remark            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':display_name', $display_name, PDO::PARAM_STR);
            $query->bindParam(':checkin_time', $checkin_time, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM v_checkins WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM v_checkins WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_CHECK_IN_OUT') {

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
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_checkins WHERE 1 ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_checkins WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records

    $sql_get_date = "SELECT * FROM v_checkins WHERE 1=1 ORDER BY id DESC  " . $searchQuery . " LIMIT :limit,:offset";

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

            $mapLink = "";
            if (!empty($row['latitude']) && !empty($row['longitude'])) {
                $mapLink = "<a href='https://www.google.com/maps?q={$row['latitude']},{$row['longitude']}' target='_blank' class='btn btn-sm btn-outline-primary' data-toggle='tooltip' title='เปิดแผนที่'>
                        <i class='fa fa-map-marker'></i> Map
                    </a>";
            }

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
                "map" => $mapLink, // เพิ่มปุ่ม Google Map
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
