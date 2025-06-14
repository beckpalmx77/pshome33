<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/reorder_record.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM vims_product WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "product_id" => $result['product_id'],
            "name_t" => $result['name_t'],
            "quantity" => $result['quantity'],
            "pgroup_id" => $result['pgroup_id'],
            "pgroup_name" => $result['pgroup_name'],
            "brand_id" => $result['brand_id'],
            "brand_name" => $result['brand_name'],
            "unit_id" => $result['unit_id'],
            "unit_name" => $result['unit_name'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["product_id"] !== '') {

        $product_id = $_POST["product_id"];
        $sql_find = "SELECT * FROM ims_product WHERE product_id = '" . $product_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {

    if ($_POST["product_id"] != '') {

        $product_id = $_POST["product_id"];
        $name_t = $_POST["name_t"];
        $quantity = $_POST["quantity"];
        $status = $_POST["status"];
        $pgroup_id = $_POST["pgroup_id"];
        $brand_id = $_POST["brand_id"];
        $unit_id = $_POST["unit_id"];
        $picture = "product-001.png";
        $sql_find = "SELECT * FROM ims_product WHERE product_id = '" . $product_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO ims_product(product_id,name_t,quantity,pgroup_id,brand_id,unit_id,picture,status)
            VALUES (:product_id,:name_t,:quantity,:pgroup_id,:brand_id,:unit_id,:picture,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
            $query->bindParam(':name_t', $name_t, PDO::PARAM_STR);
            $query->bindParam(':quantity', $quantity, PDO::PARAM_STR);
            $query->bindParam(':pgroup_id', $pgroup_id, PDO::PARAM_STR);
            $query->bindParam(':brand_id', $brand_id, PDO::PARAM_STR);
            $query->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);
            $query->bindParam(':picture', $picture, PDO::PARAM_STR);
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

    if ($_POST["product_id"] != '') {

        $id = $_POST["id"];
        $product_id = $_POST["product_id"];
        $name_t = $_POST["name_t"];
        $quantity = $_POST["quantity"];
        $status = $_POST["status"];
        $pgroup_id = $_POST["pgroup_id"];
        $brand_id = $_POST["brand_id"];
        $unit_id = $_POST["unit_id"];
        $picture = "product-001.png";
        $sql_find = "SELECT * FROM ims_product WHERE product_id = '" . $product_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_product SET name_t=:name_t,quantity=:quantity,status=:status
            ,pgroup_id=:pgroup_id,brand_id=:brand_id,unit_id=:unit_id,picture=:picture
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':name_t', $name_t, PDO::PARAM_STR);
            $query->bindParam(':quantity', $quantity, PDO::PARAM_STR);
            $query->bindParam(':pgroup_id', $pgroup_id, PDO::PARAM_STR);
            $query->bindParam(':brand_id', $brand_id, PDO::PARAM_STR);
            $query->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);
            $query->bindParam(':picture', $picture, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }

    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_product WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_product WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            Reorder_Record($conn, "ims_product");
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
        name_t LIKE :name_t OR
        name_e LIKE :name_e OR         
        status LIKE :status ) ";
        $searchArray = array(
            'product_id' => "%$searchValue%",
            'name_t' => "%$searchValue%",
            'name_e' => "%$searchValue%",
            'status' => "%$searchValue%"
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_product ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_product WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM vims_product WHERE 1 " . $searchQuery
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
                "name_t" => $row['name_t'],
                "name_e" => $row['name_e'],
                "quantity" => $row['quantity'],
                "unit_id" => $row['unit_id'],
                "unit_name" => $row['unit_name'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "picture" => "<img src = '" . $row['picture'] . "'  width='32' height='32' title='" . $row['name_t'] . "'>",
                "status" => $row['status'] === 'Active' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "product_id" => $row['product_id'],
                "name_t" => $row['name_t'],
                "unit_id" => $row['unit_id'],
                "unit_name" => $row['unit_name'],
                "select" => "<button type='button' name='select' id='" . $row['product_id'] . "@" . $row['name_t'] . "@" . $row['unit_id'] . "@" . $row['unit_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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