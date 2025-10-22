<?php
include "../config/connect_db.php";
// REGISTER USER

$name = $_POST['name'];
$password = $_POST['password'];



$var = $name . " | " . $password  ;

$myfile = fopen("myqeury_1.txt", "w") or die("Unable to open file!");
fwrite($myfile, $var);
fclose($myfile);


