<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

ob_clean();

if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

include('../config/connect_db.php');
include('../config/lang.php');

function handlePictureUpload($fileInputName, $uploadDir, $existingFileName = '')
{
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES[$fileInputName]['tmp_name'];
        $fileName = $_FILES[$fileInputName]['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $dest_path = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            return $newFileName;
        }
    }
    return $existingFileName;
}

function sendVisitorLineNotification($conn, $house_number, $visitor_name, $visitor_type, $license_plate, $purpose, $check_in_time) {
    // 1. Get all LINE users for this house
    $sql = "SELECT line_user_id FROM ims_house_line_user WHERE house_number = :house_number AND status = 'Y' AND line_user_id IS NOT NULL AND line_user_id != ''";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':house_number', $house_number, PDO::PARAM_STR);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($users) === 0) {
        return; // No registered LINE users for this house
    }

    $token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

    $message_text = "📢 แจ้งเตือนผู้มาติดต่อ (Visitor Notification)\n"
                  . "ขณะนี้มีผู้มาติดต่อคุณที่บ้านเลขที่: " . $house_number . "\n"
                  . "👤 ชื่อผู้ติดต่อ: " . $visitor_name . "\n"
                  . "ประเภท: " . ($visitor_type ? $visitor_type : 'ผู้มาเยือน') . "\n"
                  . "🚗 ทะเบียนรถ: " . ($license_plate ? $license_plate : '-') . "\n"
                  . "วัตถุประสงค์: " . ($purpose ? $purpose : '-') . "\n"
                  . "เวลาเข้า: " . date('d/m/Y H:i', strtotime($check_in_time)) . " น.";

    foreach ($users as $user) {
        $line_user_id = $user['line_user_id'];
        
        $url = 'https://api.line.me/v2/bot/message/push';
        $data = [
            'to' => $line_user_id,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message_text
                ]
            ]
        ];
        $post = json_encode($data);
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
    }
}

if ($_POST["action"] === 'GET_HOUSE_AUTOCOMPLETE') {
    $search = $_POST["search"];
    $return_arr = array();
    $sql_get = "SELECT house_number FROM ims_house_master WHERE house_number LIKE :search AND status = 'Y' ORDER BY house_number LIMIT 20";
    $stmt = $conn->prepare($sql_get);
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $result) {
        $return_arr[] = $result['house_number'];
    }
    echo json_encode($return_arr);
}

if ($_POST["action"] === 'GET_DATA_BY_HOUSE') {
    $house_number = $_POST["house_number"];
    $return_arr = array();

    $sql_get = "SELECT * FROM ims_house_master WHERE house_number = :house_number";
    $stmt = $conn->prepare($sql_get);
    $stmt->bindParam(':house_number', $house_number, PDO::PARAM_STR);
    $stmt->execute();
    $house = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($house) {
        $return_arr['house'] = array(
            "house_number" => $house['house_number'],
            "contact_name" => $house['house_number'],
            "phone_number" => '',
            "alley" => $house['alley']
        );

        $sql_visitors = "SELECT * FROM ims_visitor_contact WHERE house_number = :house_number ORDER BY id DESC";
        $stmt2 = $conn->prepare($sql_visitors);
        $stmt2->bindParam(':house_number', $house_number, PDO::PARAM_STR);
        $stmt2->execute();
        $visitors = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $return_arr['visitors'] = array();
        foreach ($visitors as $v) {
            $return_arr['visitors'][] = array(
                "id" => $v['id'],
                "visitor_name" => $v['visitor_name'],
                "phone_number" => $v['phone_number'],
                "license_plate" => $v['license_plate'],
                "visitor_type" => $v['visitor_type'],
                "purpose" => $v['purpose'],
                "note" => $v['note'],
                "card_exchange" => $v['card_exchange'],
                "card_no" => $v['card_no'],
                "card_exchange_date" => $v['card_exchange_date'],
                "check_in_status" => $v['check_in_status'],
                "check_in_datetime" => $v['check_in_datetime'],
                "check_out_datetime" => $v['check_out_datetime'],
                "picture_1" => $v['picture_1'],
                "picture_2" => $v['picture_2'],
                "picture_3" => $v['picture_3'],
                "picture_4" => $v['picture_4'],
                "picture_5" => $v['picture_5']
            );
        }
    } else {
        $return_arr = null;
    }

    echo json_encode($return_arr);
}

