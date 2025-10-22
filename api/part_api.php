<?php
include('../config/connect_pg_db.php');
include('../config/lang.php');


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
        $sql_get = "SELECT * FROM vm_part WHERE runno = " . $id;
    } else {
        $sql_get = "SELECT * FROM vm_part ";
    }

    $return_arr = array();

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['runno'],
            "partid" => $result['part_id'],
            "partname" => $result['part_name'],
            "unitid" => $result['unit_id'],
            "unitname" => $result['unit_name'],
            "status" => $result['delete_flag']);
    }

    $part = json_encode($return_arr);
    file_put_contents("part.json", $part);
    echo json_encode($return_arr);

}