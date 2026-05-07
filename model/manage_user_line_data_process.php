<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

// --- 1. ดึงข้อมูลแสดงใน DataTable (Server-side) ---
if ($_POST["action"] === 'GET_USER_LINE_DATA') {
    $draw = $_POST['draw'];
    $row = (int)$_POST['start'];
    $rowperpage = (int)$_POST['length'];
    $searchValue = $_POST['search']['value'];

    $searchArray = array();
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (l.house_number LIKE :search 
                         OR l.line_phone LIKE :search 
                         OR l.line_user_name LIKE :search 
                         OR u.first_name LIKE :search) ";
        $searchArray = array('search' => "%$searchValue%");
    }

    // นับจำนวนทั้งหมดจากตารางหลัก l
    $stmtAll = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_house_line_user");
    $stmtAll->execute();
    $totalRecords = $stmtAll->fetch()['allcount'];

    // นับจำนวนหลังกรอง
    $sqlFilter = "SELECT COUNT(*) AS allcount FROM ims_house_line_user l 
                  LEFT JOIN ims_user u ON l.line_phone = u.user_id 
                  WHERE 1 " . $searchQuery;
    $stmtFilter = $conn->prepare($sqlFilter);
    foreach ($searchArray as $key => $search) { $stmtFilter->bindValue(':' . $key, $search, PDO::PARAM_STR); }
    $stmtFilter->execute();
    $totalRecordwithFilter = $stmtFilter->fetch()['allcount'];

    // ดึงข้อมูลจริง
    $sqlMain = "SELECT l.id, l.line_phone, l.house_number, m.alley , l.line_user_name, u.first_name, u.last_name 
                FROM ims_house_line_user l 
                LEFT JOIN ims_user u ON l.line_phone = u.user_id 
                LEFT JOIN ims_house_master m ON m.house_number = l.house_number
                WHERE 1 " . $searchQuery . " 
                ORDER BY l.id DESC LIMIT :limit, :offset";

    $stmt = $conn->prepare($sqlMain);
    foreach ($searchArray as $key => $search) { $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR); }
    $stmt->bindValue(':limit', $row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $rowperpage, PDO::PARAM_INT);
    $stmt->execute();

    $empRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = array();
    $no = $row + 1;

    foreach ($empRecords as $val) {
        $fullName = $val['first_name'] ? $val['first_name'] . " " . $val['last_name'] : "-";
        $data[] = array(
            "no"             => $no,
            "phone_number"   => $val['line_phone'],
            "member_name"    => $fullName,
            "house_number"   => $val['house_number'],
            "alley"          => $val['alley'],
            "line_user_name" => $val['line_user_name'],
            "action"         => "<button type='button' id='".$val['id']."' class='btn btn-warning btn-xs update mr-1'><i class='fa fa-edit'></i> แก้ไข</button>"
                                 . "<button type='button' id='".$val['id']."' class='btn btn-danger btn-xs delete'><i class='fa fa-trash'></i> ลบ</button>"
        );
        $no++;
    }

    echo json_encode(array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    ));
    exit;
}

// --- 2. ดึงข้อมูล "ทุกฟิลด์" จาก 3 ตารางเพื่อแสดงก่อนลบ ---
if ($_POST["action"] === 'GET_ALL_FIELDS') {
    $id = $_POST["id"];
    $sql = "SELECT l.id, l.line_phone, l.house_number, l.line_user_name, l.line_user_id, l.line_picture_profile, l.create_date,
                   u.user_id as u_user_id, u.first_name, u.last_name, u.account_type,
                   h.contact_name, h.house_status,
                   m.alley
            FROM ims_house_line_user l
            LEFT JOIN ims_user u ON l.line_phone = u.user_id
            LEFT JOIN ims_house h ON l.line_phone = h.phone_number AND l.house_number = h.house_number
            LEFT JOIN ims_house_master m ON m.house_number = l.house_number
            WHERE l.id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// --- 3. แก้ไขข้อมูล ims_house_line_user (house_number) และ ims_house_master (alley) ---
if ($_POST["action"] === 'UPDATE') {
    $id = $_POST["id"];
    $house_number = $_POST["house_number"];
    $alley = $_POST["alley"];

    try {
        $conn->beginTransaction();

        // อัปเดต house_number ใน ims_house_line_user
        $stmt1 = $conn->prepare("UPDATE ims_house_line_user SET house_number = :house_number WHERE id = :id");
        $stmt1->execute([':house_number' => $house_number, ':id' => $id]);

        // อัปเดต alley ใน ims_house_master
        $stmt2 = $conn->prepare("UPDATE ims_house_master SET alley = :alley WHERE house_number = :house_number");
        $stmt2->execute([':alley' => $alley, ':house_number' => $house_number]);

        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
    exit;
}

// --- 4. ลบข้อมูล 3 ตาราง (Transaction) โดยใช้ Phone Number ---
if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $stmt_find = $conn->prepare("SELECT line_phone FROM ims_house_line_user WHERE id = :id");
    $stmt_find->execute([':id' => $id]);
    $row = $stmt_find->fetch();

    if ($row) {
        $phone_key = $row['line_phone'];
        try {
            $conn->beginTransaction();

            // ลบจาก ims_house_line_user
            $conn->prepare("DELETE FROM ims_house_line_user WHERE line_phone = :p")->execute([':p' => $phone_key]);
            // ลบจาก ims_house
            $conn->prepare("DELETE FROM ims_house WHERE phone_number = :p")->execute([':p' => $phone_key]);
            // ลบจาก ims_user
            $conn->prepare("DELETE FROM ims_user WHERE user_id = :p")->execute([':p' => $phone_key]);

            $conn->commit();
            echo "success";
        } catch (Exception $e) {
            $conn->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }
    exit;
}