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

    $sql_find = "SELECT COUNT(*) FROM ims_house WHERE id = :id";
    $stmt = $conn->prepare($sql_find);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $nRows = $stmt->fetchColumn();
    if ($nRows > 0) {
        try {
            // Soft delete: set status to 'N' instead of physical DELETE
            $sql = "UPDATE ims_house SET status = 'N' WHERE id = :id";
            $query = $conn->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
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
                "change_holder" => "<a href='manage_change_house_holder.php?m=" . urlencode($_GET['m']) . "&s=" . urlencode('เปลี่ยนสิทธิ์ผู้อยู่อาศัย') . "&house_number=" . urlencode($row['house_number']) . "' class='btn btn-warning btn-xs' data-toggle='tooltip' title='เปลี่ยนสิทธิ์ผู้อยู่อาศัย'>เปลี่ยนสิทธิ์</a>",
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

if ($_POST["action"] === 'GET_HOUSE_DETAIL_FOR_CHANGE') {
    $house_number = $_POST["house_number"];
    
    // 1. ดึงข้อมูลบ้านหลักและข้อมูลมาสเตอร์
    $sql_house = "SELECT h.*, hm.area_size, hm.common_fee, hm.garbage_collection_fee 
                  FROM ims_house h
                  LEFT JOIN ims_house_master hm ON h.house_number = hm.house_number
                  WHERE h.house_number = :house_number";
    $stmt_house = $conn->prepare($sql_house);
    $stmt_house->execute([':house_number' => $house_number]);
    $house_data = $stmt_house->fetch(PDO::FETCH_ASSOC);
    
    if (!$house_data) {
        echo json_encode(["status" => "error", "message" => "ไม่พบข้อมูลบ้านเลขที่นี้ หรือบ้านถูกระงับสถานะไปแล้ว"]);
        exit;
    }
    
    // 2. นับบัญชี LINE OA
    $sql_line = "SELECT COUNT(*) AS line_count FROM ims_house_line_user WHERE house_number = :house_number AND status = 'Y'";
    $stmt_line = $conn->prepare($sql_line);
    $stmt_line->execute([':house_number' => $house_number]);
    $line_count = $stmt_line->fetchColumn();
    
    // 3. ดึงรายการรายชื่อ LINE OA
    $sql_line_list = "SELECT line_user_name, user_type FROM ims_house_line_user WHERE house_number = :house_number AND status = 'Y'";
    $stmt_line_list = $conn->prepare($sql_line_list);
    $stmt_line_list->execute([':house_number' => $house_number]);
    $line_users = $stmt_line_list->fetchAll(PDO::FETCH_ASSOC);

    // 4. นับสัตว์เลี้ยง
    $sql_pet = "SELECT COUNT(*) AS pet_count FROM ims_house_pet WHERE house_number = :house_number";
    $stmt_pet = $conn->prepare($sql_pet);
    $stmt_pet->execute([':house_number' => $house_number]);
    $pet_count = $stmt_pet->fetchColumn();
    
    // 5. คำนวณยอดค้างชำระ
    $monthly_rate = (float)($house_data['common_fee'] ?? 0);
    $year = (int)date('Y');
    $sql_pay = "SELECT period_month_start, period_month_to FROM ims_house_payment WHERE house_number = :house_number AND period_year = :year AND payment_status = 'Y'";
    $stmt_pay = $conn->prepare($sql_pay);
    $stmt_pay->execute([':house_number' => $house_number, ':year' => $year]);
    $payments = $stmt_pay->fetchAll(PDO::FETCH_ASSOC);
    
    $current_month = (int)date('n');
    $unpaid_months_count = 0;
    for ($m = 1; $m <= $current_month; $m++) {
        $is_paid = false;
        foreach ($payments as $p) {
            if ($m >= (int)$p['period_month_start'] && $m <= (int)$p['period_month_to']) {
                $is_paid = true;
                break;
            }
        }
        if (!$is_paid) {
            $unpaid_months_count++;
        }
    }
    $outstanding_amount = $unpaid_months_count * $monthly_rate;
    
    // 6. ตรวจสอบว่าบัญชีเว็บ (ims_user) อยู่ในระบบหรือไม่
    $web_user_status = "Inactive";
    if (!empty($house_data['phone_number'])) {
        $sql_web = "SELECT status FROM ims_user WHERE user_id = :phone";
        $stmt_web = $conn->prepare($sql_web);
        $stmt_web->execute([':phone' => $house_data['phone_number']]);
        $web_status = $stmt_web->fetchColumn();
        if ($web_status) {
            $web_user_status = $web_status;
        }
    }
    
    echo json_encode([
        "status" => "success",
        "data" => [
            "house_number" => $house_data['house_number'],
            "contact_name" => $house_data['contact_name'],
            "phone_number" => $house_data['phone_number'],
            "house_status" => $house_data['house_status'],
            "car_no1" => $house_data['car_no1'] ?? '',
            "car_brand" => $house_data['car_no1_brand'] ?? '',
            "car_color" => $house_data['car_no1_color'] ?? '',
            "car_province" => $house_data['car_no1_province'] ?? '',
            "car_type" => $house_data['car_no1_type'] ?? '',
            "remark" => $house_data['remark'] ?? '',
            "line_count" => $line_count,
            "line_users" => $line_users,
            "pet_count" => $pet_count,
            "outstanding_amount" => $outstanding_amount,
            "web_user_status" => $web_user_status
        ]
    ]);
    exit;
}

if ($_POST["action"] === 'GET_HOUSE_AUTOCOMPLETE') {
    $search = $_POST["search"] ?? '';
    $return_arr = array();
    $sql_get = "SELECT hm.house_number, h.contact_name 
                FROM ims_house_master hm 
                LEFT JOIN ims_house h ON hm.house_number = h.house_number 
                WHERE hm.house_number LIKE :search AND hm.status = 'Y' 
                ORDER BY CAST(hm.house_number AS UNSIGNED), hm.house_number 
                LIMIT 20";
    $stmt = $conn->prepare($sql_get);
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $result) {
        $contact = $result['contact_name'];
        $label = $result['house_number'] . ($contact ? ' (' . $contact . ')' : ' (บ้านว่าง)');
        $return_arr[] = array(
            'label' => $label,
            'value' => $result['house_number']
        );
    }
    echo json_encode($return_arr);
    exit();
}

if ($_POST["action"] === 'MOVE_HOUSE_MEMBER_SIMPLIFIED') {
    $old_house = $_POST["old_house_number"];
    $new_house = $_POST["new_house_number"];
    $new_status = $_POST["new_house_status"] ?? 'O';
    $admin_name = $_SESSION['alogin'] ?? 'Admin';

    if (empty($old_house) || empty($new_house)) {
        echo json_encode(["status" => "error", "message" => "กรุณาเลือกบ้านเลขที่เดิมและบ้านเลขที่ใหม่"]);
        exit;
    }

    if ($old_house === $new_house) {
        echo json_encode(["status" => "error", "message" => "บ้านเลขที่เดิมและบ้านเลขที่ใหม่ต้องไม่ซ้ำกัน"]);
        exit;
    }

    try {
        $conn->beginTransaction();

        // 1. ดึงข้อมูลผู้อยู่อาศัย ทะเบียนรถ และสติกเกอร์จากบ้านเดิม
        $stmt_old = $conn->prepare("SELECT * FROM ims_house WHERE house_number = :old_house");
        $stmt_old->execute([':old_house' => $old_house]);
        $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);

        if (!$old_data) {
            echo json_encode(["status" => "error", "message" => "ไม่พบข้อมูลบ้านเลขที่เดิมในตารางหลัก"]);
            $conn->rollBack();
            exit;
        }

        $resident_name = $old_data['contact_name'];
        $resident_phone = $old_data['phone_number'];

        // 2. เคลียร์ข้อมูลที่บ้านเดิม (ให้เป็นบ้านว่าง)
        $sql_clear = "UPDATE ims_house SET 
            contact_name = 'บ้านว่าง',
            phone_number = '',
            car_no1 = '', car_no1_province = '', car_no1_brand = '', car_no1_color = '', car_no1_type = '',
            car_no2 = '', car_no2_province = '', car_no2_brand = '', car_no2_color = '', car_no2_type = '',
            car_no3 = '', car_no3_province = '', car_no3_brand = '', car_no3_color = '', car_no3_type = '',
            car_no4 = '', car_no4_province = '', car_no4_brand = '', car_no4_color = '', car_no4_type = '',
            car_no5 = '', car_no5_province = '', car_no5_brand = '', car_no5_color = '', car_no5_type = '',
            car_no6 = '', car_no6_province = '', car_no6_brand = '', car_no6_color = '', car_no6_type = '',
            car_no7 = '', car_no7_province = '', car_no7_brand = '', car_no7_color = '', car_no7_type = '',
            car_no8 = '', car_no8_province = '', car_no8_brand = '', car_no8_color = '', car_no8_type = '',
            car_no9 = '', car_no9_province = '', car_no9_brand = '', car_no9_color = '', car_no9_type = '',
            car_no10 = '', car_no10_province = '', car_no10_brand = '', car_no10_color = '', car_no10_type = '',
            sticker_receive_status = 'N',
            house_status = 'V',
            remark = :clear_remark,
            update_by = :admin_name
            WHERE house_number = :old_house";
        $stmt_clear = $conn->prepare($sql_clear);
        $stmt_clear->execute([
            ':clear_remark' => "ย้ายออกไปยังบ้านเลขที่ " . $new_house,
            ':admin_name' => $admin_name,
            ':old_house' => $old_house
        ]);

        // 3. ตรวจสอบว่าบ้านใหม่มีอยู่แล้วในตาราง ims_house หรือไม่
        $stmt_new_check = $conn->prepare("SELECT id FROM ims_house WHERE house_number = :new_house");
        $stmt_new_check->execute([':new_house' => $new_house]);
        $new_house_exists = $stmt_new_check->fetch(PDO::FETCH_ASSOC);

        if ($new_house_exists) {
            // ทำการ UPDATE บ้านใหม่ด้วยข้อมูลที่ย้ายมา
            $sql_up_new = "UPDATE ims_house SET 
                contact_name = :contact_name,
                phone_number = :phone_number,
                car_no1 = :car1, car_no1_province = :prov1, car_no1_brand = :brand1, car_no1_color = :color1, car_no1_type = :type1,
                car_no2 = :car2, car_no2_province = :prov2, car_no2_brand = :brand2, car_no2_color = :color2, car_no2_type = :type2,
                car_no3 = :car3, car_no3_province = :prov3, car_no3_brand = :brand3, car_no3_color = :color3, car_no3_type = :type3,
                car_no4 = :car4, car_no4_province = :prov4, car_no4_brand = :brand4, car_no4_color = :color4, car_no4_type = :type4,
                car_no5 = :car5, car_no5_province = :prov5, car_no5_brand = :brand5, car_no5_color = :color5, car_no5_type = :type5,
                car_no6 = :car6, car_no6_province = :prov6, car_no6_brand = :brand6, car_no6_color = :color6, car_no6_type = :type6,
                car_no7 = :car7, car_no7_province = :prov7, car_no7_brand = :brand7, car_no7_color = :color7, car_no7_type = :type7,
                car_no8 = :car8, car_no8_province = :prov8, car_no8_brand = :brand8, car_no8_color = :color8, car_no8_type = :type8,
                car_no9 = :car9, car_no9_province = :prov9, car_no9_brand = :brand9, car_no9_color = :color9, car_no9_type = :type9,
                car_no10 = :car10, car_no10_province = :prov10, car_no10_brand = :brand10, car_no10_color = :color10, car_no10_type = :type10,
                sticker_receive_status = :sticker_status,
                house_status = :house_status,
                remark = :remark,
                update_by = :admin_name
                WHERE house_number = :new_house";
            $stmt_up_new = $conn->prepare($sql_up_new);
            $stmt_up_new->execute([
                ':contact_name' => $resident_name,
                ':phone_number' => $resident_phone,
                ':car1' => $old_data['car_no1'] ?? '', ':prov1' => $old_data['car_no1_province'] ?? '', ':brand1' => $old_data['car_no1_brand'] ?? '', ':color1' => $old_data['car_no1_color'] ?? '', ':type1' => $old_data['car_no1_type'] ?? '',
                ':car2' => $old_data['car_no2'] ?? '', ':prov2' => $old_data['car_no2_province'] ?? '', ':brand2' => $old_data['car_no2_brand'] ?? '', ':color2' => $old_data['car_no2_color'] ?? '', ':type2' => $old_data['car_no2_type'] ?? '',
                ':car3' => $old_data['car_no3'] ?? '', ':prov3' => $old_data['car_no3_province'] ?? '', ':brand3' => $old_data['car_no3_brand'] ?? '', ':color3' => $old_data['car_no3_color'] ?? '', ':type3' => $old_data['car_no3_type'] ?? '',
                ':car4' => $old_data['car_no4'] ?? '', ':prov4' => $old_data['car_no4_province'] ?? '', ':brand4' => $old_data['car_no4_brand'] ?? '', ':color4' => $old_data['car_no4_color'] ?? '', ':type4' => $old_data['car_no4_type'] ?? '',
                ':car5' => $old_data['car_no5'] ?? '', ':prov5' => $old_data['car_no5_province'] ?? '', ':brand5' => $old_data['car_no5_brand'] ?? '', ':color5' => $old_data['car_no5_color'] ?? '', ':type5' => $old_data['car_no5_type'] ?? '',
                ':car6' => $old_data['car_no6'] ?? '', ':prov6' => $old_data['car_no6_province'] ?? '', ':brand6' => $old_data['car_no6_brand'] ?? '', ':color6' => $old_data['car_no6_color'] ?? '', ':type6' => $old_data['car_no6_type'] ?? '',
                ':car7' => $old_data['car_no7'] ?? '', ':prov7' => $old_data['car_no7_province'] ?? '', ':brand7' => $old_data['car_no7_brand'] ?? '', ':color7' => $old_data['car_no7_color'] ?? '', ':type7' => $old_data['car_no7_type'] ?? '',
                ':car8' => $old_data['car_no8'] ?? '', ':prov8' => $old_data['car_no8_province'] ?? '', ':brand8' => $old_data['car_no8_brand'] ?? '', ':color8' => $old_data['car_no8_color'] ?? '', ':type8' => $old_data['car_no8_type'] ?? '',
                ':car9' => $old_data['car_no9'] ?? '', ':prov9' => $old_data['car_no9_province'] ?? '', ':brand9' => $old_data['car_no9_brand'] ?? '', ':color9' => $old_data['car_no9_color'] ?? '', ':type9' => $old_data['car_no9_type'] ?? '',
                ':car10' => $old_data['car_no10'] ?? '', ':prov10' => $old_data['car_no10_province'] ?? '', ':brand10' => $old_data['car_no10_brand'] ?? '', ':color10' => $old_data['car_no10_color'] ?? '', ':type10' => $old_data['car_no10_type'] ?? '',
                ':sticker_status' => $old_data['sticker_receive_status'] ?? 'N',
                ':house_status' => $new_status,
                ':remark' => "ย้ายเข้าจากบ้านเลขที่ " . $old_house,
                ':admin_name' => $admin_name,
                ':new_house' => $new_house
            ]);
        } else {
            // ค้นหาซอยจาก ims_house_master
            $stmt_alley = $conn->prepare("SELECT alley FROM ims_house_master WHERE house_number = :new_house");
            $stmt_alley->execute([':new_house' => $new_house]);
            $alley = $stmt_alley->fetchColumn() ?: '';

            // ทำการ INSERT บ้านหลังใหม่
            $sql_ins_new = "INSERT INTO ims_house (
                house_number, alley, contact_name, phone_number, house_status,
                car_no1, car_no1_province, car_no1_brand, car_no1_color, car_no1_type,
                car_no2, car_no2_province, car_no2_brand, car_no2_color, car_no2_type,
                car_no3, car_no3_province, car_no3_brand, car_no3_color, car_no3_type,
                car_no4, car_no4_province, car_no4_brand, car_no4_color, car_no4_type,
                car_no5, car_no5_province, car_no5_brand, car_no5_color, car_no5_type,
                car_no6, car_no6_province, car_no6_brand, car_no6_color, car_no6_type,
                car_no7, car_no7_province, car_no7_brand, car_no7_color, car_no7_type,
                car_no8, car_no8_province, car_no8_brand, car_no8_color, car_no8_type,
                car_no9, car_no9_province, car_no9_brand, car_no9_color, car_no9_type,
                car_no10, car_no10_province, car_no10_brand, car_no10_color, car_no10_type,
                sticker_receive_status, status, remark, update_by
            ) VALUES (
                :new_house, :alley, :contact_name, :phone_number, :house_status,
                :car1, :prov1, :brand1, :color1, :type1,
                :car2, :prov2, :brand2, :color2, :type2,
                :car3, :prov3, :brand3, :color3, :type3,
                :car4, :prov4, :brand4, :color4, :type4,
                :car5, :prov5, :brand5, :color5, :type5,
                :car6, :prov6, :brand6, :color6, :type6,
                :car7, :prov7, :brand7, :color7, :type7,
                :car8, :prov8, :brand8, :color8, :type8,
                :car9, :prov9, :brand9, :color9, :type9,
                :car10, :prov10, :brand10, :color10, :type10,
                :sticker_status, NULL, :remark, :admin_name
            )";
            $stmt_ins_new = $conn->prepare($sql_ins_new);
            $stmt_ins_new->execute([
                ':new_house' => $new_house,
                ':alley' => $alley,
                ':contact_name' => $resident_name,
                ':phone_number' => $resident_phone,
                ':house_status' => $new_status,
                ':car1' => $old_data['car_no1'] ?? '', ':prov1' => $old_data['car_no1_province'] ?? '', ':brand1' => $old_data['car_no1_brand'] ?? '', ':color1' => $old_data['car_no1_color'] ?? '', ':type1' => $old_data['car_no1_type'] ?? '',
                ':car2' => $old_data['car_no2'] ?? '', ':prov2' => $old_data['car_no2_province'] ?? '', ':brand2' => $old_data['car_no2_brand'] ?? '', ':color2' => $old_data['car_no2_color'] ?? '', ':type2' => $old_data['car_no2_type'] ?? '',
                ':car3' => $old_data['car_no3'] ?? '', ':prov3' => $old_data['car_no3_province'] ?? '', ':brand3' => $old_data['car_no3_brand'] ?? '', ':color3' => $old_data['car_no3_color'] ?? '', ':type3' => $old_data['car_no3_type'] ?? '',
                ':car4' => $old_data['car_no4'] ?? '', ':prov4' => $old_data['car_no4_province'] ?? '', ':brand4' => $old_data['car_no4_brand'] ?? '', ':color4' => $old_data['car_no4_color'] ?? '', ':type4' => $old_data['car_no4_type'] ?? '',
                ':car5' => $old_data['car_no5'] ?? '', ':prov5' => $old_data['car_no5_province'] ?? '', ':brand5' => $old_data['car_no5_brand'] ?? '', ':color5' => $old_data['car_no5_color'] ?? '', ':type5' => $old_data['car_no5_type'] ?? '',
                ':car6' => $old_data['car_no6'] ?? '', ':prov6' => $old_data['car_no6_province'] ?? '', ':brand6' => $old_data['car_no6_brand'] ?? '', ':color6' => $old_data['car_no6_color'] ?? '', ':type6' => $old_data['car_no6_type'] ?? '',
                ':car7' => $old_data['car_no7'] ?? '', ':prov7' => $old_data['car_no7_province'] ?? '', ':brand7' => $old_data['car_no7_brand'] ?? '', ':color7' => $old_data['car_no7_color'] ?? '', ':type7' => $old_data['car_no7_type'] ?? '',
                ':car8' => $old_data['car_no8'] ?? '', ':prov8' => $old_data['car_no8_province'] ?? '', ':brand8' => $old_data['car_no8_brand'] ?? '', ':color8' => $old_data['car_no8_color'] ?? '', ':type8' => $old_data['car_no8_type'] ?? '',
                ':car9' => $old_data['car_no9'] ?? '', ':prov9' => $old_data['car_no9_province'] ?? '', ':brand9' => $old_data['car_no9_brand'] ?? '', ':color9' => $old_data['car_no9_color'] ?? '', ':type9' => $old_data['car_no9_type'] ?? '',
                ':car10' => $old_data['car_no10'] ?? '', ':prov10' => $old_data['car_no10_province'] ?? '', ':brand10' => $old_data['car_no10_brand'] ?? '', ':color10' => $old_data['car_no10_color'] ?? '', ':type10' => $old_data['car_no10_type'] ?? '',
                ':sticker_status' => $old_data['sticker_receive_status'] ?? 'N',
                ':remark' => "ย้ายเข้าจากบ้านเลขที่ " . $old_house,
                ':admin_name' => $admin_name
            ]);
        }

        // 4. อัปเดต LINE user ให้ผูกกับบ้านใหม่
        if (!empty($resident_phone)) {
            $stmt_line = $conn->prepare("UPDATE ims_house_line_user SET house_number = :new_house, status = 'Y' WHERE line_phone = :phone");
            $stmt_line->execute([
                ':new_house' => $new_house,
                ':phone' => $resident_phone
            ]);
        }

        // 5. อัปเดต สัตว์เลี้ยง ให้ผูกกับบ้านใหม่
        $stmt_pet = $conn->prepare("UPDATE ims_house_pet SET house_number = :new_house, contact_name = :new_name WHERE house_number = :old_house");
        $stmt_pet->execute([
            ':new_house' => $new_house,
            ':new_name' => $resident_name,
            ':old_house' => $old_house
        ]);

        // 6. บันทึกประวัติการย้ายสิทธิ์ลงใน log
        $sql_log = "INSERT INTO ims_house_change_log 
            (house_number, change_type, old_contact_name, old_phone_number, old_house_status, new_contact_name, new_phone_number, new_house_status, remark, created_by)
            VALUES 
            (:old_house, 'MOVE_OUT_IN', :old_name, :old_phone, :old_status, :new_name, :new_phone, :new_status, :remark, :admin_name)";
        $stmt_log = $conn->prepare($sql_log);
        $stmt_log->execute([
            ':old_house' => $old_house,
            ':old_name' => $resident_name,
            ':old_phone' => $resident_phone,
            ':old_status' => $old_data['house_status'] ?? '',
            ':new_name' => $resident_name,
            ':new_phone' => $resident_phone,
            ':new_status' => $new_status,
            ':remark' => "ย้ายบ้านและสิทธิ์ทั้งหมดจาก " . $old_house . " ไปยัง " . $new_house,
            ':admin_name' => $admin_name
        ]);

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "โอนย้ายข้อมูลลูกบ้านเรียบร้อยแล้ว"]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาดในการโอนย้าย: " . $e->getMessage()]);
    }
    exit;
}

