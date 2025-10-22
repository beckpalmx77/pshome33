<?php

include('../config/connect_db.php');

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM ims_user WHERE user_id ='$username'";

$query = $conn->prepare($sql);
$query->bindParam(':username', $username, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);


if ($query->rowCount() == 1) {
    foreach ($results as $result) {
        if (password_verify($password, $result->password)) {
            $onLoginStatus = 'Success';
        } else {
            $onLoginStatus = 'Error';
        }
    }
} else {
    $onLoginStatus = 'Error';
}

/*
$myfile = fopen("pw-param.txt", "w") or die("Unable to open file!");
fwrite($myfile,  $onLoginStatus . " | " . $password . " | " . $username);
fclose($myfile);
*/

echo json_encode($onLoginStatus);



/*
$sql = "SELECT * FROM users WHERE username = '".$username."' AND password = '".$password."'";
$result = mysqli_query($db,$sql);
$count = mysqli_num_rows($result);

if($count == 1){
    echo json_encode("Success");
}
else{
    echo json_encode("Error");
}
*/