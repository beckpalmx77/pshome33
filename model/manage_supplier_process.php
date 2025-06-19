<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();
    $sql_get = "SELECT * FROM ims_supplier WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "supplier_id" => $result['supplier_id'],
            "supplier_name" => $result['supplier_name'],
            "address" => $result['address'],
            "phone" => $result['phone'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {
    if ($_POST["supplier_name"] !== '') {
        $supplier_name = $_POST["supplier_name"];
        $sql_find = "SELECT * FROM ims_supplier WHERE supplier_name = '" . $supplier_name . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    // 1. ตรวจสอบว่ามีการส่งค่าที่จำเป็นสำหรับผู้จัดจำหน่ายมาหรือไม่
    // ใช้ isset() สำหรับสถานะที่อาจจะเป็น 0
    if (!empty($_POST["supplier_name"]) && !empty($_POST["address"]) && !empty($_POST["phone"]) && isset($_POST["status"])) {

        $supplier_name = trim($_POST["supplier_name"]); // ใช้ trim เพื่อลบช่องว่างหัวท้าย
        $address = $_POST["address"];
        $phone = $_POST["phone"];
        $status = $_POST["status"];

        try {
            // 2. ตรวจสอบว่า supplier_name ซ้ำกันหรือไม่
            $stmtCheckSupplier = $conn->prepare("SELECT supplier_id FROM ims_supplier WHERE supplier_name = :supplier_name");
            $stmtCheckSupplier->bindParam(':supplier_name', $supplier_name, PDO::PARAM_STR);
            $stmtCheckSupplier->execute();
            $existing_supplier_id = $stmtCheckSupplier->fetchColumn();

            if ($existing_supplier_id) {
                // ถ้า supplier_name ซ้ำกัน ให้แจ้งว่ามีข้อมูลแล้ว
                echo $dup;
            } else {
                // 3. ถ้า supplier_name ไม่ซ้ำกัน ให้สร้าง supplier_id ใหม่
                $stmtMaxID = $conn->prepare("SELECT supplier_id FROM ims_supplier WHERE supplier_id LIKE 'S%' ORDER BY supplier_id DESC LIMIT 1");
                $stmtMaxID->execute();
                $lastSupplierID = $stmtMaxID->fetchColumn();

                // แปลงตัวเลขจาก 'S0000X' และเพิ่มค่า
                $newNumber = $lastSupplierID ? ((int)substr($lastSupplierID, 1)) + 1 : 1;
                $new_supplier_id = sprintf("S%05d", $newNumber);

                // 4. ทำการ INSERT ข้อมูลผู้จัดจำหน่ายใหม่ทั้งหมด
                $sql = "INSERT INTO ims_supplier (supplier_id, supplier_name, address, phone, status)
                        VALUES (:supplier_id, :supplier_name, :address, :phone, :status)";
                $query = $conn->prepare($sql);
                $query->bindParam(':supplier_id', $new_supplier_id, PDO::PARAM_STR);
                $query->bindParam(':supplier_name', $supplier_name, PDO::PARAM_STR);
                $query->bindParam(':address', $address, PDO::PARAM_STR);
                $query->bindParam(':phone', $phone, PDO::PARAM_STR);
                $query->bindParam(':status', $status, PDO::PARAM_STR);
                $query->execute();

                // ตรวจสอบว่าการแทรกข้อมูลสำเร็จหรือไม่
                if ($query->rowCount()) { // ใช้ rowCount() แทน lastInsertId() สำหรับ INSERT ปกติ
                    echo $save_success;
                } else {
                    echo $error; // อาจจะเกิดข้อผิดพลาดในการ execute แต่ไม่มี Exception
                }
            }

        } catch (PDOException $e) {
            // ดักจับข้อผิดพลาดที่เกิดจากการดำเนินการฐานข้อมูล
            error_log("Error adding supplier: " . $e->getMessage()); // บันทึกใน log
            echo $error; // แจ้งผู้ใช้ด้วยข้อความทั่วไป
        } catch (Exception $e) {
            // ดักจับข้อผิดพลาดทั่วไปอื่นๆ
            error_log("General error adding supplier: " . $e->getMessage()); // บันทึกใน log
            echo $error;
        }

    } else {
        // กรณีที่ข้อมูลที่จำเป็นไม่ครบถ้วน
        echo $error; // หรือข้อความว่า "กรุณากรอกข้อมูลผู้จัดจำหน่ายให้ครบถ้วน"
    }
}

if ($_POST["action"] === 'UPDATE') {
    if ($_POST["supplier_id"] != '') {
        $id = $_POST["id"];
        $supplier_id = $_POST["supplier_id"];
        $supplier_name = $_POST["supplier_name"];
        $address = $_POST["address"];
        $phone = $_POST["phone"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM ims_supplier WHERE supplier_id = '" . $supplier_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE ims_supplier SET supplier_name=:supplier_name
            ,address=:address,phone=:phone,status=:status
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':supplier_name', $supplier_name, PDO::PARAM_STR);
            $query->bindParam(':address', $address, PDO::PARAM_STR);
            $query->bindParam(':phone', $phone, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }
    }
}


if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT * FROM ims_supplier WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_supplier WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_SUPPLIER') {
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
        $searchQuery = " AND (supplier_id LIKE :supplier_id or
        supplier_name LIKE :supplier_name or address LIKE :address) ";
        $searchArray = array(
            'supplier_id' => "%$searchValue%",
            'supplier_name' => "%$searchValue%",
            'address' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_supplier ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_supplier WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_supplier WHERE 1 " . $searchQuery
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
                "id" => $row['id'],
                "supplier_id" => $row['supplier_id'],
                "supplier_name" => $row['supplier_name'],
                "address" => $row['address'],
                "phone" => $row['phone'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Active' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "supplier_id" => $row['supplier_id'],
                "supplier_name" => $row['supplier_name'],
                "select" => "<button type='button' name='select' id='" . $row['supplier_id'] . "@" . $row['supplier_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