if ($_POST["action"] === 'CHANGE_HOUSE_HOLDER_FULL') {
    $house_number = $_POST["house_number"];
    $change_type = $_POST["change_type"];
    $new_name = $_POST["new_contact_name"];
    $new_phone = $_POST["new_phone_number"];
    $new_status = $_POST["new_house_status"];
    $car_no1 = $_POST["car_no1"] ?? '';
    $car_province = $_POST["car_no1_province"] ?? '';
    $car_brand = $_POST["car_no1_brand"] ?? '';
    $car_color = $_POST["car_no1_color"] ?? '';
    $car_type = $_POST["car_no1_type"] ?? '';
    $remark = $_POST["remark"] ?? '';
    
    $deactivate_old_user = isset($_POST["deactivate_old_user"]) && $_POST["deactivate_old_user"] === '1';
    $deactivate_line = isset($_POST["deactivate_line"]) && $_POST["deactivate_line"] === '1';
    $deactivate_stickers = isset($_POST["deactivate_stickers"]) && $_POST["deactivate_stickers"] === '1';
    
    $admin_name = $_SESSION['first_name'] . " " . $_SESSION['last_name'];

    try {
        $conn->beginTransaction();

        // 0. ตรวจสอบและสร้างตาราง log หากไม่มีในระบบ
        $conn->exec("CREATE TABLE IF NOT EXISTS ims_house_change_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            house_number VARCHAR(50) NOT NULL,
            change_type VARCHAR(50) NOT NULL,
            old_contact_name VARCHAR(255),
            old_phone_number VARCHAR(100),
            old_house_status VARCHAR(100),
            new_contact_name VARCHAR(255),
            new_phone_number VARCHAR(100),
            new_house_status VARCHAR(100),
            remark TEXT,
            created_by VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_house_log (house_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        // 1. ดึงข้อมูลลูกบ้านเดิมก่อนแก้ไขเพื่อเขียน Log และระงับบัญชีเว็บ
        $stmt_old = $conn->prepare("SELECT contact_name, phone_number, house_status FROM ims_house WHERE house_number = :house_number");
        $stmt_old->execute([':house_number' => $house_number]);
        $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);

        if ($old_data) {
            $old_phone = $old_data['phone_number'];
            
            // 2. ระงับการใช้งานบัญชีผู้ใช้เก่า (ims_user)
            if ($deactivate_old_user && !empty($old_phone)) {
                $stmt_deact = $conn->prepare("UPDATE ims_user SET status = 'Inactive' WHERE user_id = :old_phone");
                $stmt_deact->execute([':old_phone' => $old_phone]);

                // ล้าง Login Session บังคับ Logout
                $stmt_sess = $conn->prepare("DELETE FROM ims_user_login_logs WHERE user_id = :old_phone");
                $stmt_sess->execute([':old_phone' => $old_phone]);
            }
        }

        // 3. บันทึก Log การย้ายข้อมูล
        $log_sql = "INSERT INTO ims_house_change_log (house_number, change_type, old_contact_name, old_phone_number, old_house_status, new_contact_name, new_phone_number, new_house_status, remark, created_by)
                    VALUES (:house_number, :change_type, :old_name, :old_phone, :old_status, :new_name, :new_phone, :new_status, :remark, :admin_name)";
        $stmt_log = $conn->prepare($log_sql);
        $stmt_log->execute([
            ':house_number' => $house_number,
            ':change_type' => $change_type,
            ':old_name' => $old_data['contact_name'] ?? '',
            ':old_phone' => $old_phone ?? '',
            ':old_status' => $old_data['house_status'] ?? '',
            ':new_name' => $new_name,
            ':new_phone' => $new_phone,
            ':new_status' => $new_status,
            ':remark' => $remark,
            ':admin_name' => $admin_name
        ]);

        // 4. จัดการลบรถยนต์เดิมและสติกเกอร์จอดรถ
        if ($deactivate_stickers) {
            $stmt_reset_cars = $conn->prepare("UPDATE ims_house SET 
                car_no1 = :car1, car_no1_province = :prov1, car_no1_type = :type1, car_no1_brand = :brand1, car_no1_color = :color1,
                car_no2 = '', car_no2_province = '', car_no2_type = '', car_no2_brand = '', car_no2_color = '',
                car_no3 = '', car_no3_province = '', car_no3_type = '', car_no3_brand = '', car_no3_color = '',
                car_no4 = '', car_no4_province = '', car_no4_type = '', car_no4_brand = '', car_no4_color = '',
                car_no5 = '', car_no5_province = '', car_no5_type = '', car_no5_brand = '', car_no5_color = '',
                car_no6 = '', car_no6_province = '', car_no6_type = '', car_no6_brand = '', car_no6_color = '',
                car_no7 = '', car_no7_province = '', car_no7_type = '', car_no7_brand = '', car_no7_color = '',
                car_no8 = '', car_no8_province = '', car_no8_type = '', car_no8_brand = '', car_no8_color = '',
                sticker_receive_status = 'N', sticker_receive_date = NULL
                WHERE house_number = :house_number");
            $stmt_reset_cars->execute([
                ':car1' => $car_no1,
                ':prov1' => $car_province,
                ':type1' => $car_type,
                ':brand1' => $car_brand,
                ':color1' => $car_color,
                ':house_number' => $house_number
            ]);
        }

        // 5. อัปเดตข้อมูลบ้านหลัก
        $update_sql = "UPDATE ims_house SET 
                        contact_name = :new_name,
                        phone_number = :new_phone,
                        house_status = :new_status,
                        remark = :remark,
                        update_by = :admin_name
                       WHERE house_number = :house_number";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->execute([
            ':new_name' => $new_name,
            ':new_phone' => $new_phone,
            ':new_status' => $new_status,
            ':admin_name' => $admin_name,
            ':house_number' => $house_number
        ]);

        // 6. จัดการสิทธิ์การผูก LINE
        if ($deactivate_line) {
            if ($change_type === 'CHANGE_OWNER' || $change_type === 'MOVE_OUT_IN') {
                $line_sql = "UPDATE ims_house_line_user SET status = 'N' WHERE house_number = :house_number";
                $stmt_line = $conn->prepare($line_sql);
                $stmt_line->execute([':house_number' => $house_number]);
            } else if ($change_type === 'CHANGE_TENANT') {
                if (!empty($old_phone)) {
                    $line_sql = "UPDATE ims_house_line_user SET status = 'N' WHERE house_number = :house_number AND line_phone = :old_phone";
                    $stmt_line = $conn->prepare($line_sql);
                    $stmt_line->execute([':house_number' => $house_number, ':old_phone' => $old_phone]);
                }
            }
        }

        // 7. จัดการสัตว์เลี้ยงถ้าเจ้าของขายบ้าน (Change Owner) หรือย้ายออกทั้งหมด
        if ($change_type === 'CHANGE_OWNER' || $change_type === 'MOVE_OUT_IN') {
            $stmt_pet = $conn->prepare("UPDATE ims_house_pet SET remark = CONCAT('ย้ายออกจากการปรับปรุงสิทธิ์เมื่อ ', NOW()) WHERE house_number = :house_number");
            $stmt_pet->execute([':house_number' => $house_number]);
        } else {
            $stmt_pet_up = $conn->prepare("UPDATE ims_house_pet SET contact_name = :new_name, phone_number = :new_phone WHERE house_number = :house_number");
            $stmt_pet_up->execute([
                ':new_name' => $new_name,
                ':new_phone' => $new_phone,
                ':house_number' => $house_number
            ]);
        }

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "บันทึกข้อมูลเรียบร้อยแล้ว"]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(["status" => "error", "message" => "ข้อผิดพลาดฐานข้อมูล: " . $e->getMessage()]);
    }
    exit;
}