if ($_POST["action"] === 'SAVE_VISITOR') {
    $id = $_POST["id"];
    $house_number = $_POST["house_number"];
    $visitor_name = $_POST["visitor_name"];
    $visitor_phone = $_POST["visitor_phone"];
    $license_plate = $_POST["license_plate"];
    $visitor_type = $_POST["visitor_type"];
    $purpose = $_POST["purpose"];
    $note = $_POST["note"];
    $card_exchange = $_POST["card_exchange"];
    $card_no = $_POST["card_no"];

    $uploadDir = '../uploads/visitor/';
    $existing_picture_1 = $_POST["existing_picture_1"];
    $existing_picture_2 = $_POST["existing_picture_2"];
    $existing_picture_3 = $_POST["existing_picture_3"];
    $existing_picture_4 = $_POST["existing_picture_4"];
    $existing_picture_5 = $_POST["existing_picture_5"];

    $picture_1 = handlePictureUpload('picture_1', $uploadDir, $existing_picture_1);
    $picture_2 = handlePictureUpload('picture_2', $uploadDir, $existing_picture_2);
    $picture_3 = handlePictureUpload('picture_3', $uploadDir, $existing_picture_3);
    $picture_4 = handlePictureUpload('picture_4', $uploadDir, $existing_picture_4);
    $picture_5 = handlePictureUpload('picture_5', $uploadDir, $existing_picture_5);

    $card_exchange_date = null;
    if ($card_exchange === 'Y') {
        $card_exchange_date = date('Y-m-d H:i:s');
    }

    $create_by = $_SESSION['alogin'];
    $update_by = $_SESSION['alogin'];

    if ($id > 0) {
        $sql_update = "UPDATE ims_visitor_contact SET 
            visitor_name = :visitor_name,
            phone_number = :visitor_phone,
            license_plate = :license_plate,
            visitor_type = :visitor_type,
            purpose = :purpose,
            note = :note,
            card_exchange = :card_exchange,
            card_no = :card_no,
            card_exchange_date = :card_exchange_date,
            picture_1 = :picture_1,
            picture_2 = :picture_2,
            picture_3 = :picture_3,
            picture_4 = :picture_4,
            picture_5 = :picture_5,
            update_by = :update_by,
            update_datetime = NOW()
            WHERE id = :id";

        $stmt = $conn->prepare($sql_update);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':visitor_name', $visitor_name, PDO::PARAM_STR);
        $stmt->bindParam(':visitor_phone', $visitor_phone, PDO::PARAM_STR);
        $stmt->bindParam(':license_plate', $license_plate, PDO::PARAM_STR);
        $stmt->bindParam(':visitor_type', $visitor_type, PDO::PARAM_STR);
        $stmt->bindParam(':purpose', $purpose, PDO::PARAM_STR);
        $stmt->bindParam(':note', $note, PDO::PARAM_STR);
        $stmt->bindParam(':card_exchange', $card_exchange, PDO::PARAM_STR);
        $stmt->bindParam(':card_no', $card_no, PDO::PARAM_STR);
        $stmt->bindParam(':card_exchange_date', $card_exchange_date, PDO::PARAM_STR);
        $stmt->bindParam(':picture_1', $picture_1, PDO::PARAM_STR);
        $stmt->bindParam(':picture_2', $picture_2, PDO::PARAM_STR);
        $stmt->bindParam(':picture_3', $picture_3, PDO::PARAM_STR);
        $stmt->bindParam(':picture_4', $picture_4, PDO::PARAM_STR);
        $stmt->bindParam(':picture_5', $picture_5, PDO::PARAM_STR);
        $stmt->bindParam(':update_by', $update_by, PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $check_in_status = (isset($_POST["check_in_status"])) ? $_POST["check_in_status"] : 'N';
        $check_in_datetime = null;
        $check_in_by = null;
        if ($check_in_status === 'Y') {
            $check_in_datetime = date('Y-m-d H:i:s');
            $check_in_by = $create_by;
        }
        
        $sql_insert = "INSERT INTO ims_visitor_contact (
            house_number, visitor_name, phone_number, license_plate, visitor_type, purpose, note,
            card_exchange, card_no, card_exchange_date,
            check_in_status, check_in_datetime, check_in_by,
            picture_1, picture_2, picture_3, picture_4, picture_5,
            create_by, create_datetime
        ) VALUES (
            :house_number, :visitor_name, :visitor_phone, :license_plate, :visitor_type, :purpose, :note,
            :card_exchange, :card_no, :card_exchange_date,
            :check_in_status, :check_in_datetime, :check_in_by,
            :picture_1, :picture_2, :picture_3, :picture_4, :picture_5,
            :create_by, NOW()
        )";

        $stmt = $conn->prepare($sql_insert);
        $stmt->bindParam(':house_number', $house_number, PDO::PARAM_STR);
        $stmt->bindParam(':visitor_name', $visitor_name, PDO::PARAM_STR);
        $stmt->bindParam(':visitor_phone', $visitor_phone, PDO::PARAM_STR);
        $stmt->bindParam(':license_plate', $license_plate, PDO::PARAM_STR);
        $stmt->bindParam(':visitor_type', $visitor_type, PDO::PARAM_STR);
        $stmt->bindParam(':purpose', $purpose, PDO::PARAM_STR);
        $stmt->bindParam(':note', $note, PDO::PARAM_STR);
        $stmt->bindParam(':card_exchange', $card_exchange, PDO::PARAM_STR);
        $stmt->bindParam(':card_no', $card_no, PDO::PARAM_STR);
        $stmt->bindParam(':card_exchange_date', $card_exchange_date, PDO::PARAM_STR);
        $stmt->bindParam(':check_in_status', $check_in_status, PDO::PARAM_STR);
        $stmt->bindParam(':check_in_datetime', $check_in_datetime, PDO::PARAM_STR);
        $stmt->bindParam(':check_in_by', $check_in_by, PDO::PARAM_STR);
        $stmt->bindParam(':picture_1', $picture_1, PDO::PARAM_STR);
        $stmt->bindParam(':picture_2', $picture_2, PDO::PARAM_STR);
        $stmt->bindParam(':picture_3', $picture_3, PDO::PARAM_STR);
        $stmt->bindParam(':picture_4', $picture_4, PDO::PARAM_STR);
        $stmt->bindParam(':picture_5', $picture_5, PDO::PARAM_STR);
        $stmt->bindParam(':create_by', $create_by, PDO::PARAM_STR);
        $stmt->execute();
        $id = $conn->lastInsertId();
        
        if ($check_in_status === 'Y') {
            sendVisitorLineNotification(
                $conn, 
                $house_number, 
                $visitor_name, 
                $visitor_type, 
                $license_plate, 
                $purpose, 
                $check_in_datetime
            );
        }
    }

    echo json_encode(array("result" => "1", "id" => $id));
}

