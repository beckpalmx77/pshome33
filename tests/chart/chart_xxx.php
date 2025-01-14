<?php
//setting header to json
header('Content-Type: application/json');

//database
define('DB_HOST', 'localhost:3307');
define('DB_USERNAME', 'myadmin');
define('DB_PASSWORD', 'myadmin');
define('DB_NAME', 'charty');

//get connection
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if(!$mysqli){
    die("Connection failed: " . $mysqli->error);
}

//query to get data from the table
$query = sprintf("SELECT userid, facebook, twitter, googleplus,line FROM followers");

//execute query
$result = $mysqli->query($query);

//loop through the returned data
$data = array();
foreach ($result as $row) {
    $data[] = $row;
}

//free memory associated with result
$result->close();

//close connection
$mysqli->close();

//now print the data
print json_encode($data);