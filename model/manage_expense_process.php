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

    $sql_get = "SELECT * FROM v_ims_expenses WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "doc_ref" => $result['doc_ref'],
            "runno" => $result['runno'],
            "expense_date" => $result['expense_date'],
            "inv" => $result['inv'],
            "category_id" => $result['category_id'],
            "category_name" => $result['category_name'],
            "description" => $result['description'],
            "qty" => $result['qty'],
            "unit_id" => $result['unit_id'],
            "unit_name" => $result['unit_name'],
            "amount" => $result['amount'],
            "price_per_unit" => $result['price_per_unit'],
            "total_amount" => $result['total_amount'],
            "file_attach" => $result['file_attach'],
            "remark" => $result['remark'],
            "receipt_name" => $result['receipt_name'],
            "payment_method" => $result['payment_method'],
            "approve_status" => $result['approve_status']);
    }

    echo json_encode($return_arr);

}


if ($_POST["action"] === 'ADD') {

    if (!empty($_POST["expense_date"])) {

        $expense_date = $_POST["expense_date"];
        $exp_month = substr($_POST["expense_date"], 3, 2);
        $exp_year = substr($_POST["expense_date"], 6, 4);
        $category_id = $_POST["category_id"];
        $description = $_POST["description"];
        $approve_status = $_POST["approve_status"];
        $qty = $_POST["qty"];
        $inv = $_POST["inv"];
        $unit_id = $_POST["unit_id"];
        $amount = $_POST["amount"];
        $price_per_unit = $_POST["price_per_unit"];
        $total_amount = $_POST["total_amount"];
        $remark = $_POST["remark"];
        $receipt_name = $_POST["receipt_name"];
        $payment_method = $_POST["payment_method"];

        $field = "runno";
        $table = "ims_expenses";
        $cond = " WHERE exp_month = '" . $exp_month . "' AND exp_year = '" . $exp_year . "'";

        $runno = LAST_DOCUMENT_NUMBER($conn, $field, $table, $cond);
        $doc_id = "EXP-" . $exp_year . "-" . $exp_month . "-" . sprintf('%04s', $runno);

        $file_names = [];

        // ตรวจสอบและอัปโหลดไฟล์
        if (!empty($_FILES['file_attach']['name'][0])) {
            $uploadDir = '../uploads/files/';
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

        $sql = "INSERT INTO ims_expenses(runno, doc_id, expense_date, exp_month, exp_year, category_id, description, qty, unit_id, amount, remark
                , receipt_name, inv, file_attach, payment_method, price_per_unit, total_amount)
                VALUES (:runno, :doc_id, :expense_date, :exp_month, :exp_year, :category_id, :description, :qty, :unit_id, :amount, :remark
                , :receipt_name,  :inv, :file_attach, :payment_method, :price_per_unit, :total_amount)";
        $query = $conn->prepare($sql);
        $query->bindParam(':runno', $runno, PDO::PARAM_STR);
        $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
        $query->bindParam(':expense_date', $expense_date, PDO::PARAM_STR);
        $query->bindParam(':exp_month', $exp_month, PDO::PARAM_STR);
        $query->bindParam(':exp_year', $exp_year, PDO::PARAM_STR);
        $query->bindParam(':category_id', $category_id, PDO::PARAM_STR);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':qty', $qty, PDO::PARAM_STR);
        $query->bindParam(':unit_id', $unit_id, PDO::PARAM_STR);
        $query->bindParam(':amount', $amount, PDO::PARAM_STR);
        $query->bindParam(':remark', $remark, PDO::PARAM_STR);
        $query->bindParam(':inv', $inv, PDO::PARAM_STR);
        $query->bindParam(':receipt_name', $receipt_name, PDO::PARAM_STR);
        $query->bindParam(':file_attach', $file_attach, PDO::PARAM_STR);
        $query->bindParam(':payment_method', $payment_method);
        $query->bindParam(':price_per_unit', $price_per_unit);
        $query->bindParam(':total_amount', $total_amount);

        $query->execute();
        $lastInsertId = $conn->lastInsertId();

        echo $lastInsertId ? $save_success : $error;
    }
}

