<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT em.*,mp.position_desc,wt.work_time_id,wt.work_time_detail,wt.work_time_detail,work_time_start,work_time_stop
            FROM memployee em            
            left join mposition mp on mp.position_id = em.position_id
            left join mwork_time wt on wt.work_time_id = em.work_time_id   
            WHERE em.id = " . $id;

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "emp_id" => $result['emp_id'],
            "f_name" => $result['f_name'],
            "l_name" => $result['l_name'],
            "sex" => $result['sex'],
            "start_work_date" => $result['start_work_date'],
            "prefix" => $result['prefix'],
            "nick_name" => $result['nick_name'],
            "phone" => $result['phone'],
            "position_id" => $result['position_id'],
            "position_desc" => $result['position_desc'],
            "week_holiday" => $result['week_holiday'],
            "work_time_id" => $result['work_time_id'],
            "work_time_detail" => $result['work_time_detail'],
            "remark" => $result['remark'],
            "image" => $result['image'],
            "status" => $result['status']);
    }
    echo json_encode($return_arr);
}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["l_name"] !== '') {

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
    if (!empty($_POST["f_name"])) {
        try {

            $year = date("Y");
            $cond = " where year = '" . $year . "'" ;

            $emp_id = "PS33-" . $year ."-" . sprintf('%05s', LAST_DOCUMENT_NUMBER($conn, 'id',"memployee", $cond));

            $f_name = $_POST["f_name"];
            $l_name = $_POST["l_name"];
            $position_id = $_POST["position_id"];
            $week_holiday = $_POST["week_holiday"];
            $work_time_id = $_POST["work_time_id"];
            $remark = $_POST["remark"];
            $sex = $_POST["sex"];
            $prefix = $_POST["prefix"];
            $nick_name = $_POST["nick_name"];
            $start_work_date = $_POST["start_work_date"];
            $phone = $_POST["phone"];
            $status = $_POST["status"];

            $image_filename = $_POST['old_image'] ?? null;

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/employees/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                if ($image_filename && file_exists($upload_dir . $image_filename)) {
                    unlink($upload_dir . $image_filename);
                }

                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image_filename = uniqid('emp_', true) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename);
            }

            //file_put_contents("empte2.txt", "$emp_id | $f_name | $l_name | $position_id");

            $sql_find = "SELECT COUNT(*) FROM memployee WHERE emp_id = :emp_id";
            $stmt_check = $conn->prepare($sql_find);
            $stmt_check->bindParam(':emp_id', $emp_id);
            $stmt_check->execute();
            $nRows = $stmt_check->fetchColumn();

            if ($nRows > 0) {
                //$log_message = "[" . date("Y-m-d H:i:s") . "] Duplicate emp_id detected: $emp_id ($f_name $l_name)\n";
                //file_put_contents("emp_duplicate.log", $log_message, FILE_APPEND);
                echo $dup;
            } else {
                $sql = "INSERT INTO memployee (emp_id, f_name, l_name, week_holiday, work_time_id, position_id, remark, sex, prefix, nick_name, start_work_date, status, phone, year, image)
                VALUES (:emp_id, :f_name, :l_name, :week_holiday, :work_time_id, :position_id, :remark, :sex, :prefix, :nick_name, :start_work_date, :status, :phone, :year, :image)";
                $query = $conn->prepare($sql);
                $query->bindParam(':emp_id', $emp_id);
                $query->bindParam(':f_name', $f_name);
                $query->bindParam(':l_name', $l_name);
                $query->bindParam(':week_holiday', $week_holiday);
                $query->bindParam(':work_time_id', $work_time_id);
                $query->bindParam(':position_id', $position_id);
                $query->bindParam(':remark', $remark);
                $query->bindParam(':sex', $sex);
                $query->bindParam(':prefix', $prefix);
                $query->bindParam(':nick_name', $nick_name);
                $query->bindParam(':start_work_date', $start_work_date);
                $query->bindParam(':status', $status);
                $query->bindParam(':phone', $phone);
                $query->bindParam(':year', $year);
                $query->bindParam(':image', $image_filename);

                if (!$query->execute()) {
                    $errorInfo = $query->errorInfo();
                    file_put_contents("sql_error.txt", print_r($errorInfo, true));
                    echo "SQL Error: " . $errorInfo[2];
                } else {
                    echo $save_success;
                }
            }
        } catch (PDOException $e) {
            echo "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }

}


