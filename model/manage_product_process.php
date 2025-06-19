<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/reorder_record.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM ims_products WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "product_id" => $result['product_id'],
            "product_name" => $result['product_name'],
            "unit_id" => $result['unit_id'],
            "unit_name" => $result['unit_name'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["product_id"] !== '') {

        $product_id = $_POST["product_id"];
        $sql_find = "SELECT * FROM ims_products WHERE product_id = '" . $product_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {

    // ตรวจสอบว่ามีการส่งค่า product_name มาหรือไม่
    if (!empty($_POST["product_name"])) {
        $product_name = $_POST["product_name"];
        $unit_id = $_POST["unit_id"];
        $status = $_POST["status"];

        // 1. ตรวจสอบว่า product_name ซ้ำกันหรือไม่
        $sql_find_name = "SELECT product_id FROM ims_products WHERE product_name = :product_name";
        $query_find_name = $conn->prepare($sql_find_name);
        $query_find_name->bindParam(':product_name', $product_name, PDO::PARAM_STR);
        $query_find_name->execute();
        $existing_product = $query_find_name->fetch(PDO::FETCH_ASSOC);

        if ($existing_product) {
            // ถ้า product_name ซ้ำกัน ให้แจ้งว่ามีข้อมูลแล้ว
            echo $dup; // หรือข้อความอื่นๆ ที่คุณต้องการ เช่น "สินค้านี้มีอยู่แล้วในระบบ"
        } else {
            // ถ้า product_name ไม่ซ้ำกัน ให้สร้างรหัสสินค้าใหม่
            // 2. ค้นหารหัสสินค้าล่าสุดที่มีอยู่ในฐานข้อมูล
            $sql_last_id = "SELECT product_id FROM ims_products ORDER BY product_id DESC LIMIT 1";
            $stmt_last_id = $conn->query($sql_last_id);
            $last_product_id = $stmt_last_id->fetchColumn();

            $newProductNumber = 1;
            if ($last_product_id) {
                // ถ้ามีรหัสสินค้าเดิมอยู่ ให้ดึงตัวเลขจากรหัสล่าสุดมาเพิ่ม
                // เช่น P00005 จะได้ 5
                $last_number = (int) substr($last_product_id, 1);
                $newProductNumber = $last_number + 1;
            }

            // สร้างรหัสสินค้าใหม่ในรูปแบบ P00001
            $product_id = sprintf("P%05d", $newProductNumber);

            // 3. ทำการเพิ่มข้อมูลสินค้าใหม่ลงในฐานข้อมูล
            $sql = "INSERT INTO ims_products(product_id, product_name, unit_id, status)
                    VALUES (:product_id, :product_name, :unit_id, :status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $query->bindParam(':product_name', $product_name, PDO::PARAM_STR);
            $query->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->execute();

            $lastInsertId = $conn->lastInsertId();
            if ($lastInsertId) {
                echo $save_success;
            } else {
                echo $error;
            }
        }
    } else {
        // กรณีที่ไม่ได้ส่งค่า product_name มา
        echo $error; // หรือแจ้งเตือนให้กรอกข้อมูล product_name
    }
}

if ($_POST["action"] === 'UPDATE') {

    if ($_POST["product_id"] != '') {

        $id = $_POST["id"];
        $product_id = $_POST["product_id"];
        $product_name = $_POST["product_name"];
        $unit_id = $_POST["unit_id"];
        $status = $_POST["status"];

        $sql_find = "SELECT * FROM ims_products WHERE product_id = '" . $product_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_products SET product_name=:product_name,unit_id=:unit_id,status=:status
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':product_name', $product_name, PDO::PARAM_STR);
            $query->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_products WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_products WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            Reorder_Record($conn, "ims_products");
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_PRODUCT') {

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
        $searchQuery = " AND (product_id LIKE :product_id or 
        product_name LIKE :product_name OR
        status LIKE :status ) ";
        $searchArray = array(
            'product_id' => "%$searchValue%",
            'product_name' => "%$searchValue%",
            'status' => "%$searchValue%"
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_products ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_products WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_products WHERE 1 " . $searchQuery
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
        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "unit_id" => $row['unit_id'],
                "unit_name" => $row['unit_name'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                //"picture" => "<img src = '" . $row['picture'] . "'  width='32' height='32' title='" . $row['product_name'] . "'>",
                "status" => $row['status'] === 'Active' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "unit_id" => $row['unit_id'],
                "unit_name" => $row['unit_name'],
                "select" => "<button type='button' name='select' id='" . $row['product_id'] . "@" . $row['product_name'] . "@" . $row['unit_id'] . "@" . $row['unit_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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