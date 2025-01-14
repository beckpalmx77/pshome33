<?php

define('DB_HOST','192.168.88.7');
define('DB_PORT','3307');
define('DB_USER','myadmin');
define('DB_PASS','myadmin');
define('DB_NAME','sac_emp');

try
{
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=" .DB_PORT,DB_USER, DB_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));

    if($conn) {
        echo "Connect DB";
    }


}
catch (PDOException $e)
{
    echo "Error: " . $e->getMessage();
    exit("Error: " . $e->getMessage());
}
