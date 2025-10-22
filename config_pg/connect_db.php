<?php
date_default_timezone_set("Asia/Bangkok");
include ('db_pg_value.inc');

//กำหนดค่า Access-Control-Allow-Origin ให้ เครื่อง อื่น ๆ สามารถเรียกใช้งานหน้านี้ได้

header("Access-Control-Allow-Origin: *");

header("Content-Type: application/json; charset=UTF-8");

header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");

header("Access-Control-Max-Age: 84000");

header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
    // If you change db server system, change this too!
    $conn = new PDO("pgsql:host=$host port=5432 dbname=$dbname", $dbuser, $dbpass);
    
} catch (PDOException $e) {
    echo $e->getMessage();
}



