<?php
date_default_timezone_set("Asia/Bangkok");
include('db_value.inc');

try
{
    $buffered_attr = defined('Pdo\Mysql::ATTR_USE_BUFFERED_QUERY') ? \Pdo\Mysql::ATTR_USE_BUFFERED_QUERY : PDO::MYSQL_ATTR_USE_BUFFERED_QUERY;

    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=" .DB_PORT,DB_USER, DB_PASS
        ,array(
            $buffered_attr => true
        ));
    $conn->exec("SET NAMES 'utf8mb4'");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
    echo "Error: " . $e->getMessage();
    exit("Error: " . $e->getMessage());
}