if ($_POST["action"] === 'CHECK_IN') {
    $id = $_POST["id"];
    $check_in_by = $_SESSION['alogin'];

    $sql_get = "SELECT * FROM ims_visitor_contact WHERE id = :id";
    $stmt_get = $conn->prepare($sql_get);
    $stmt_get->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt_get->execute();
    $visitor = $stmt_get->fetch(PDO::FETCH_ASSOC);

    if ($visitor) {
        $now = date('Y-m-d H:i:s');
        $sql_update = "UPDATE ims_visitor_contact SET 
            check_in_status = 'Y',
            check_in_datetime = :now,
            check_in_by = :check_in_by
            WHERE id = :id";

        $stmt = $conn->prepare($sql_update);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':now', $now, PDO::PARAM_STR);
        $stmt->bindParam(':check_in_by', $check_in_by, PDO::PARAM_STR);
        $stmt->execute();

        sendVisitorLineNotification(
            $conn, 
            $visitor['house_number'], 
            $visitor['visitor_name'], 
            $visitor['visitor_type'], 
            $visitor['license_plate'], 
            $visitor['purpose'], 
            $now
        );

        echo json_encode(array("result" => "1"));
    } else {
        echo json_encode(array("result" => "0"));
    }
}

if ($_POST["action"] === 'CHECK_OUT') {
    $id = $_POST["id"];
    $check_out_by = $_SESSION['alogin'];

    $sql_update = "UPDATE ims_visitor_contact SET 
        check_in_status = 'N',
        check_out_datetime = NOW(),
        check_out_by = :check_out_by
        WHERE id = :id";

    $stmt = $conn->prepare($sql_update);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':check_out_by', $check_out_by, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode(array("result" => "1"));
}

