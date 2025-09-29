<?php
session_start();
error_reporting(0); // ควรเปลี่ยนเป็น E_ALL สำหรับการพัฒนา

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

// ==================================================================================
// == ฟังก์ชันสำหรับจัดการการอัปโหลดรูปภาพสัตว์เลี้ยง (เพิ่มใหม่) ==
// ==================================================================================
function handlePetPictureUpload($fileInputName, $uploadDir, $existingFileName = '')
{
    // ตรวจสอบว่ามี folder uploads/pet หรือไม่ ถ้าไม่มีให้สร้างขึ้นมา
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES[$fileInputName]['tmp_name'];
        $fileName = $_FILES[$fileInputName]['name'];
        $fileSize = $_FILES[$fileInputName]['size'];
        $fileType = $_FILES[$fileInputName]['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // สร้างชื่อไฟล์ใหม่ที่ไม่ซ้ำกันเพื่อป้องกันการเขียนทับ
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $dest_path = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            return $newFileName; // คืนค่าชื่อไฟล์ใหม่ถ้าอัปโหลดสำเร็จ
        }
    }
    return $existingFileName; // คืนค่าชื่อไฟล์เดิมถ้าไม่มีการอัปโหลดไฟล์ใหม่
}

// ==================================================================================


if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();

    // ปรับปรุง: ใช้ Prepared Statement เพื่อความปลอดภัย และดึงข้อมูลสัตว์เลี้ยงทั้งหมด
    $sql_get = "SELECT * FROM ims_house_pet WHERE id = :id";
    $stmt = $conn->prepare($sql_get);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ปรับปรุง: เพิ่มข้อมูลสัตว์เลี้ยงทั้งหมดลงใน array ที่จะส่งกลับไป
    foreach ($results as $result) {
        $return_arr[] = array(
            "id" => $result['id'],
            "house_number" => $result['house_number'],
            "contact_name" => $result['contact_name'],
            "phone_number" => $result['phone_number'],
            "alley" => $result['alley'],
            "pet_quantity" => $result['pet_quantity'],
            // ข้อมูลสัตว์เลี้ยง 1-6
            "type_1" => $result['type_1'], "pet_1" => $result['pet_1'], "picture_pet_1" => $result['picture_pet_1'],
            "type_2" => $result['type_2'], "pet_2" => $result['pet_2'], "picture_pet_2" => $result['picture_pet_2'],
            "type_3" => $result['type_3'], "pet_3" => $result['pet_3'], "picture_pet_3" => $result['picture_pet_3'],
            "type_4" => $result['type_4'], "pet_4" => $result['pet_4'], "picture_pet_4" => $result['picture_pet_4'],
            "type_5" => $result['type_5'], "pet_5" => $result['pet_5'], "picture_pet_5" => $result['picture_pet_5'],
            "type_6" => $result['type_6'], "pet_6" => $result['pet_6'], "picture_pet_6" => $result['picture_pet_6']
        );
    }
    echo json_encode($return_arr);
}

