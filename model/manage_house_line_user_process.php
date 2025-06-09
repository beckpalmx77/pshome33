<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM ims_house_line_user "
        . " WHERE ims_house_line_user.id = " . $id;

    //$myfile = fopen("myqeury_1.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_get);
    //fclose($myfile);

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "line_user_id" => $result['line_user_id'],
            "line_user_name" => $result['line_user_name'],
            "house_number" => $result['house_number'],
            "f_name" => $result['f_name'],
            "l_name" => $result['l_name'],
            "line_phone" => $result['line_phone'],
            "line_email" => $result['line_email'],
            "line_picture_profile" => $result['line_picture_profile'],
            "status" => $result['status'],
            "user_type" => $result['user_type']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["line_user_id"] !== '') {

        $line_user_id = $_POST["line_user_id"];
        $sql_find = "SELECT * FROM ims_house_line_user WHERE line_user_id = '" . $line_user_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'UPDATE') {
    if (!empty($_POST["line_user_name"]) && !empty($_POST["id"])) {
        $id = $_POST["id"];
        $line_user_id = $_POST["line_user_id"];
        $line_user_name = $_POST["line_user_name"];
        $line_phone = $_POST["line_phone"];
        $user_type = $_POST["user_type"];
        $f_name = $_POST["f_name"];
        $l_name = $_POST["l_name"];
        $line_picture_profile = $_POST["line_picture_profile"];

/*
        $txt = "line id = " . $line_user_id . "\n\rline user name = " . $line_user_name . "\n\rphone = " . $line_phone . "\n\ruser type = " . $user_type
            . "\n\rf_name = " . $f_name . "\n\rl_name = " . $l_name . "\n\r pic = " . $line_picture_profile;
        $myfile = fopen("myqeury_1.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $txt);
        fclose($myfile);
*/

        $sql_find = "SELECT COUNT(*) FROM ims_house_line_user WHERE id = :id";
        $stmt = $conn->prepare($sql_find);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $nRows = $stmt->fetchColumn();

        if ($nRows > 0) {
            $sql_update = "UPDATE ims_house_line_user SET                
                line_user_name = :line_user_name,
                line_phone = :line_phone,
                user_type = :user_type,                
                l_name = :l_name,
                f_name = :f_name,
                line_picture_profile = :line_picture_profile
                WHERE id = :id";

            $query = $conn->prepare($sql_update);
            $query->bindParam(':line_user_name', $line_user_name, PDO::PARAM_STR);
            $query->bindParam(':line_phone', $line_phone, PDO::PARAM_STR);
            $query->bindParam(':user_type', $user_type, PDO::PARAM_STR);
            $query->bindParam(':l_name', $l_name, PDO::PARAM_STR);
            $query->bindParam(':f_name', $f_name, PDO::PARAM_STR);
            $query->bindParam(':line_picture_profile', $line_picture_profile, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_INT);

            if ($query->execute()) {
                echo "บันทึกข้อมูลเรียบร้อยแล้ว";
            } else {
                echo "เกิดข้อผิดพลาดในการบันทึก";
            }
        } else {
            echo "ไม่พบข้อมูลสำหรับอัปเดต";
        }
    } else {
        echo "ข้อมูลไม่ครบถ้วน";
    }
}


if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_house_line_user WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_house_line_user WHERE id = " . $id;
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
        $searchQuery = " AND (line_user_id LIKE :line_user_id 
        or line_user_name LIKE :line_user_name
        or f_name LIKE :f_name
        or l_name LIKE :l_name
        or house_number LIKE :house_number) ";
        $searchArray = array(
            'line_user_id' => "%$searchValue%",
            'line_user_name' => "%$searchValue%",
            'f_name' => "%$searchValue%",
            'l_name' => "%$searchValue%",
            'house_number' => "%$searchValue%",
        );
    }

    $where_line_user_id = "";

    if (($_SESSION['account_type']) === "house_user") {
        $where_line_user_id = " AND line_user_id = '" . $_SESSION['line_user_id'] . "' ";
    }

    /*
        $txt = $where_line_user_id;
        $my_file = fopen("device_a.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $txt);
        fclose($my_file);
    */

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_house_line_user WHERE 1=1 " . $where_line_user_id);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_house_line_user WHERE 1=1 " . $where_line_user_id . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];
## Fetch records

    $sql_get_date = "SELECT * FROM ims_house_line_user WHERE 1=1 " . $where_line_user_id . $searchQuery . " ORDER BY id DESC LIMIT :limit,:offset";

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

    $counter = $row + 1; // เริ่มลำดับตาม offset

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {

            $update_btn = "";
            $delete_btn = "";

            if ($_SESSION['account_type'] === 'admin') {
                $update_btn = "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>";
                $delete_btn = "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>";
            } else {
                $update_btn = "<button type='button' class='btn btn-info btn-xs' disabled data-toggle='tooltip' title='Update (เฉพาะแอดมิน)'>Update</button>";
                $delete_btn = "<button type='button' class='btn btn-danger btn-xs' disabled data-toggle='tooltip' title='Delete (เฉพาะแอดมิน)'>Delete</button>";
            }


            $data[] = array(
                "no" => $counter, // 🆕 ลำดับ
                "id" => $row['id'],
                "line_user_id" => $row['line_user_id'],
                "user_type" => $row['user_type'],
                "line_user_name" => $row['line_user_name'],
                "house_number" => $row['house_number'],
                "phone_number" => $row['phone_number'],
                "f_name" => $row['f_name'],
                "l_name" => $row['l_name'],
                "line_picture_profile" => $row['line_picture_profile'],
                "line_picture_profile_text" => $row['line_picture_profile'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "line_user_id" => $row['line_user_id'],
                "line_user_name" => $row['line_user_name'],
                "select" => "<button type='button' name='select' id='" . $row['line_user_id'] . "@" . $row['line_user_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
</button>",
            );
        }

        $counter++; // เพิ่มลำดับ

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
