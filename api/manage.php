<?php

include('../config/connect_db.php');

//$username = $_POST['username'];
//$password = $_POST['password'];
//$fullname = $_POST['fullname'];

$username = "admin@myadmin.com";
$password = "admin";
$fullname = "admin@myadmin.com";


$sql = "SELECT * FROM ims_user WHERE user_id ='$username'";

$query = $conn->prepare($sql);
$query->bindParam(':username', $username, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);


if ($query->rowCount() == 1) {
    foreach ($results as $result) {
        if (password_verify($password, $result->password)) {

                //$return_arr[] = array("id" => $result['id'],
                //"first_name" => $result['first_name'],
                //"last_name" => $result['last_name'],
                //"emp_id" => $result['emp_id']);

            $onLoginStatus = 'Login Success';

        } else {
            $onLoginStatus = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง ลองใหม่อีกครั้ง';
            $onLoginStatus = 'Can not login';
        }
    }
} else {
    $onLoginStatus = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง ลองใหม่อีกครั้ง';
    $onLoginStatus = 'Can not login';
}


echo json_encode($onLoginStatus);
