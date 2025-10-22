<?php
header('Content-Type: application/json');

include("../config/connect_db.php");


$month = $_POST["month"];
$year = $_POST["year"];
$branch = $_POST["branch"];


$sql_get = " SELECT SKU_CAT,ICCAT_NAME,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as  TRD_QTY,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN 
 FROM ims_product_sale_cockpit
 WHERE PGROUP = 'P2'
 AND DI_YEAR = '" . $year . "'
 AND DI_MONTH = '" . $month . "'
 AND BRANCH = '" . $branch . "'
 GROUP BY SKU_CAT,ICCAT_NAME
 ORDER BY SKU_CAT ";

//$myfile = fopen("sql_get-1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, "[" . $sql_get . " / " . $month);
//fclose($myfile);

$return_arr = array();

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
    $return_arr[] = array("ICCAT_NAME" => $result['ICCAT_NAME'],
        "TRD_G_KEYIN" => $result['TRD_G_KEYIN']);
}

//$myfile = fopen("qry_file_pie.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_get);
//fclose($myfile);

echo json_encode($return_arr);