if ($_POST["action"] === 'GET_DATA_BY_HOUSE_NUMBER') {
    $house_number = $_POST["house_number"];
    $return_arr = array();
    $sql_get = "SELECT * FROM ims_house_pet WHERE house_number = :house_number";
    $stmt = $conn->prepare($sql_get);
    $stmt->bindParam(':house_number', $house_number, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $result) {
        $return_arr[] = array(
            "id" => $result['id'], "house_number" => $result['house_number'], "contact_name" => $result['contact_name'],
            "phone_number" => $result['phone_number'], "alley" => $result['alley'], "pet_quantity" => $result['pet_quantity'],
            "type_1" => $result['type_1'], "pet_1" => $result['pet_1'], "picture_pet_1" => $result['picture_pet_1'],
            "type_2" => $result['type_2'], "pet_2" => $result['pet_2'], "picture_pet_2" => $result['picture_pet_2'],
            "type_3" => $result['type_3'], "pet_3" => $result['pet_3'], "picture_pet_3" => $result['picture_pet_3'],
            "type_4" => $result['type_4'], "pet_4" => $result['pet_4'], "picture_pet_4" => $result['picture_pet_4'],
            "type_5" => $result['type_5'], "pet_5" => $result['pet_5'], "picture_pet_5" => $result['picture_pet_5'],
            "type_6" => $result['type_6'], "pet_6" => $result['pet_6'], "picture_pet_6" => $result['picture_pet_6']
        );
    }
    echo json_encode($return_arr);
    exit();
}
if ($_POST["action"] === 'ADD') {
    if ($_POST["house_number"] !== '') {
        $house_number = $_POST["house_number"];
        $contact_name = $_POST["contact_name"];
        $phone_number = $_POST["phone_number"];
        $alley = $_POST["alley"];
        $pet_quantity = $_POST["pet_quantity"];
        //$create_by = $_SESSION['first_name'] . " " . $_SESSION['last_name'];

        /*
                $myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
                fwrite($myfile, $_POST["action"]. " | " . $house_number  . " | " . $contact_name . " | " . $phone_number . " | " . $alley);
                fclose($myfile);
        */
        // === จัดการการอัปโหลดรูปภาพ 1-6 ===
        $upload_dir = '../uploads/pet/';
        $picture_filenames = [];
        for ($i = 1; $i <= 6; $i++) {
            $picture_filenames['picture_pet_' . $i] = handlePetPictureUpload('picture_pet_' . $i, $upload_dir, '');
        }

        // ป้องกัน SQL Injection
        $sql_find = "SELECT COUNT(*) FROM ims_house_pet WHERE house_number = :house_number";
        $stmt = $conn->prepare($sql_find);
        $stmt->bindParam(':house_number', $house_number);
        $stmt->execute();
        $nRows = $stmt->fetchColumn();

        if ($nRows > 0) {
            echo $dup; // มีข้อมูลซ้ำ
        } else {
            // ปรับปรุง: เพิ่ม field ของสัตว์เลี้ยงทั้งหมดลงในคำสั่ง INSERT
            $sql = "INSERT INTO ims_house_pet (
                        house_number, contact_name, phone_number, alley, 
                        type_1, pet_1, picture_pet_1,
                        type_2, pet_2, picture_pet_2,
                        type_3, pet_3, picture_pet_3,
                        type_4, pet_4, picture_pet_4,
                        type_5, pet_5, picture_pet_5,
                        type_6, pet_6, picture_pet_6,
                        pet_quantity                        
                    ) VALUES (
                        :house_number, :contact_name, :phone_number, :alley,
                        :type_1, :pet_1, :picture_pet_1,
                        :type_2, :pet_2, :picture_pet_2,
                        :type_3, :pet_3, :picture_pet_3,
                        :type_4, :pet_4, :picture_pet_4,
                        :type_5, :pet_5, :picture_pet_5,
                        :type_6, :pet_6, :picture_pet_6,
                        :pet_quantity
                    )";
            $query = $conn->prepare($sql);
            // Bind ข้อมูลหลัก
            $query->bindParam(':house_number', $house_number, PDO::PARAM_STR);
            $query->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
            $query->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
            $query->bindParam(':alley', $alley, PDO::PARAM_STR);
            //$query->bindParam(':create_by', $create_by, PDO::PARAM_STR);

            // Bind ข้อมูลสัตว์เลี้ยงและรูปภาพ
            for ($i = 1; $i <= 6; $i++) {
                $query->bindParam(':type_' . $i, $_POST['type_' . $i], PDO::PARAM_STR);
                $query->bindParam(':pet_' . $i, $_POST['pet_' . $i], PDO::PARAM_STR);
                $query->bindParam(':picture_pet_' . $i, $picture_filenames['picture_pet_' . $i], PDO::PARAM_STR);
            }

            $query->bindParam(':pet_quantity', $pet_quantity, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();
            echo $lastInsertId ? $save_success : $error;
        }
    }
}

