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

    $sql_get = "SELECT * FROM ims_document_contact WHERE id = " . $id;
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_no" => $result['doc_no'],
            "doc_date" => $result['doc_date'],
            "doc_year" => $result['doc_year'],
            "contact_name" => $result['contact_name'],
            "topic" => $result['topic'],
            "detail" => $result['detail'],
            "file_attach" => $result['file_attach'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["doc_year"] !== '') {

        $doc_year = $_POST["doc_year"];
        $sql_find = "SELECT * FROM ims_document_contact WHERE doc_year = '" . $doc_year . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {

    // Debug: แสดงค่าที่รับมาทั้งหมด (สำหรับ debug ชั่วคราว)
    error_log("POST Data: " . print_r($_POST, true));
    error_log("FILES Data: " . print_r($_FILES, true));

    if (!empty($_POST["doc_date"])) {

        $doc_date = $_POST["doc_date"];
        $doc_year = substr($doc_date,6,4);

        $contact_name = $_POST["contact_name"] ?? '-';   // ใช้ null coalescing กันกรณีไม่มี key
        $topic = $_POST["topic"] ?? '-';
        $detail = $_POST["detail"] ?? '-';
        $file_attach = $_POST["file_attach"] ?? '-';
        $status = $_POST["status"] ?? '';

        $table = "ims_document_contact";
        $field = "doc_no";
        $cond = " WHERE doc_year = '" . $doc_year . "' ";
        $doc_no = $doc_year . "-" . sprintf('%04s', LAST_DOCUMENT_NUMBER($conn, $field, $table, $cond));

        $file_names = [];

        if (!empty($_FILES['file_attach']['name'][0])) {
            $uploadDir = '../uploads/document/';
            $uploadedOriginals = [];

            foreach ($_FILES['file_attach']['tmp_name'] as $key => $tmp_name) {
                $originalName = basename($_FILES['file_attach']['name'][$key]);

                if (in_array($originalName, $uploadedOriginals)) {
                    continue;
                }
                $uploadedOriginals[] = $originalName;

                $fileName = time() . '_' . uniqid() . '_' . $originalName;
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($tmp_name, $targetPath)) {
                    $file_names[] = $fileName;
                    error_log("Uploaded file: $fileName");
                } else {
                    error_log("Failed to upload file: $originalName");
                }
            }
        } else {
            error_log("No files uploaded.");
        }

        $file_names = array_unique($file_names);
        $file_attach = implode(',', $file_names);

        // เตรียม sql insert
        $sql = "INSERT INTO ims_document_contact(doc_date, doc_no, doc_year, contact_name, topic, detail, file_attach, status)
                VALUES (:doc_date, :doc_no, :doc_year, :contact_name, :topic, :detail, :file_attach, :status)";
        $query = $conn->prepare($sql);

        // *** ปัญหา: ตัวแปร $doc_date ไม่ถูกกำหนด ***
        // ต้องกำหนด $doc_date ก่อน bindParam เช่น:
        $doc_date = $_POST["doc_date"] ?? '';  // เพิ่มเติม ถ้ามีค่า doc_date

        $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
        $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
        $query->bindParam(':doc_year', $doc_year, PDO::PARAM_STR);
        $query->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
        $query->bindParam(':topic', $topic, PDO::PARAM_STR);
        $query->bindParam(':detail', $detail, PDO::PARAM_STR);
        $query->bindParam(':file_attach', $file_attach, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);

        if ($query->execute()) {
            $lastInsertId = $conn->lastInsertId();
            echo $lastInsertId ? $save_success : $error;
        } else {
            // แสดง error จาก PDO
            $errorInfo = $query->errorInfo();
            error_log("PDO Error: " . print_r($errorInfo, true));
            echo $error;
        }

    } else {
        error_log("doc_date is empty");
        echo "doc_date is required.";
    }
}

if ($_POST["action"] === 'UPDATE') {

    if (!empty($_POST["doc_year"])) {

        $id = $_POST["id"];
        $contact_name = $_POST["contact_name"] ?? '-';   // ใช้ null coalescing กันกรณีไม่มี key
        $topic = $_POST["topic"] ?? '-';
        $detail = $_POST["detail"] ?? '-';
        $file_attach = $_POST["file_attach"] ?? '-';
        $status = $_POST["status"] ?? '';

        $uploadDir = '../uploads/document/';
        $file_names = [];

        // ดึงชื่อไฟล์เก่าจาก DB
        $stmt = $conn->prepare("SELECT file_attach FROM ims_document_contact WHERE id = :id");
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
                    $remainingOldFiles = array_filter($remainingOldFiles, function ($f) use ($safeOriginalName) {
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
        $sql_update = "UPDATE ims_document_contact 
            SET contact_name = :contact_name,
                topic = :topic,
                detail = :detail,                
                status = :status,                
                file_attach = :file_attach
            WHERE id = :id";

        $query = $conn->prepare($sql_update);
        $query->bindParam(':contact_name', $contact_name);
        $query->bindParam(':topic', $topic);
        $query->bindParam(':detail', $detail);
        $query->bindParam(':status', $status);
        $query->bindParam(':file_attach', $finalFileAttach);
        $query->bindParam(':id', $id);
        $query->execute();

        echo $save_success;
    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_document_contact WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_document_contact WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            Reorder_Record($conn, "ims_document_contact");
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_DOCUMENT') {

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
        $searchQuery = " AND (doc_date LIKE :doc_date) ";
        $searchArray = array(
            'doc_date' => "%$searchValue%"
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_document_contact ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_document_contact WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_document_contact WHERE 1 " . $searchQuery
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
                "doc_no" => $row['doc_no'],
                "doc_date" => $row['doc_date'],
                "doc_year" => $row['doc_year'],
                "doc_runno" => $row['doc_runno'],
                "contact_name" => $row['contact_name'],
                "topic" => $row['topic'],
                "detail" => $row['detail'],
                "file_attach" => $row['file_attach'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_no" => $row['doc_no'],
                "doc_date" => $row['doc_date'],
                "select" => "<button type='button' name='select' id='" . $row['doc_no'] . "@" . $row['doc_date'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