if ($_POST["action"] === 'SAVE_CHECK_IN_OUT') {
    $id = $_POST["id"];
    $check_in_status = $_POST["check_in_status"];
    $check_in_datetime = $_POST["check_in_datetime"];
    $check_out_datetime = $_POST["check_out_datetime"];

    $sql_update = "UPDATE ims_visitor_contact SET 
        check_in_status = :check_in_status,
        check_in_datetime = :check_in_datetime,
        check_out_datetime = :check_out_datetime
        WHERE id = :id";

    $stmt = $conn->prepare($sql_update);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':check_in_status', $check_in_status, PDO::PARAM_STR);
    $stmt->bindParam(':check_in_datetime', $check_in_datetime, PDO::PARAM_STR);
    $stmt->bindParam(':check_out_datetime', $check_out_datetime, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode(array("result" => "1"));
}

if ($_POST["action"] === 'DELETE_VISITOR') {
    $id = $_POST["id"];

    $sql_delete = "DELETE FROM ims_visitor_contact WHERE id = :id";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(array("result" => "1"));
}

if ($_POST["action"] === 'GET_ACTIVE_VISITORS') {
    $sql_visitors = "SELECT * FROM ims_visitor_contact WHERE check_in_status = 'Y' ORDER BY check_in_datetime DESC";
    $stmt = $conn->prepare($sql_visitors);
    $stmt->execute();
    $visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($visitors);
}

if ($_POST["action"] === 'GET_VISITOR_LOGS') {
    $search = isset($_POST["search"]) ? $_POST["search"] : '';
    $status = isset($_POST["status"]) ? $_POST["status"] : 'all';
    $date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : '';
    $date_to = isset($_POST["date_to"]) ? $_POST["date_to"] : '';
    $visitor_type = isset($_POST["visitor_type"]) ? $_POST["visitor_type"] : '';

    $sql = "SELECT * FROM ims_visitor_contact WHERE 1=1";
    $params = array();

    if ($search !== '') {
        $sql .= " AND (visitor_name LIKE :search 
                   OR phone_number LIKE :search 
                   OR license_plate LIKE :search 
                   OR card_no LIKE :search 
                   OR house_number LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    if ($status === 'inside') {
        $sql .= " AND check_in_status = 'Y'";
    } elseif ($status === 'left') {
        $sql .= " AND check_in_status = 'N' AND check_out_datetime IS NOT NULL";
    }

    if ($date_from !== '') {
        $sql .= " AND check_in_datetime >= :date_from";
        $params[':date_from'] = $date_from . ' 00:00:00';
    }

    if ($date_to !== '') {
        $sql .= " AND check_in_datetime <= :date_to";
        $params[':date_to'] = $date_to . ' 23:59:59';
    }

    if ($visitor_type !== '') {
        $sql .= " AND visitor_type = :visitor_type";
        $params[':visitor_type'] = $visitor_type;
    }

    $sql .= " ORDER BY id DESC LIMIT 500";

    $stmt = $conn->prepare($sql);
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val, PDO::PARAM_STR);
    }
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($logs);
}

$conn = null;
?>