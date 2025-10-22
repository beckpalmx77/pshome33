<?php

include('db_value_sqlserver.inc');

try {
    // If you change db server system, change this too!
    //$conn = new PDO("pgsql:host=$host port=5432 dbname=$dbname", $dbuser, $dbpass);
    $conn_sqlsvr = new PDO("sqlsrv:server=$host ; Database = $dbname", $dbuser, $dbpass);
    $conn_sqlsvr->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo $e->getMessage();
}
