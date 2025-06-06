<?php
session_start();
// เปิดเฉพาะตอนพัฒนาเท่านั้น
// ini_set('display_errors', 1); error_reporting(E_ALL);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';

if ($_POST["action"] === 'GET_CHECK_IN_OUT') {

    $userId = $_POST['userId'] ?? '';

    if (empty($userId)) {
        echo json_encode([
            "draw" => intval($_POST['draw'] ?? 0),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => []
        ]);
        exit;
    }

    // Input สำหรับ DataTables
    $draw = intval($_POST['draw'] ?? 0);
    $row = intval($_POST['start'] ?? 0);
    $rowperpage = intval($_POST['length'] ?? 10);
    $columnIndex = intval($_POST['order'][0]['column'] ?? 0);
    $columnName = $_POST['columns'][$columnIndex]['data'] ?? 'id';
    $columnSortOrder = $_POST['order'][0]['dir'] ?? 'desc';
    $searchValue = $_POST['search']['value'] ?? '';

    $searchArray = [
        'line_user_id' => $userId
    ];

    // เงื่อนไขค้นหา
    $searchQuery = "";
    if (!empty($searchValue)) {
        $searchQuery .= " AND (display_name LIKE :display_name OR checkin_time LIKE :checkin_time) ";
        $searchArray['display_name'] = "%$searchValue%";
        $searchArray['checkin_time'] = "%$searchValue%";
    }

    // นับทั้งหมด
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_checkins_time WHERE line_user_id = :line_user_id");
    $stmt->execute(['line_user_id' => $userId]);
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    // นับตาม filter
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_checkins_time WHERE line_user_id = :line_user_id $searchQuery");
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    // ดึงข้อมูลจริง
    $sql = "SELECT * FROM v_checkins_time 
            WHERE line_user_id = :line_user_id $searchQuery 
            ORDER BY id DESC 
            LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($sql);

    foreach ($searchArray as $key => $value) {
        $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', $rowperpage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $row, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();

    $data = [];
    foreach ($empRecords as $row) {
        if ($_POST['sub_action'] === "GET_MASTER") {
            $mapLink = (!empty($row['latitude']) && !empty($row['longitude']))
                ? "<a href='https://www.google.com/maps?q={$row['latitude']},{$row['longitude']}' target='_blank' class='btn btn-sm btn-outline-primary' title='เปิดแผนที่'><i class='fa fa-map-marker'></i> Map</a>"
                : "";

            $data[] = [
                "id" => $row['id'],
                "display_name" => $row['display_name'],
                "emp_name" => $row['emp_name'],
                "line_picture_profile" => $row['line_picture_profile'],
                "checkin_time" => $row['checkin_time'],
                "place_name" => $row['place_name'],
                "latitude" => $row['latitude'],
                "longitude" => $row['longitude'],
                "check_type" => $row['check_type'],
                "photo_path" => $row['photo_path'],
                "map" => $mapLink,
                "update" => "<button type='button' name='update' id='{$row['id']}' class='btn btn-info btn-xs update' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='{$row['id']}' class='btn btn-danger btn-xs delete' title='Delete'>Delete</button>",
                "detail" => "<button type='button' name='detail' id='{$row['id']}' class='btn btn-secondary btn-xs detail' title='Detail'>Detail</button>",
                "remark" => $row['remark']
            ];
        } else {
            $data[] = [
                "id" => $row['id'],
                "display_name" => $row['display_name'],
                "checkin_time" => $row['checkin_time'],
                "select" => "<button type='button' name='select' id='{$row['display_name']}@{$row['checkin_time']}' class='btn btn-outline-success btn-xs select' title='select'>select <i class='fa fa-check'></i></button>"
            ];
        }
    }

    echo json_encode([
        "draw" => $draw,
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    ]);
}