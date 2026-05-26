<?php
session_start();
error_reporting(0);
include('../config/connect_db.php');

if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $sql = "SELECT * FROM ims_chart_of_accounts WHERE acc_code = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}

if ($_POST["action"] === 'ADD') {
    $acc_code = $_POST["acc_code"];
    $acc_name = $_POST["acc_name"];
    $acc_group = $_POST["acc_group"];
    
    $sql = "INSERT INTO ims_chart_of_accounts (acc_code, acc_name, acc_group, status) VALUES (:acc_code, :acc_name, :acc_group, 'Active')";
    $query = $conn->prepare($sql);
    $query->bindParam(':acc_code', $acc_code);
    $query->bindParam(':acc_name', $acc_name);
    $query->bindParam(':acc_group', $acc_group);
    $query->execute();
    echo "บันทึกข้อมูลสำเร็จ";
    exit;
}

if ($_POST["action"] === 'UPDATE') {
    $acc_code = $_POST["acc_code"];
    $acc_name = $_POST["acc_name"];
    $acc_group = $_POST["acc_group"];
    
    $sql = "UPDATE ims_chart_of_accounts SET acc_name = :acc_name, acc_group = :acc_group WHERE acc_code = :acc_code";
    $query = $conn->prepare($sql);
    $query->bindParam(':acc_name', $acc_name);
    $query->bindParam(':acc_group', $acc_group);
    $query->bindParam(':acc_code', $acc_code);
    $query->execute();
    echo "แก้ไขข้อมูลสำเร็จ";
    exit;
}

if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql = "DELETE FROM ims_chart_of_accounts WHERE acc_code = :id";
    $query = $conn->prepare($sql);
    $query->bindParam(':id', $id);
    $query->execute();
    echo "ลบข้อมูลสำเร็จ";
    exit;
}

if ($_POST["action"] === 'GET_COA') {
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length'];
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];
    $searchValue = $_POST['search']['value'];

    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (acc_code LIKE :acc_code OR acc_name LIKE :acc_name OR acc_group LIKE :acc_group) ";
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_chart_of_accounts");
    $stmt->execute();
    $totalRecords = $stmt->fetch()['allcount'];

    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_chart_of_accounts WHERE 1 " . $searchQuery);
    if ($searchValue != '') {
        $stmt->bindValue(':acc_code', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':acc_name', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':acc_group', "%$searchValue%", PDO::PARAM_STR);
    }
    $stmt->execute();
    $totalRecordwithFilter = $stmt->fetch()['allcount'];

    $sql = "SELECT * FROM ims_chart_of_accounts WHERE 1 " . $searchQuery . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";
    $stmt = $conn->prepare($sql);
    if ($searchValue != '') {
        $stmt->bindValue(':acc_code', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':acc_name', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':acc_group', "%$searchValue%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll();

    $data = [];
    foreach ($records as $r) {
        $data[] = [
            "acc_code" => $r['acc_code'],
            "acc_name" => $r['acc_name'],
            "acc_group" => $r['acc_group'],
            "status" => $r['status'],
            "update" => "<button type='button' class='btn btn-info btn-xs update' id='".$r['acc_code']."'>Update</button>",
            "delete" => "<button type='button' class='btn btn-danger btn-xs delete' id='".$r['acc_code']."'>Delete</button>"
        ];
    }

    echo json_encode([
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    ]);
}
