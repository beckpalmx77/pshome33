<?php
session_start();
error_reporting(0);
include('../config/connect_db.php');

$save_success = "บันทึกข้อมูลสำเร็จ";
$del_success = "ลบข้อมูลสำเร็จ";
$upload_dir = "../line_oa/checkin/uploads/leaves/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Fetch leave data by ID
if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = [];
    $sql_get = "SELECT * FROM leave_requests WHERE id = :id";
    $stmt = $conn->prepare($sql_get);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $result['start_date'] = date('d-m-Y', strtotime($result['start_date']));
        $result['end_date'] = date('d-m-Y', strtotime($result['end_date']));
        if ($result['swap_date']) {
            $result['swap_date'] = date('d-m-Y', strtotime($result['swap_date']));
        }
        // No need to str_replace if path is stored correctly from root
        $return_arr[] = $result;
    }
    echo json_encode($return_arr);
    exit;
}

// Add or Update leave request
if ($_POST["action"] === 'ADD' || $_POST["action"] === 'UPDATE') {
    $user_id = $_POST['user_id'];
    $emp_id = $_POST['emp_id'];
    $emp_name = $_POST['emp_name'];
    $leave_type = $_POST['leave_type'];
    $start_date = date('Y-m-d', strtotime($_POST['start_date']));
    $end_date = date('Y-m-d', strtotime($_POST['end_date']));
    $swap_date = !empty($_POST['swap_date']) ? date('Y-m-d', strtotime($_POST['swap_date'])) : null;
    $remark = $_POST['remark'];
    $id = $_POST['id'];

    $photo_path = "";
    if (isset($_FILES['leave_photo']) && $_FILES['leave_photo']['error'] == 0) {
        $extension = pathinfo($_FILES['leave_photo']['name'], PATHINFO_EXTENSION);
        $new_filename = "leave_" . time() . "_" . rand(1000, 9999) . "." . $extension;
        $target_file = $upload_dir . $new_filename;
        if (move_uploaded_file($_FILES['leave_photo']['tmp_name'], $target_file)) {
            $photo_path = "line_oa/checkin/uploads/leaves/" . $new_filename;
        }
    }

    if ($_POST["action"] === 'ADD') {
        $sql = "INSERT INTO leave_requests (user_id, emp_id, emp_name, leave_type, start_date, end_date, swap_date, remark, photo_path) 
                VALUES (:user_id, :emp_id, :emp_name, :leave_type, :start_date, :end_date, :swap_date, :remark, :photo_path)";
        $query = $conn->prepare($sql);
    } else {
        $sql = "UPDATE leave_requests SET 
                emp_id = :emp_id, 
                emp_name = :emp_name, 
                leave_type = :leave_type, 
                start_date = :start_date, 
                end_date = :end_date, 
                swap_date = :swap_date,
                remark = :remark" . 
                ($photo_path ? ", photo_path = :photo_path" : "") . 
                " WHERE id = :id";
        $query = $conn->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
    }

    $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $query->bindParam(':emp_id', $emp_id, PDO::PARAM_STR);
    $query->bindParam(':emp_name', $emp_name, PDO::PARAM_STR);
    $query->bindParam(':leave_type', $leave_type, PDO::PARAM_STR);
    $query->bindParam(':start_date', $start_date, PDO::PARAM_STR);
    $query->bindParam(':end_date', $end_date, PDO::PARAM_STR);
    $query->bindValue(':swap_date', $swap_date, $swap_date ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $query->bindParam(':remark', $remark, PDO::PARAM_STR);
    if ($photo_path || $_POST["action"] === 'ADD') {
        $query->bindParam(':photo_path', $photo_path, PDO::PARAM_STR);
    }

    if ($query->execute()) {
        echo $save_success;
    } else {
        echo "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
    }
    exit;
}

// Delete leave request
if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    // Optional: Delete photo file
    $sql_get = "SELECT photo_path FROM leave_requests WHERE id = :id";
    $stmt = $conn->prepare($sql_get);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $photo = $stmt->fetchColumn();
    if ($photo && file_exists("../" . $photo)) {
        unlink("../" . $photo);
    }

    $sql = "DELETE FROM leave_requests WHERE id = :id";
    $query = $conn->prepare($sql);
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    if ($query->execute()) {
        echo $del_success;
    } else {
        echo "เกิดข้อผิดพลาดในการลบข้อมูล";
    }
    exit;
}

