<?php
header('Content-Type: application/json');

include("../config/connect_db.php");

$year = $_POST["year"];
$p_group = $_POST["p_group"];

//$myfile = fopen("param-brn.txt", "w") or die("Unable to open file!");
//fwrite($myfile, "Year = " . $year . " | " . $p_group );
//fclose($myfile);

$sql_get = "
 SELECT BRN_NAME
 ,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
 FROM ims_product_sale_cockpit 
 WHERE  PGROUP = '" . $p_group . "'  
 AND DI_YEAR = '" . $year . "'   
 GROUP BY BRN_NAME
 ORDER BY sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) DESC 
";

//$myfile = fopen("param-brn2.txt", "w") or die("Unable to open file!");
//fwrite($myfile, "sql_get = " . $sql_get);
//fclose($myfile);

$return_arr = array();

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
  $return_arr[] = array("BRN_NAME" => $result['BRN_NAME'],
      "TRD_G_KEYIN" => $result['TRD_G_KEYIN']);
}

//$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_get);
//fclose($myfile);

echo json_encode($return_arr);

