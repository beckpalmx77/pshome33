<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_ims_house "
        . " WHERE v_ims_house.id = " . $id;

    //$myfile = fopen("myqeury_1.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_get);
    //fclose($myfile);

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "house_number" => $result['house_number'],
            "contact_name" => $result['contact_name'],
            "house_name" => $result['house_name'],
            "house_status" => $result['house_status'],
            "phone_number" => $result['phone_number'],
            "line_picture_profile" => $result['line_picture_profile'],
            "remark" => $result['remark'],
            "car_no1" => $result['car_no1'],
            "car_no2" => $result['car_no2'],
            "car_no3" => $result['car_no3'],
            "car_no4" => $result['car_no4'],
            "car_no5" => $result['car_no5'],
            "alley" => $result['alley']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["house_number"] !== '') {

        $house_number = $_POST["house_number"];
        $sql_find = "SELECT * FROM ims_house WHERE house_number = '" . $house_number . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["contact_name"] !== '') {
        $house_number = $_POST["house_number"];
        $contact_name = $_POST["contact_name"];
        $phone_number = $_POST["phone_number"];
        $alley = $_POST["alley"];
        $remark = $_POST["remark"];
        $house_status = $_POST["house_status"];
        $car_no1 = $_POST["car_no1"];
        $car_no2 = $_POST["car_no2"];
        $car_no3 = $_POST["car_no3"];
        $car_no4 = $_POST["car_no4"];
        $car_no5 = $_POST["car_no5"];

        // ป้องกัน SQL Injection โดยใช้ prepare
        $sql_find = "SELECT COUNT(*) FROM ims_house WHERE house_number = :house_number";
        $stmt = $conn->prepare($sql_find);
        $stmt->bindParam(':house_number', $house_number);
        $stmt->execute();
        $nRows = $stmt->fetchColumn();

        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO ims_house (
                        house_number, contact_name, phone_number, alley, remark,
                        car_no1, car_no2, car_no3, car_no4, car_no5, house_status
                    ) VALUES (
                        :house_number, :contact_name, :phone_number, :alley, :remark,
                        :car_no1, :car_no2, :car_no3, :car_no4, :car_no5, :house_status
                    )";
            $query = $conn->prepare($sql);
            $query->bindParam(':house_number', $house_number, PDO::PARAM_STR);
            $query->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
            $query->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
            $query->bindParam(':alley', $alley, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':car_no1', $car_no1, PDO::PARAM_STR);
            $query->bindParam(':car_no2', $car_no2, PDO::PARAM_STR);
            $query->bindParam(':car_no3', $car_no3, PDO::PARAM_STR);
            $query->bindParam(':car_no4', $car_no4, PDO::PARAM_STR);
            $query->bindParam(':car_no5', $car_no5, PDO::PARAM_STR);
            $query->bindParam(':house_status', $house_status, PDO::PARAM_STR);
            $query->execute();

            $lastInsertId = $conn->lastInsertId();
            echo $lastInsertId ? $save_success : $error;
        }
    }
}

if ($_POST["action"] === 'UPDATE') {
    if ($_POST["contact_name"] !== '') {
        $id = $_POST["id"];
        $house_number = $_POST["house_number"];
        $contact_name = $_POST["contact_name"];
        $phone_number = $_POST["phone_number"];
        $alley = $_POST["alley"];
        $remark = $_POST["remark"];
        $house_status = $_POST["house_status"];
        $car_no1 = $_POST["car_no1"];
        $car_no2 = $_POST["car_no2"];
        $car_no3 = $_POST["car_no3"];
        $car_no4 = $_POST["car_no4"];
        $car_no5 = $_POST["car_no5"];

        $sql_find = "SELECT COUNT(*) FROM ims_house WHERE id = :id";
        $stmt = $conn->prepare($sql_find);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $nRows = $stmt->fetchColumn();

        if ($nRows > 0) {
            $sql_update = "UPDATE ims_house SET 
                house_number = :house_number,
                contact_name = :contact_name,
                alley = :alley,
                phone_number = :phone_number,
                remark = :remark,
                car_no1 = :car_no1,
                car_no2 = :car_no2,
                car_no3 = :car_no3,
                car_no4 = :car_no4,
                car_no5 = :car_no5,
                house_status = :house_status
                WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':house_number', $house_number, PDO::PARAM_STR);
            $query->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
            $query->bindParam(':alley', $alley, PDO::PARAM_STR);
            $query->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':car_no1', $car_no1, PDO::PARAM_STR);
            $query->bindParam(':car_no2', $car_no2, PDO::PARAM_STR);
            $query->bindParam(':car_no3', $car_no3, PDO::PARAM_STR);
            $query->bindParam(':car_no4', $car_no4, PDO::PARAM_STR);
            $query->bindParam(':car_no5', $car_no5, PDO::PARAM_STR);
            $query->bindParam(':house_status', $house_status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }
    }
}


if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_house WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_house WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_HOUSE') {

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
        contact_name LIKE :contact_name ) ";
        $searchArray = array(
            'house_number' => "%$searchValue%",
            'contact_name' => "%$searchValue%",
        );
    }

    $where_house_number = "";

    if (($_SESSION['account_type']) === "house_user") {
        $where_house_number = " AND house_number = '" . $_SESSION['house_number'] . "' ";
    }

/*
    $txt = $where_house_number;
    $my_file = fopen("device_a.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_house WHERE 1 " . $where_house_number );
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_house WHERE 1 " . $where_house_number . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records

    $sql_get_date = "SELECT * FROM v_ims_house WHERE 1 " . $where_house_number . $searchQuery . " LIMIT :limit,:offset";

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
                "house_number" => $row['house_number'],
                "alley" => $row['alley'],
                "contact_name" => $row['contact_name'],
                "phone_number" => $row['phone_number'],
                "house_status" => $row['house_status'],
                "line_picture_profile" => $row['line_picture_profile'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "remark" => $row['remark']
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "house_number" => $row['house_number'],
                "contact_name" => $row['contact_name'],
                "select" => "<button type='button' name='select' id='" . $row['house_number'] . "@" . $row['contact_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
