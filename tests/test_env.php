<?php

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$my_env = $_ENV['STICK'];
$my_env2 = $_ENV['ATTAA'];


echo $my_env . " | " . $my_env2;