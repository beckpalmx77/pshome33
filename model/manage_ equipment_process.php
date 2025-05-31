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

    $sql_get = "SELECT * FROM inventory_items WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "item_code" => $result['item_code'],
            "item_name" => $result['item_name'],
            "category_id" => $result['category_id'],
            "inv" => $result['inv'],
            "received_date" => $result['received_date'],
            "category_id_name" => $result['category_id_name'],
            "description" => $result['description'],
            "qty" => $result['qty'],
            "unit_id" => $result['unit_id'],
            "unit_name" => $result['unit_name'],
            "amount" => $result['amount'],
            "file_attach" => $result['file_attach'],
            "remark" => $result['remark'],
            "approve_status" => $result['approve_status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["category_id"] !== '') {

        $category_id = $_POST["category_id"];
        $sql_find = "SELECT * FROM inventory_items WHERE category_id = '" . $category_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {

    if (!empty($_POST["category_id"])) {

        $category_id = $_POST["category_id"];
        $brand_id = $_POST["brand_id"];
        $details = $_POST["details"];
        $received_date = $_POST["received_date"];
        $status = $_POST["status"];

        $table = "inventory_items";
        $field = "item_code";
        $cond = " where category_id = '" . $category_id . "' ";
        $item_code = $category_id . "-" . sprintf('%04s', LAST_DOCUMENT_NUMBER($conn, $field, $table, $cond));

        $file_names = [];

        // ตรวจสอบและอัปโหลดไฟล์
        if (!empty($_FILES['file_attach']['name'][0])) {
            $uploadDir = '../uploads/equipment/';
            $uploadedOriginals = []; // เก็บชื่อไฟล์ต้นฉบับ เพื่อป้องกันแนบซ้ำ

            foreach ($_FILES['file_attach']['tmp_name'] as $key => $tmp_name) {
                $originalName = basename($_FILES['file_attach']['name'][$key]);

                // ป้องกันแนบไฟล์ต้นฉบับชื่อเดียวกันหลายครั้ง
                if (in_array($originalName, $uploadedOriginals)) {
                    continue;
                }
                $uploadedOriginals[] = $originalName;

                // ตั้งชื่อไฟล์ใหม่ให้ไม่ซ้ำ
                $fileName = time() . '_' . uniqid() . '_' . $originalName;
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($tmp_name, $targetPath)) {
                    $file_names[] = $fileName;
                }
            }
        }

        // ลบชื่อไฟล์ซ้ำอีกชั้น (เพื่อความชัวร์)
        $file_names = array_unique($file_names);

        $file_attach = implode(',', $file_names);

        $sql = "INSERT INTO inventory_items(item_name, item_code, category_id, brand_id, details, received_date, image_files,status)
                VALUES (:item_name, :item_code, :category_id, :brand_id, :details, :received_date,:image_files,:status)";
        $query = $conn->prepare($sql);
        $query->bindParam(':item_name', $item_name, PDO::PARAM_STR);
        $query->bindParam(':item_code', $item_code, PDO::PARAM_STR);
        $query->bindParam(':category_id', $category_id, PDO::PARAM_STR);
        $query->bindParam(':brand_id', $brand_id, PDO::PARAM_STR);
        $query->bindParam(':details', $details, PDO::PARAM_STR);
        $query->bindParam(':received_date', $received_date, PDO::PARAM_STR);
        $query->bindParam(':image_files', $file_attach, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $conn->lastInsertId();

        echo $lastInsertId ? $save_success : $error;
    }
}

if ($_POST["action"] === 'UPDATE') {

    if (!empty($_POST["category_id"])) {

        $id = $_POST["id"];
        $category_id = $_POST["category_id"];
        $brand_id = substr($category_id, 3, 2);
        $details = substr($category_id, 6, 4);
        $received_date = $_POST["received_date"];
        $description = $_POST["description"];
        $approve_status = $_POST["approve_status"];
        $qty = $_POST["qty"];
        $unit_id = $_POST["unit_id"];
        $amount = $_POST["amount"];
        $remark = $_POST["remark"];
        $inv = $_POST["inv"];
        $uploadDir = '../uploads/files/';
        $file_names = [];

        // ดึงชื่อไฟล์เก่าจาก DB
        $stmt = $conn->prepare("SELECT file_attach FROM inventory_items WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $oldFiles = $stmt->fetchColumn(); // เช่น "file1.jpg,file2.png"
        $oldFileArray = !empty($oldFiles) ? explode(',', $oldFiles) : [];

        // ไฟล์เดิมที่ยังคงอยู่จากฟอร์ม (ส่งมาเป็น string comma-separated)
        $existingFilesStr = isset($_POST['existing_files']) ? $_POST['existing_files'] : '';
        $existingFiles = $existingFilesStr !== '' ? explode(',', $existingFilesStr) : [];

        // ลบไฟล์เก่าที่ถูกลบออก (ที่ไม่อยู่ใน existingFiles)
        foreach ($oldFileArray as $oldFile) {
            if (!in_array($oldFile, $existingFiles)) {
                $oldFilePath = $uploadDir . $oldFile;
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }
        }

        // ชื่อไฟล์เก่าที่ยังเหลือ (ในฟอร์ม)
        $remainingOldFiles = array_intersect($oldFileArray, $existingFiles);

        // อัปโหลดไฟล์ใหม่ (ถ้ามี)
        if (!empty($_FILES['file_attach']['name'][0])) {
            foreach ($_FILES['file_attach']['tmp_name'] as $key => $tmp_name) {
                $originalName = basename($_FILES['file_attach']['name'][$key]);
                // sanitize ชื่อไฟล์ (ไม่บังคับแต่แนะนำ)
                $safeOriginalName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $targetPath = $uploadDir . $safeOriginalName;

                // ถ้าไฟล์ชื่อเดียวกับไฟล์เก่าอยู่แล้ว ให้ลบไฟล์เก่าออกก่อน (แทนที่ด้วยไฟล์ใหม่)
                if (in_array($safeOriginalName, $remainingOldFiles) && file_exists($targetPath)) {
                    unlink($targetPath);
                    // เอาออกจากรายชื่อไฟล์เก่าที่เหลือ เพื่อเพิ่มไฟล์ใหม่แทน
                    $remainingOldFiles = array_filter($remainingOldFiles, function($f) use ($safeOriginalName) {
                        return $f !== $safeOriginalName;
                    });
                }

                // ถ้าไฟล์นี้ยังไม่ถูกเพิ่ม ให้เพิ่มและอัปโหลด
                if (!in_array($safeOriginalName, $file_names)) {
                    if (move_uploaded_file($tmp_name, $targetPath)) {
                        $file_names[] = $safeOriginalName;
                    }
                }
            }
        }

        // รวมไฟล์เก่าที่เหลือกับไฟล์ใหม่
        $combinedFiles = array_merge($remainingOldFiles, $file_names);

        // กรองชื่อซ้ำ
        $combinedFiles = array_unique($combinedFiles);

        $finalFileAttach = implode(',', $combinedFiles);

        // อัพเดตข้อมูลใน DB
        $sql_update = "UPDATE inventory_items 
            SET category_id = :category_id,
                brand_id = :brand_id,
                details = :details,
                received_date = :received_date,
                description = :description,
                qty = :qty,
                unit_id = :unit_id,
                amount = :amount,
                remark = :remark,
                approve_status = :approve_status,
                inv = :inv,
                file_attach = :file_attach
            WHERE id = :id";

        $query = $conn->prepare($sql_update);
        $query->bindParam(':category_id', $category_id);
        $query->bindParam(':brand_id', $brand_id);
        $query->bindParam(':details', $details);
        $query->bindParam(':received_date', $received_date);
        $query->bindParam(':description', $description);
        $query->bindParam(':qty', $qty);
        $query->bindParam(':unit_id', $unit_id);
        $query->bindParam(':amount', $amount);
        $query->bindParam(':remark', $remark);
        $query->bindParam(':approve_status', $approve_status);
        $query->bindParam(':inv', $inv);
        $query->bindParam(':file_attach', $finalFileAttach);
        $query->bindParam(':id', $id);
        $query->execute();

        echo $save_success;
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
            Reorder_Record($conn, "inventory_items");
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

    $searchArray = array();

## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (item_name LIKE :item_name) ";
        $searchArray = array(
            'item_name' => "%$searchValue%"
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
    $stmt = $conn->prepare("SELECT * FROM v_inventory_items WHERE 1 " . $searchQuery
        . " ORDER BY id DESC LIMIT :limit,:offset");

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
                "item_code" => $row['item_code'],
                "item_name" => $row['item_name'],
                "category_id" => $row['category_id'],
                "brand_id" => $row['brand_id'],
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
