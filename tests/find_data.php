<?php
session_start();
error_reporting(0);

$host = "localhost"; /* Host name */
$user = "sadmin"; /* User */
$password = "sadmin"; /* Password */
$dbname = "myadmin_dbs"; /* Database name */

$con = mysqli_connect($host, $user, $password,$dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if(!isset($_POST['searchTerm'])){
    $fetchData = mysqli_query($con,"select * from ims_unit order by id limit 5");
}else{
    $search = $_POST['searchTerm'];
    $fetchData = mysqli_query($con,"select * from ims_unit where unit_name like '%".$search."%' limit 5");
}

$data = array();
while ($row = mysqli_fetch_array($fetchData)) {
    $data[] = array("id"=>$row['id'], "text"=>$row['unit_name']);
}

echo json_encode($data);


