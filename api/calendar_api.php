<?php
include('../config/connect_db.php');
include('../config/lang.php');
include('../util/reorder_record.php');

//กำหนดค่า Access-Control-Allow-Origin ให้ เครื่อง อื่น ๆ สามารถเรียกใช้งานหน้านี้ได้
header("Access-Control-Allow-Origin: *");

header("Content-Type: application/json; charset=UTF-8");

header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");

header("Access-Control-Max-Age: 3600");

header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$requestMethod = $_SERVER["REQUEST_METHOD"];

//ตรวจสอบหากใช้ Method GET

if ($requestMethod == 'GET') {

    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = $_GET['id'];
        $sql_get = "SELECT * FROM ims_event WHERE id = " . $id;
    } else {
        $sql_get = "SELECT * FROM ims_event ";
    }

    $return_arr = array();

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "title" => $result['title'],
            "date" => $result['start_event']);
    }

    $event = json_encode($return_arr);
    file_put_contents("event.json", $event);
    echo json_encode($return_arr);

}