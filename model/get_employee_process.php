<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM memployee "
        . " WHERE memployee.id = " . $id;

    //$myfile = fopen("myqeury_1.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_get);
    //fclose($myfile);

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "emp_id" => $result['emp_id'],
            "f_name" => $result['f_name'],
            "leave_before" => $result['leave_before'],
            "day_max" => $result['day_max'],
            "work_age_allow" => $result['work_age_allow'],
            "day_flag" => $result['day_flag'],
            "remark" => $result['remark'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH_DATA') {

    $emp_id = $_POST["emp_id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM memployee "
        . " WHERE memployee.emp_id = '" . $emp_id . "'";
    /*
        $myfile = fopen("myqeury_2.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $sql_get);
        fclose($myfile);
    */

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "emp_id" => $result['emp_id'],
            "f_name" => $result['f_name'],
            "leave_before" => $result['leave_before'],
            "day_max" => $result['day_max'],
            "work_age_allow" => $result['work_age_allow'],
            "day_flag" => $result['day_flag'],
            "remark" => $result['remark'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["emp_id"] !== '') {

        $emp_id = $_POST["emp_id"];
        $sql_find = "SELECT * FROM memployee WHERE emp_id = '" . $emp_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["f_name"] !== '') {
        //$emp_id = "D" . sprintf('%03s', LAST_ID($conn, "memployee", 'id'));
        $emp_id = $_POST["emp_id"];
        $f_name = $_POST["f_name"];
        $day_max = $_POST["day_max"];
        $leave_before = $_POST["leave_before"];
        $work_age_allow = $_POST["work_age_allow"];
        $remark = $_POST["remark"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM memployee WHERE emp_id = '" . $emp_id . "'";

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO memployee(emp_id,f_name,day_max,leave_before,work_age_allow,remark,status) 
                    VALUES (:emp_id,:f_name,:day_max,:leave_before,:work_age_allow,:remark,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':emp_id', $emp_id, PDO::PARAM_STR);
            $query->bindParam(':f_name', $f_name, PDO::PARAM_STR);
            $query->bindParam(':day_max', $day_max, PDO::PARAM_STR);
            $query->bindParam(':leave_before', $leave_before, PDO::PARAM_STR);
            $query->bindParam(':work_age_allow', $work_age_allow, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
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
    if ($_POST["f_name"] != '') {
        $id = $_POST["id"];
        $emp_id = $_POST["emp_id"];
        $f_name = $_POST["f_name"];
        $day_max = $_POST["day_max"];
        $leave_before = $_POST["leave_before"];
        $remark = $_POST["remark"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM memployee WHERE id = '" . $id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE memployee SET emp_id=:emp_id,f_name=:f_name
            ,day_max=:day_max,leave_before=:leave_before,work_age_allow=:work_age_allow,remark=:remark,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':emp_id', $emp_id, PDO::PARAM_STR);
            $query->bindParam(':f_name', $f_name, PDO::PARAM_STR);
            $query->bindParam(':day_max', $day_max, PDO::PARAM_STR);
            $query->bindParam(':leave_before', $leave_before, PDO::PARAM_STR);
            $query->bindParam(':work_age_allow', $work_age_allow, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }
    }
}

if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT * FROM memployee WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM memployee WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_EMPLOYEE') {
    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    $searchArray = array();

    $searchQuery = " ";

    /*
        if ($_SESSION['document_dept_cond']!=="A") {
            $searchQuery = " AND dept_id in (" . $_SESSION['document_dept_cond'] . ") ";
        }
    */

    /*
        if ($_SESSION['role'] === "SUPERVISOR") {
            $searchQuery = " AND dept_id_approve = '" . $_SESSION['dept_id_approve'] . "' ";
        } else if ($_SESSION['role'] === "EMPLOYEE") {
            $searchQuery = " AND emp_id = '" . $_SESSION['emp_id'] . "' ";
        } else if ($_SESSION['role'] === "ADMIN" || $_SESSION['role'] === "HR") {
            $searchQuery = " AND (branch <> 'XXX' AND branch NOT LIKE 'CP%') ";
        }
    */

    if ($_SESSION['role'] === "SUPERVISOR") {
        $searchQuery = " AND dept_id_approve = '" . $_SESSION['dept_id_approve'] . "' ";
    } else if ($_SESSION['role'] !== "EMPLOYEE") {
        $searchQuery = " AND emp_id = '" . $_SESSION['emp_id'] . "' ";
    }

    /*
        $txt = $searchQuery ;
        $my_file = fopen("leave_select_count.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $searchValue . " | " . $txt);
        fclose($my_file);
    */

    if ($searchValue != '') {
        $searchQuery = " AND (emp_id LIKE :emp_id or
        f_name LIKE :f_name ) ";
        $searchArray = array(
            'emp_id' => "%$searchValue%",
            'f_name' => "%$searchValue%",
        );
    }

## Total number of records without filtering

    $sql_cond = "SELECT COUNT(*) AS allcount FROM memployee WHERE status = 'Y' ";

    $stmt = $conn->prepare($sql_cond);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM memployee WHERE status = 'Y' " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $sql_get = "SELECT * FROM memployee WHERE status = 'Y' " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";

    $stmt = $conn->prepare($sql_get);
    /*
            $txt = $_POST["action"] . " | "  . $_POST["sub_action"] . " | " . $_POST["action_for"] . " | " . $columnName . " | " . $columnSortOrder . " | " . $sql_get;
            $my_file = fopen("emp_leave_a.txt", "w") or die("Unable to open file!");
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
                "emp_id" => $row['emp_id'],
                "f_name" => $row['f_name'],
                "day_max" => $row['day_max'],
                "leave_before" => $row['leave_before'],
                "work_age_allow" => $row['work_age_allow'],
                "remark" => $row['remark'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {

            $data[] = array(
                "id" => $row['id'],
                "emp_id" => $row['emp_id'],
                "full_name" => $row['f_name'] . " " . $row['l_name'],
                "nick_name" => $row['nick_name'],
                "department_id" => $row['department_id'],
                "select" => "<button type='button' name='select' id='" . $row['emp_id'] . "@" . $row['f_name'] . " " . $row['l_name'] . "@" . $row['nick_name'] . "@" . $row['department_id'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
</button>",
            );
            /*
                        $txt = $txt. ' ' . $row['emp_id'] . " | " .$row['f_name'] ;
                        $my_file = fopen("leave_select.txt", "w") or die("Unable to open file!");
                        fwrite($my_file, $txt);
                        fclose($my_file);
            */

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
