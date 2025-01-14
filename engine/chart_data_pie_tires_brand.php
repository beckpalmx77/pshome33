<?php
header('Content-Type: application/json');

include("../config/connect_db.php");

$year = date("Y");

$doc_date = str_replace("-", "/", $_POST["doc_date"]);
$branch = $_POST["branch"];
//WHERE SKU_CAT IN ('2SAC01','2SAC02','2SAC03','2SAC02','2SAC04','2SAC05','2SAC06','2SAC07','2SAC08','2SAC09','2SAC10','2SAC11','2SAC12','2SAC13','2SAC14','2SAC15')
$sql_get = "
 SELECT BRN_CODE,BRN_NAME,SKU_CAT,ICCAT_NAME,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as  TRD_QTY
 ,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN 
 FROM ims_product_sale_cockpit 
 WHERE PGROUP IN ('P1')
 AND DI_YEAR = '" . $year . "'
 GROUP BY BRN_CODE,BRN_NAME,SKU_CAT,ICCAT_NAME
 ORDER BY SKU_CAT 
 ";

//$myfile = fopen("sql_get.txt", "w") or die("Unable to open file!");
//fwrite($myfile,  $sql_get);
//fclose($myfile);

$return_arr = array();

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
    $return_arr[] = array("BRN_CODE" => $result['BRN_CODE'],
        "BRN_NAME" => $result['BRN_NAME'],
        "TRD_QTY" => $result['TRD_QTY'],
        "TRD_G_KEYIN" => $result['TRD_G_KEYIN']);
}

//$myfile = fopen("qry_file_pie.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_get);
//fclose($myfile);

echo json_encode($return_arr);

