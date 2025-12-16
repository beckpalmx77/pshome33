<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/reorder_record.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_ims_user WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {

        $return_arr[] = array("id" => $result['id'],
            "email" => $result['email'],
            "first_name" => $result['first_name'],
            "last_name" => $result['last_name'],
            "account_type" => $result['account_type'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'ADD') {

    if ($_POST["email"] !== '') {

        $email = $_POST["email"];
        $user_id = $_POST["email"];
        //$password = password_hash($password, PASSWORD_DEFAULT);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $first_name = $_POST["first_name"];
        $last_name = $_POST["last_name"];
        $account_type = $_POST["account_type"];

        $picture = $account_type == 'admin' ? "img/icon/admin-001.png" : "img/icon/user-001.png";

        $status = "Active";

        $sql_find = "SELECT * FROM ims_user WHERE email = '" . $email . "'";

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            $sql = "INSERT INTO ims_user(user_id,email,password,first_name,last_name,account_type,picture,status)
            VALUES (:user_id,:email,:password,:first_name,:last_name,:account_type,:picture,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':password', $password, PDO::PARAM_STR);
            $query->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $query->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $query->bindParam(':account_type', $account_type, PDO::PARAM_STR);
            $query->bindParam(':picture', $picture, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->execute();

            $lastInsertId = $conn->lastInsertId();
            if ($lastInsertId) {
                Reorder_Record($conn, "ims_user");
                echo 1;
            } else {
                echo 3;
            }
        }
    }
}


if ($_POST["action"] === 'UPDATE' && $_SESSION['account_type'] === 'admin') {

    if ($_POST["email"] != '') {

        $id = $_POST["id"];
        $email = $_POST["email"];
        $first_name = $_POST["first_name"];
        $last_name = $_POST["last_name"];
        $status = $_POST["status"];
        $account_type = $_POST["account_type"];
        $picture = $account_type === 'admin' ? "img/icon/admin-001.png" : "img/icon/user-001.png";
        $sql_find = "SELECT * FROM ims_user WHERE email = '" . $email . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_user SET first_name=:first_name,last_name=:last_name,status=:status,account_type=:account_type
            ,picture=:picture
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $query->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $query->bindParam(':account_type', $account_type, PDO::PARAM_STR);
            $query->bindParam(':picture', $picture, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }
    } else {
        echo $error;
    }

}

if ($_POST["action"] === 'DELETE' && $_SESSION['account_type'] === 'admin') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_user WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_user WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            Reorder_Record($conn, "ims_user");
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}


if ($_POST["action"] === 'CHG') {
    // 1. ประกาศตัวแปรเริ่มต้น (เพื่อป้องกัน Undefined Variable)
    $result = 0;
    $nRows = 0;
    $username = $_POST["username"] ?? ''; // ป้องกันกรณีไม่ได้ส่งค่ามา
    $new_password = $_POST['new_password'] ?? '';

    // ตรวจสอบว่าข้อมูลไม่ว่างเปล่า
    if (!empty($username) && !empty($new_password)) {
        try {
            $password = password_hash($new_password, PASSWORD_DEFAULT);

            // 2. ตรวจสอบว่ามี username อยู่หรือไม่
            $sql_find = "SELECT COUNT(id) FROM ims_user WHERE user_id = :username";
            $query = $conn->prepare($sql_find);
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->execute();
            $nRows = $query->fetchColumn();

            if ($nRows > 0) {
                try {
                    // Update password
                    $sql_update = "UPDATE ims_user SET password = :password WHERE user_id = :username";
                    $update_query = $conn->prepare($sql_update);
                    $update_query->bindParam(':password', $password, PDO::PARAM_STR);
                    $update_query->bindParam(':username', $username, PDO::PARAM_STR);
                    $update_query->execute();

                    $result = 1;  // Success
                } catch (Exception $e) {
                    $result = 3;  // Error while updating
                    // บันทึก Error จริงลง Log เพื่อให้รู้สาเหตุ
                    //file_put_contents("error_log.txt", $e->getMessage() . "\n", FILE_APPEND);
                }
            } else {
                $result = 2;  // User not found
            }
        } catch (Exception $e) {
            $result = 3;  // General error (เช่น Database connect ไม่ได้)
            //file_put_contents("error_log.txt", $e->getMessage() . "\n", FILE_APPEND);
        }
    } else {
        $result = 0; // ข้อมูลไม่ครบ
    }

    // Log ผลลัพธ์
    $logData = date('Y-m-d H:i:s') . " | Result: $result | Rows Found: $nRows | username: $username\n";
    file_put_contents("chg-param.txt", $logData, FILE_APPEND);

    echo $result;
}

if ($_POST["action"] === 'CHL') {
    try {
        $lang = $_POST['lang'];
        $id = $_POST["login_id"];
        $sql_update = "UPDATE ims_user SET lang=:lang WHERE id = :id";
        $query = $conn->prepare($sql_update);
        $query->bindParam(':lang', $lang, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        echo 1;
    } catch (Exception $e) {
        echo 3;
    }
}

if ($_POST["action"] === 'GET_ACCOUNT') {
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
        $searchQuery = " AND (email LIKE :email or 
        first_name LIKE :first_name OR
        last_name LIKE :last_name OR         
        status LIKE :status ) ";
        $searchArray = array(
            'email' => "%$searchValue%",
            'first_name' => "%$searchValue%",
            'last_name' => "%$searchValue%",
            'status' => "%$searchValue%"
        );
    }


## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_user ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_user WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records


    if ($columnName==="line_no") {
        $columnName = " line_no " ;
        $columnSortOrder = " desc " ;
    }

    $stmt = $conn->prepare("SELECT * FROM v_ims_user WHERE 1 " . $searchQuery
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

        $data[] = array(
            "line_no" => $row['line_no'],
            "user_id" => $row['user_id'],
            "role" => $row['role'],
            "email" => $row['email'],
            "first_name" => $row['first_name'],
            "last_name" => $row['last_name'],
            "line_user_name" => $row['line_user_name'],
            "line_picture_profile" => $row['line_picture_profile'],
            "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
            "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
            "picture" => "<img src = '" . $row['picture'] . "'  width='32' height='32' title='" . $row['account_type'] . "'>",
            "status" => $row['status'] === 'Active' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
        );

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




