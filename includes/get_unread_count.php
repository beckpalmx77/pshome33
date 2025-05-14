<?php
require '../config/connect_db.php'; // เชื่อมต่อฐานข้อมูล

$sql = "SELECT COUNT(*) FROM afront_contact WHERE status = 'N'";
$count = $conn->query($sql)->fetchColumn();

/*
$myfile = fopen("msg_check.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql . " | " . $count);
fclose($myfile);
*/

echo $count;