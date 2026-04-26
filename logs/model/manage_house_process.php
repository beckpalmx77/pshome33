<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_ims_house "
        . " WHERE v_ims_house.id = " . $id;

    //$myfile = fopen("myqeury_1.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_get);
    //fclose($myfile);

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "house_number" => $result['house_number'],
            "contact_name" => $result['contact_name'],
            "house_name" => $result['house_name'],
            "house_status" => $result['house_status'],
            "phone_number" => $result['phone_number'],
            "line_picture_profile" => $result['line_picture_profile'],
            "remark" => $result['remark'],
            "car_no1" => $result['car_no1'],
            "car_no2" => $result['car_no2'],
            "car_no3" => $result['car_no3'],
            "car_no4" => $result['car_no4'],
            "car_no5" => $result['car_no5'],
            "alley" => $result['alley']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["house_number"] !== '') {

        $house_number = $_POST["house_number"];
        $sql_find = "SELECT * FROM ims_house WHERE house_number = '" . $house_number . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["contact_name"] !== '') {
        $house_number = $_POST["house_number"];
        $contact_name = $_POST["contact_name"];
        $phone_number = $_POST["phone_number"];
        $alley = $_POST["alley"];
        $remark = $_POST["remark"];
        $house_status = $_POST["house_status"];
        $car_no1 = $_POST["car_no1"];
        $car_no2 = $_POST["car_no2"];
        $car_no3 = $_POST["car_no3"];
        $car_no4 = $_POST["car_no4"];
        $car_no5 = $_POST["car_no5"];
        $create_by = $_SESSION['first_name'] . " " . $_SESSION['last_name'];

        // ป้องกัน SQL Injection โดยใช้ prepare
        $sql_find = "SELECT COUNT(*) FROM ims_house WHERE house_number = :house_number";
        $stmt = $conn->prepare($sql_find);
        $stmt->bindParam(':house_number', $house_number);
        $stmt->execute();
        $nRows = $stmt->fetchColumn();

        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO ims_house (
                        house_number, contact_name, phone_number, alley, remark,
                        car_no1, car_no2, car_no3, car_no4, car_no5, house_status, create_by
                    ) VALUES (
                        :house_number, :contact_name, :phone_number, :alley, :remark,
                        :car_no1, :car_no2, :car_no3, :car_no4, :car_no5, :house_status, :create_by
                    )";
            $query = $conn->prepare($sql);
            $query->bindParam(':house_number', $house_number, PDO::PARAM_STR);
            $query->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
            $query->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
            $query->bindParam(':alley', $alley, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':car_no1', $car_no1, PDO::PARAM_STR);
            $query->bindParam(':car_no2', $car_no2, PDO::PARAM_STR);
            $query->bindParam(':car_no3', $car_no3, PDO::PARAM_STR);
            $query->bindParam(':car_no4', $car_no4, PDO::PARAM_STR);
            $query->bindParam(':car_no5', $car_no5, PDO::PARAM_STR);
            $query->bindParam(':house_status', $house_status, PDO::PARAM_STR);
            $query->bindParam(':create_by', $create_by, PDO::PARAM_STR);
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
        $remark = $_POST["remark"];
        $house_status = $_POST["house_status"];
        $car_no1 = $_POST["car_no1"];
        $car_no2 = $_POST["car_no2"];
        $car_no3 = $_POST["car_no3"];
        $car_no4 = $_POST["car_no4"];
        $car_no5 = $_POST["car_no5"];
        $update_by = $_SESSION['first_name'] . " " . $_SESSION['last_name'];

        $sql_find = "SELECT COUNT(*) FROM ims_house WHERE id = :id";
        $stmt = $conn->prepare($sql_find);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $nRows = $stmt->fetchColumn();

        if ($nRows > 0) {
            $sql_update = "UPDATE ims_house SET 
                house_number = :house_number,
                contact_name = :contact_name,
                alley = :alley,
                phone_number = :phone_number,
                remark = :remark,
                car_no1 = :car_no1,
                car_no2 = :car_no2,
                car_no3 = :car_no3,
                car_no4 = :car_no4,
                car_no5 = :car_no5,
                house_status = :house_status,
                update_by = :update_by                
                WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':house_number', $house_number, PDO::PARAM_STR);
            $query->bindParam(':contact_name', $contact_name, PDO::PARAM_STR);
            $query->bindParam(':alley', $alley, PDO::PARAM_STR);
            $query->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':car_no1', $car_no1, PDO::PARAM_STR);
            $query->bindParam(':car_no2', $car_no2, PDO::PARAM_STR);
            $query->bindParam(':car_no3', $car_no3, PDO::PARAM_STR);
            $query->bindParam(':car_no4', $car_no4, PDO::PARAM_STR);
            $query->bindParam(':car_no5', $car_no5, PDO::PARAM_STR);
            $query->bindParam(':house_status', $house_status, PDO::PARAM_STR);
            $query->bindParam(':update_by', $update_by, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();
            echo $save_success;
        }
    }
}


