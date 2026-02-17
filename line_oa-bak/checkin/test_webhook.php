<?php
$ip = $_SERVER['REMOTE_ADDR'];
file_put_contents("ip_log.txt", $ip . PHP_EOL, FILE_APPEND);
echo "Your IP: " . $ip;