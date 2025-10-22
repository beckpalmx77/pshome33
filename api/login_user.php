<?php

//Define your Server host name here.
$HostName = "localhost";

//Define your MySQL Database Name here.
$DatabaseName = "sac_emp";

//Define your Database User Name here.
$HostUser = "myadmin";

//Define your Database Password here.
$HostPass = "myadmin";

// Creating MySQL Connection.
$con = mysqli_connect($HostName,$HostUser,$HostPass,$DatabaseName);

// Getting the received JSON into $json variable.
$json = file_get_contents('php://input');

// Decoding the received JSON and store into $obj variable.
$obj = json_decode($json,true);

// Getting User user from JSON $obj array and store into $user.
$user = $obj['user'];

// Getting Password from JSON $obj array and store into $password.

$password = $obj['password'];


$myfile = fopen("pw-param.txt", "w") or die("Unable to open file!");
fwrite($myfile,  $user. " | " . $password);
fclose($myfile);

//Applying User Login query with user and password.

$password = password_hash($obj['password'], PASSWORD_DEFAULT);

$loginQuery = "select * from ims_user where user = '$user' and password = '$password' ";

// Executing SQL Query.
$check = mysqli_fetch_array(mysqli_query($con,$loginQuery));

if(isset($check)){

    // Successfully Login Message.
    $onLoginSuccess = 'Login Matched';

    // Converting the message into JSON format.
    $SuccessMSG = json_encode($onLoginSuccess);

    // Echo the message.
    echo $SuccessMSG ;

}

else{

    // If user and Password did not Matched.
    $InvalidMSG = 'Invalid Username or Password Please Try Again' ;

    // Converting the message into JSON format.
    $InvalidMSGJSon = json_encode($InvalidMSG);

    // Echo the message.
    echo $InvalidMSGJSon ;

}

mysqli_close($con);
?>