if ($_POST["action"] === 'UPDATE') {
    if ($_POST["f_name"] !== '' && $_POST["emp_id"] !== '') {
        $id = $_POST["id"];
        $emp_id = $_POST["emp_id"];
        $f_name = $_POST["f_name"];
        $l_name = $_POST["l_name"];
        $position_id = $_POST["position_id"];
        $week_holiday = $_POST["week_holiday"];
        $work_time_id = $_POST["work_time_id"];
        $remark = $_POST["remark"];
        $sex = $_POST["sex"];
        $prefix = $_POST["prefix"];
        $nick_name = $_POST["nick_name"];
        $start_work_date = $_POST["start_work_date"];
        $phone = $_POST["phone"];
        $status = $_POST["status"];


        $image_filename = $_POST['old_image'] ?? null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/employees/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            if ($image_filename && file_exists($upload_dir . $image_filename)) {
                unlink($upload_dir . $image_filename);
            }

            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_filename = uniqid('emp_', true) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename);
        }

        $success = move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename);
        if (!$success) {
            file_put_contents("upload_error.txt", "ไม่สามารถอัปโหลดรูปได้: " . print_r($_FILES['image'], true));
        }

        // ตรวจสอบว่ามี emp_id ซ้ำแต่ไม่ใช่ record ตัวเอง
        $sql_find = "SELECT COUNT(*) FROM memployee WHERE emp_id = :emp_id AND id != :id";
        $stmt_check = $conn->prepare($sql_find);
        $stmt_check->bindParam(':emp_id', $emp_id);
        $stmt_check->bindParam(':id', $id);
        $stmt_check->execute();
        $nRows = $stmt_check->fetchColumn();

        if ($nRows > 0) {
            echo $dup; // ตัวแปรนี้คุณต้องกำหนดค่าไว้ เช่น `$dup = "รหัสซ้ำ";`
        } else {
            $sql = "UPDATE memployee SET  
                    emp_id = :emp_id,
                    f_name = :f_name,
                    l_name = :l_name,
                    week_holiday = :week_holiday,
                    work_time_id = :work_time_id,
                    position_id = :position_id,
                    remark = :remark,
                    sex = :sex,
                    prefix = :prefix,
                    nick_name = :nick_name,
                    start_work_date = :start_work_date,
                    status = :status,
                    phone = :phone,
                    image = :image
                    WHERE id = :id";

            $query = $conn->prepare($sql);
            $query->bindParam(':emp_id', $emp_id);
            $query->bindParam(':f_name', $f_name);
            $query->bindParam(':l_name', $l_name);
            $query->bindParam(':week_holiday', $week_holiday);
            $query->bindParam(':work_time_id', $work_time_id);
            $query->bindParam(':position_id', $position_id);
            $query->bindParam(':remark', $remark);
            $query->bindParam(':sex', $sex);
            $query->bindParam(':prefix', $prefix);
            $query->bindParam(':nick_name', $nick_name);
            $query->bindParam(':start_work_date', $start_work_date);
            $query->bindParam(':status', $status);
            $query->bindParam(':phone', $phone);
            $query->bindParam(':image', $image_filename);
            $query->bindParam(':id', $id);

            $query->execute();

            if ($query->rowCount() > 0) {
                echo $save_success; // กำหนดข้อความ เช่น `$save_success = "บันทึกสำเร็จ";`
            } else {
                echo "ไม่มีข้อมูลถูกเปลี่ยนแปลง";
            }
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
    //$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $columnSortOrder = 'desc'; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    $searchArray = array();

## Search
    $searchQuery = " ";
    //if ($_POST["page_manage"]!=="ADMIN") {
    //$searchQuery = " AND emp_id = '" . $_SESSION['emp_id'] . "'";
    //}

    if ($searchValue != '') {
        $searchQuery = " AND (emp_id LIKE :emp_id or l_name LIKE :l_name or
        f_name LIKE :f_name or nick_name LIKE :nick_name) ";
        $searchArray = array(
            'emp_id' => "%$searchValue%",
            'l_name' => "%$searchValue%",
            'f_name' => "%$searchValue%",
            'nick_name' => "%$searchValue%"
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM memployee ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM memployee WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $sql_getdata = "SELECT em.*,mp.position_desc,wt.work_time_id,wt.work_time_detail,wt.work_time_detail,work_time_start,work_time_stop
            FROM memployee em            
            left join mposition mp on mp.position_id = em.position_id
            left join mwork_time wt on wt.work_time_id = em.work_time_id 	 	
            WHERE 1 " . $searchQuery
        . " ORDER BY status DESC, emp_id DESC , " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";

    $stmt = $conn->prepare($sql_getdata);

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
                "l_name" => $row['l_name'],
                "nick_name" => $row['nick_name'],
                "prefix" => $row['prefix'],
                "sex" => $row['sex'],
                "phone" => $row['phone'],
                "full_name" => $row['f_name'] . " " . $row['l_name'],
                "position_id" => $row['position_id'],
                "position_desc" => $row['position_desc'],
                "work_time_id" => $row['work_time_id'],
                "work_time_detail" => $row['work_time_detail'],
                "start_work_date" => $row['start_work_date'],
                "detail" => "<button type='button' name='detail' emp_id='" . $row['emp_id'] . "' class='btn btn-info btn-xs detail' data-toggle='tooltip' title='Detail'>Detail</button>",
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . "ทำงานปกติ" . "</div>" : "<div class='text-muted'> " . "ลาออก" . "</div>",
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "position_id" => $row['position_id'],
                "select" => "<button type='button' name='select' id='" . $row['position_id'] . "@" . $row['position_id'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