if ($_POST["action"] === 'UPDATE') {
    if ($_POST["contact_name"] !== '') {
        $id = $_POST["id"];
        $house_number = $_POST["house_number"];
        $contact_name = $_POST["contact_name"];
        $phone_number = $_POST["phone_number"];
        $alley = $_POST["alley"];
        $pet_quantity = $_POST["pet_quantity"];
        //$update_by = $_SESSION['first_name'] . " " . $_SESSION['last_name'] ?? 'LIFF_USER';

        $stmt_old = $conn->prepare("SELECT * FROM ims_house_pet WHERE id = :id");
        $stmt_old->bindParam(':id', $id);
        $stmt_old->execute();
        $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);

        $upload_dir = '../uploads/pet/';
        $picture_filenames = [];
        for ($i = 1; $i <= 6; $i++) {
            $existing_file = $old_data['picture_pet_' . $i] ?? '';
            $picture_filenames['picture_pet_' . $i] = handlePetPictureUpload('picture_pet_' . $i, $upload_dir, $existing_file);
        }
        /*
                $myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
                fwrite($myfile, $_POST["action"]. " | " . $house_number  . " | " . $contact_name . " | " . $phone_number . " | " . $alley);
                fclose($myfile);
        */

        // ✅ แก้ไข SQL: ลบ ; ที่อยู่ผิดที่ออก
        $sql_update = "UPDATE ims_house_pet SET 
            house_number = :house_number, contact_name = :contact_name, phone_number = :phone_number, alley = :alley,pet_quantity = :pet_quantity,
            type_1 = :type_1, pet_1 = :pet_1, picture_pet_1 = :picture_pet_1,
            type_2 = :type_2, pet_2 = :pet_2, picture_pet_2 = :picture_pet_2,
            type_3 = :type_3, pet_3 = :pet_3, picture_pet_3 = :picture_pet_3,
            type_4 = :type_4, pet_4 = :pet_4, picture_pet_4 = :picture_pet_4,
            type_5 = :type_5, pet_5 = :pet_5, picture_pet_5 = :picture_pet_5,
            type_6 = :type_6, pet_6 = :pet_6, picture_pet_6 = :picture_pet_6
            WHERE id = :id"; // <-- แก้ไขตรงนี้

        $query = $conn->prepare($sql_update);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->bindParam(':house_number', $house_number, PDO::PARAM_STR);
        $query->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
        $query->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
        $query->bindParam(':alley', $alley, PDO::PARAM_STR);
        $query->bindParam(':pet_quantity', $pet_quantity, PDO::PARAM_STR);

        for ($i = 1; $i <= 6; $i++) {
            $query->bindParam(':type_' . $i, $_POST['type_' . $i], PDO::PARAM_STR);
            $query->bindParam(':pet_' . $i, $_POST['pet_' . $i], PDO::PARAM_STR);
            $query->bindParam(':picture_pet_' . $i, $picture_filenames['picture_pet_' . $i], PDO::PARAM_STR);
        }

        $query->execute();
        echo $save_success;
    }
}

// Delete data
if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT COUNT(*) FROM ims_house_pet WHERE id = :id";
    $stmt = $conn->prepare($sql_find);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $nRows = $stmt->fetchColumn();

    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_house_pet WHERE id = :id";
            $query = $conn->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }

    exit;
}

if ($_POST["action"] === 'GET_PET') {

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
        $searchQuery = " AND (house_number LIKE :house_number or
        contact_name LIKE :contact_name ) ";
        $searchArray = array(
            'house_number' => "%$searchValue%",
            'contact_name' => "%$searchValue%",
        );
    }

    $where_house_number = "";

    if (($_SESSION['account_type']) === "house_user") {
        $where_house_number = " AND house_number = '" . $_SESSION['house_number'] . "' ";
    }

    /*
        $txt = $where_house_number;
        $my_file = fopen("device_a.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $txt);
        fclose($my_file);
    */

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_house_pet WHERE 1=1 " . $where_house_number);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_house_pet WHERE 1=1 " . $where_house_number . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records

    $sql_get_date = "SELECT * FROM ims_house_pet WHERE 1=1 " . $where_house_number . $searchQuery . " LIMIT :limit,:offset";

    $stmt = $conn->prepare($sql_get_date);

    /*
            $txt = $sql_get_date;
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
                "house_number" => $row['house_number'],
                "alley" => $row['alley'],
                "pet_quantity" => $row['pet_quantity'],
                "contact_name" => $row['contact_name'],
                "phone_number" => $row['phone_number'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "remark" => $row['remark']
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "house_number" => $row['house_number'],
                "contact_name" => $row['contact_name'],
                "select" => "<button type='button' name='select' id='" . $row['house_number'] . "@" . $row['contact_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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