if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM ims_house WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_house WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_HOUSE') {

    // 1. รับค่าจาก DataTables
    $draw = $_POST['draw'];
    $start = $_POST['start']; // จุดเริ่มต้น (Offset)
    $length = $_POST['length']; // จำนวนแถวต่อหน้า (Limit)

    // การจัดการเรื่อง Sorting
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];

    // ป้องกัน SQL Injection จากชื่อ Column (Whitelist หรือ Check เบื้องต้น)
    // ถ้าไม่มีการส่งค่ามา ให้เรียงตาม id เป็น default
    if (empty($columnName)) {
        $columnName = 'id';
        $columnSortOrder = 'DESC';
    }

    $searchValue = $_POST['search']['value'];

    // 2. กำหนด Fields ที่จะ Select ตาม Sub Action
    // เทคนิค: เลือกเฉพาะสิ่งที่ใช้ เพื่อลดการใช้ Memory
    if ($_POST['sub_action'] === "GET_MASTER") {
        $selectFields = "id, house_number, alley, area_size, garbage_collection_fee, common_fee, contact_name, phone_number, house_status, line_picture_profile, remark";
    } else {
        $selectFields = "id, house_number, contact_name";
    }

    $searchArray = array();
    $searchQuery = "";

    // 3. สร้างเงื่อนไข Search
    if ($searchValue != '') {
        $searchQuery = " AND (house_number LIKE :house_number OR contact_name LIKE :contact_name) ";
        $searchArray = array(
            'house_number' => "%$searchValue%",
            'contact_name' => "%$searchValue%",
        );
    }

    // 4. สร้างเงื่อนไข Session (ใช้ Parameter Binding เพื่อความปลอดภัย)
    $where_house_number = "";
    if (($_SESSION['account_type']) === "house_user") {
        $where_house_number = " AND house_number = :session_house_number ";
        // เราจะ bind ค่านี้ทีหลัง
    }

    // --- Query 1: นับจำนวนทั้งหมด (ไม่กรอง) ---
    $sql_count_all = "SELECT COUNT(id) AS allcount FROM v_ims_house WHERE 1=1 " . $where_house_number;
    $stmt = $conn->prepare($sql_count_all);
    if (!empty($where_house_number)) {
        $stmt->bindValue(':session_house_number', $_SESSION['house_number'], PDO::PARAM_STR);
    }
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    // --- Query 2: นับจำนวนที่ผ่านการกรอง (Search) ---
    $sql_count_filter = "SELECT COUNT(id) AS allcount FROM v_ims_house WHERE 1=1 " . $where_house_number . $searchQuery;
    $stmt = $conn->prepare($sql_count_filter);

    // Bind Search Params
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }
    // Bind Session Param (ถ้ามี)
    if (!empty($where_house_number)) {
        $stmt->bindValue(':session_house_number', $_SESSION['house_number'], PDO::PARAM_STR);
    }
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    // --- Query 3: ดึงข้อมูลจริง (Fetch Records) ---
    // เพิ่ม ORDER BY เพื่อให้ DataTables เรียงข้อมูลได้
    $sql_get_data = "SELECT " . $selectFields . " FROM v_ims_house 
                     WHERE 1=1 " . $where_house_number . $searchQuery . " 
                     ORDER BY " . $columnName . " " . $columnSortOrder . " 
                     LIMIT :offset, :limit";

    $stmt = $conn->prepare($sql_get_data);

    // Bind Search Params
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }
    // Bind Session Param
    if (!empty($where_house_number)) {
        $stmt->bindValue(':session_house_number', $_SESSION['house_number'], PDO::PARAM_STR);
    }

    // Bind Pagination (Int ต้องระบุ Type ให้ชัดเจน)
    // หมายเหตุ: SQL LIMIT offset, length
    $stmt->bindValue(':offset', (int)$start, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);

    $stmt->execute();
    $empRecords = $stmt->fetchAll();

    $data = array();

    foreach ($empRecords as $row) {
        if ($_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id" => $row['id'],
                "house_number" => $row['house_number'],
                "alley" => $row['alley'],
                "area_size" => $row['area_size'],
                "garbage_collection_fee" => $row['garbage_collection_fee'],
                "common_fee" => $row['common_fee'],
                "contact_name" => $row['contact_name'],
                "phone_number" => $row['phone_number'],
                "house_status" => $row['house_status'],
                "line_picture_profile" => $row['line_picture_profile'],
                // ปุ่ม HTML ควรพิจารณาย้ายไปทำที่ฝั่ง JavaScript (render) เพื่อความ clean ของ data
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "remark" => $row['remark']
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "house_number" => $row['house_number'],
                "contact_name" => $row['contact_name'],
                "select" => "<button type='button' name='select' id='" . $row['house_number'] . "@" . $row['contact_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i></button>",
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