<?php
$string = "ทดสอบ";

$string_e = base64_encode($string);

echo $string;
echo "<br>";
echo $string_e;
echo "<br>";
echo base64_decode($string_e);

echo "<br>";
$url_encode = urlencode($string);
echo "url_encode = " . $url_encode;
echo "<br>";
echo "url_decode = " . urldecode($url_encode);

echo "<br>";

$password = "admin";
$password1 = "admin1";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "<br>";
echo "hashed_password = " .$hashed_password;

echo "<br>";
if(password_verify($password1, $hashed_password)) {
    echo "<br>";
    echo $password . " | hashed_password = " .$hashed_password;
} else {
    echo "<br>";
    echo "Not Equals hashed_password = " .$hashed_password;
}


