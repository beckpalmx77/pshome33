<?php
session_start();
error_reporting(0);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM inventory_items "
        . " WHERE inventory_items.id = " . $id;

    //$myfile = fopen("myqeury_1.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_get);
    //fclose($myfile);

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "item_code" => $result['item_code'],
            "item_name" => $result['item_name'],
            "category" => $result['category'],
            "brand" => $result['brand'],
            "model" => $result['model'],
            "details" => $result['details'],
            "received_date" => $result['received_date'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["item_code"] !== '') {

        $item_code = $_POST["item_code"];
        $sql_find = "SELECT * FROM inventory_items WHERE item_code = '" . $item_code . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if (!empty($_POST["category"])) {

        $item_name = $_POST["item_name"];
        $category = $_POST["category"];
        $brand = $_POST["brand"];
        $model = $_POST["model"];
        $details = $_POST["details"];
        $received_date = $_POST["received_date"];
        $status = $_POST["status"];

        $table = "inventory_items";
        $field = "item_code";
        $cond = " where category = '" . $category . "' ";
        $item_code = $category . "-" . sprintf('%04s', LAST_DOCUMENT_NUMBER($conn, $field, $table, $cond));

        file_put_contents("debug_post.txt", print_r($_POST, true));
        file_put_contents("debug_files.txt", print_r($_FILES, true));


        $txt = "DDD " . $item_code;
        $my_file = fopen("device_a.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $txt);
        fclose($my_file);

        $uploadDir = '../uploads/equipment/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $imageNames = [];

        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $originalName = basename($_FILES['images']['name'][$key]);
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $newFileName = uniqid('img_', true) . '.' . $extension;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($tmp_name, $destination)) {
                    $imageNames[] = $newFileName;
                } else {
                    // ถ้าไฟล์ไม่ถูกย้าย ลองเก็บ log หรือแจ้ง error
                    error_log("Failed to move uploaded file: " . $originalName);
                }
            }
        }

        $imageList = implode(',', $imageNames);

        // ตรวจสอบซ้ำ
        $sql_find = "SELECT COUNT(*) FROM inventory_items WHERE item_code = :item_code";
        $stmt_find = $conn->prepare($sql_find);
        $stmt_find->bindParam(':item_code', $item_code, PDO::PARAM_STR);
        $stmt_find->execute();
        $nRows = $stmt_find->fetchColumn();

        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO inventory_items(item_code,item_name,category,brand,model,details,received_date,status,image_files) 
                    VALUES (:item_code,:item_name,:category,:brand,:model,:details,:received_date,:status,:image_files)";
            $query = $conn->prepare($sql);
            $query->bindParam(':item_code', $item_code, PDO::PARAM_STR);
            $query->bindParam(':item_name', $item_name, PDO::PARAM_STR);
            $query->bindParam(':category', $category, PDO::PARAM_STR);
            $query->bindParam(':brand', $brand, PDO::PARAM_STR);
            $query->bindParam(':model', $model, PDO::PARAM_STR);
            $query->bindParam(':details', $details, PDO::PARAM_STR);
            $query->bindParam(':received_date', $received_date, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':image_files', $imageList, PDO::PARAM_STR);
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

    if ($_POST["item_name"] != '') {

        $id = $_POST["id"];
        $item_code = $_POST["item_code"];
        $item_name = $_POST["item_name"];
        $category = $_POST["category"];
        $brand = $_POST["brand"];
        $model = $_POST["model"];
        $details = $_POST["details"];
        $received_date = $_POST["received_date"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM inventory_items WHERE id = '" . $id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE inventory_items SET item_code=:item_code,item_name=:item_name,category=:category,brand=:brand,model=:model,details=:details,received_date=:received_date,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':item_code', $item_code, PDO::PARAM_STR);
            $query->bindParam(':item_name', $item_name, PDO::PARAM_STR);
            $query->bindParam(':category', $category, PDO::PARAM_STR);
            $query->bindParam(':brand', $brand, PDO::PARAM_STR);
            $query->bindParam(':model', $model, PDO::PARAM_STR);
            $query->bindParam(':details', $details, PDO::PARAM_STR);
            $query->bindParam(':received_date', $received_date, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM inventory_items WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM inventory_items WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_INVENTORY') {

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
        $searchQuery = " AND (item_code LIKE :item_code or
        item_name LIKE :item_name ) ";
        $searchArray = array(
            'item_code' => "%$searchValue%",
            'item_name' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM inventory_items ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM inventory_items WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM inventory_items WHERE 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset");

/*
    $txt = $searchQuery . " | " . $columnName . " | " . $columnSortOrder;
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
                "item_code" => $row['item_code'],
                "item_name" => $row['item_name'],
                "category" => $row['category'],
                "brand" => $row['brand'],
                "model" => $row['model'],
                "details" => $row['details'],
                "received_date" => $row['received_date'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "item_code" => $row['item_code'],
                "item_name" => $row['item_name'],
                "select" => "<button type='button' name='select' id='" . $row['item_code'] . "@" . $row['item_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