// Fetch data for DataTable
if ($_POST["action"] === 'GET_LEAVE_DATA') {
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length'];
    $searchValue = $_POST['search']['value'];

    $searchQuery = " ";
    $searchArray = array();
    if ($searchValue != '') {
        $searchQuery = " AND (emp_id LIKE :search1 OR emp_name LIKE :search2 OR leave_type LIKE :search3 OR remark LIKE :search4) ";
        $searchArray['search1'] = "%$searchValue%";
        $searchArray['search2'] = "%$searchValue%";
        $searchArray['search3'] = "%$searchValue%";
        $searchArray['search4'] = "%$searchValue%";
    }

    // Total number of records without filtering
    $sql_total = "SELECT COUNT(*) AS allcount FROM leave_requests";
    $stmt = $conn->prepare($sql_total);
    $stmt->execute();
    $totalRecords = $stmt->fetch()['allcount'];

    // Total number of records with filtering
    $sql_filter = "SELECT COUNT(*) AS allcount FROM leave_requests WHERE 1=1 " . $searchQuery;
    $stmt = $conn->prepare($sql_filter);
    $stmt->execute($searchArray);
    $totalRecordwithFilter = $stmt->fetch()['allcount'];

    // Fetch records
    $sql_data = "SELECT * FROM leave_requests WHERE 1=1 " . $searchQuery . " ORDER BY id DESC LIMIT :limit, :offset";
    $stmt = $conn->prepare($sql_data);
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll();

    $data = array();
    foreach ($records as $row) {
        $photo_html = $row['photo_path'] ? "<div class='zoom-container'><img src='{$row['photo_path']}' style='width: 50px; height: auto;'></div>" : "ไม่มีรูป";
        $data[] = array(
            "id" => $row['id'],
            "emp_id" => $row['emp_id'],
            "emp_name" => $row['emp_name'],
            "leave_type" => $row['leave_type'],
            "start_date" => date('d-m-Y', strtotime($row['start_date'])),
            "end_date" => date('d-m-Y', strtotime($row['end_date'])),
            "remark" => $row['remark'],
            "photo" => $photo_html,
            "created_at" => $row['created_at'],
            "action" => "<button type='button' id='{$row['id']}' class='btn btn-info btn-xs update'>Update</button> 
                         <button type='button' id='{$row['id']}' class='btn btn-danger btn-xs delete'>Delete</button>"
        );
    }

    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
    exit;
}

// Fetch employees for selection
if ($_POST["action"] === 'GET_EMPLOYEE') {
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length'];
    $searchValue = $_POST['search']['value'];

    $searchQuery = " AND status = 'Y' AND emp_id NOT LIKE 'PS33-2024%' ";
    $searchArray = array();
    if ($searchValue != '') {
        $searchQuery .= " AND (emp_id LIKE :search1 OR f_name LIKE :search2 OR l_name LIKE :search3) ";
        $searchArray['search1'] = "%$searchValue%";
        $searchArray['search2'] = "%$searchValue%";
        $searchArray['search3'] = "%$searchValue%";
    }

    $sql_total = "SELECT COUNT(*) AS allcount FROM memployee WHERE status = 'Y' AND emp_id NOT LIKE 'PS33-2024%'";
    $stmt = $conn->prepare($sql_total);
    $stmt->execute();
    $totalRecords = $stmt->fetch()['allcount'];

    $sql_filter = "SELECT COUNT(*) AS allcount FROM memployee WHERE 1=1 " . $searchQuery;
    $stmt = $conn->prepare($sql_filter);
    $stmt->execute($searchArray);
    $totalRecordwithFilter = $stmt->fetch()['allcount'];

    $sql_data = "SELECT emp_id, f_name, l_name FROM memployee WHERE 1=1 " . $searchQuery . " ORDER BY emp_id ASC LIMIT :limit, :offset";
    $stmt = $conn->prepare($sql_data);
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll();

    $data = array();
    foreach ($records as $row) {
        $full_name = $row['f_name'] . " " . $row['l_name'];
        $data[] = array(
            "emp_id" => $row['emp_id'],
            "emp_name" => $full_name,
            "action" => "<button type='button' id='{$row['emp_id']}|{$full_name}' class='btn btn-outline-success btn-xs select_emp'>Select <i class='fa fa-check'></i></button>"
        );
    }

    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
    exit;
}
