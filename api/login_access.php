<?php
include('../config/connect_db.php');


// Getting the received JSON into $json variable.
$json = file_get_contents('php://input');

// Decoding the received JSON and store into $obj variable.
$obj = json_decode($json, true);

// Getting User email from JSON $obj array and store into $email.
//$username = $obj['email'];

// Getting Password from JSON $obj array and store into $password.
//$password = $obj['password'];

$username = 'admin@myadmin.com';
$password = 'admin';

$sql = "SELECT * FROM ims_user  
        WHERE email=:username ";

$query = $conn->prepare($sql);
$query->bindParam(':username', $username, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() == 1) {
    foreach ($results as $result) {
        if (password_verify($password, $result->password)) {
            $onLoginStatus = 'Login Matched';
        } else {
            $onLoginStatus = 'Invalid Username or Password Please Try Again';
        }
    }
} else {
    $onLoginStatus = 'Invalid Username or Password Please Try Again';
}


$LoginMSG = json_encode($onLoginStatus);
echo $LoginMSG;

?>