if ($_POST["action"] === 'UPDATE') {

    if (!empty($_POST["expense_date"])) {

        $id = $_POST["id"];
        $expense_date = $_POST["expense_date"];
        $exp_month = substr($expense_date, 3, 2);
        $exp_year = substr($expense_date, 6, 4);
        $category_id = $_POST["category_id"];
        $description = $_POST["description"];
        $approve_status = $_POST["approve_status"];
        $qty = $_POST["qty"];
        $unit_id = $_POST["unit_id"];
        $amount = $_POST["amount"];
        $price_per_unit = $_POST["price_per_unit"];
        $total_amount = $_POST["total_amount"];
        $remark = $_POST["remark"];
        $receipt_name = $_POST["receipt_name"];
        $payment_method = $_POST["payment_method"];
        $inv = $_POST["inv"];
        $uploadDir = '../uploads/files/';
        $file_names = [];

        // ดึงชื่อไฟล์เก่าจาก DB
        $stmt = $conn->prepare("SELECT file_attach FROM ims_expenses WHERE id = :id");
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
        $sql_update = "UPDATE ims_expenses 
            SET expense_date = :expense_date,
                exp_month = :exp_month,
                exp_year = :exp_year,
                category_id = :category_id,
                description = :description,
                qty = :qty,
                unit_id = :unit_id,
                amount = :amount,
                remark = :remark,
                approve_status = :approve_status,
                receipt_name = :receipt_name,
                inv = :inv,
                file_attach = :file_attach,
                payment_method = :payment_method,
                price_per_unit = :price_per_unit,
                total_amount = :total_amount
            WHERE id = :id";

        $query = $conn->prepare($sql_update);
        $query->bindParam(':expense_date', $expense_date);
        $query->bindParam(':exp_month', $exp_month);
        $query->bindParam(':exp_year', $exp_year);
        $query->bindParam(':category_id', $category_id);
        $query->bindParam(':description', $description);
        $query->bindParam(':qty', $qty);
        $query->bindParam(':unit_id', $unit_id);
        $query->bindParam(':amount', $amount);
        $query->bindParam(':remark', $remark);
        $query->bindParam(':approve_status', $approve_status);
        $query->bindParam(':receipt_name', $receipt_name);
        $query->bindParam(':inv', $inv);
        $query->bindParam(':file_attach', $finalFileAttach);
        $query->bindParam(':payment_method', $payment_method);
        $query->bindParam(':price_per_unit', $price_per_unit);
        $query->bindParam(':total_amount', $total_amount);
        $query->bindParam(':id', $id);
        $query->execute();

        echo $save_success;
    }
}



if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_expenses WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_expenses WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            Reorder_Record($conn, "ims_expenses");
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_EXPENSE') {

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
        $searchQuery = " AND (description LIKE :description) ";
        $searchArray = array(
            'description' => "%$searchValue%"
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_expenses ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_expenses WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM v_ims_expenses WHERE 1 " . $searchQuery
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

            $approve_n = "ยังไม่ยืนยัน (รอตรวจสอบ)";
            $approve_y = "ยืนยันรายการ (อนุมัติ)";

            $data[] = array(
                "doc_id" => $row['doc_id'],
                "doc_ref" => $row['doc_ref'],
                "runno" => $row['runno'],
                "expense_date" => $row['expense_date'],
                "exp_month" => $row['exp_month'],
                "month_name" => $row['month_name'],
                "exp_year" => $row['exp_year'],
                "category_id" => $row['category_id'],
                "category_name" => $row['category_name'],
                "description" => $row['description'],
                "qty" => $row['qty'],
                "unit_id" => $row['unit_id'],
                "unit_name" => $row['unit_name'],
                "file_attach" => $row['file_attach'],
                "inv" => $row['inv'],
                "amount" => $row['amount'],
                "remark" => $row['remark'],
                "receipt_name" => $row['receipt_name'],
                "payment_method" => $row['payment_method'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "print" => "<button type='button' name='print' id='" . $row['id'] . "' class='btn btn-outline-success btn-xs print' data-toggle='tooltip' title='Print'>Print</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "approve_status" => $row['approve_status'] === 'Y' ? "<div class='text-success'>" . $approve_y . "</div>" : "<div class='text-muted'> " . $approve_n . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "expense_date" => $row['expense_date'],
                "category_id" => $row['category_id'],
                "unit_id" => $row['unit_id'],
                "unit_name" => $row['unit_name'],
                "select" => "<button type='button' name='select' id='" . $row['expense_date'] . "@" . $row['category'] . "@" . $row['unit_id'] . "@" . $row['unit_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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
