<?php
header('Content-Type: application/json');

include("../config/connect_db.php");

$doc_date = str_replace("-","/",$_POST["doc_date"]);
$branch = $_POST["branch"];

$sql_get = "
 SELECT BRANCH,PGROUP,ims_pgroup.pgroup_name,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as  TRD_G_KEYIN
 FROM ims_product_sale_cockpit 
 LEFT JOIN ims_pgroup
 ON ims_pgroup.pgroup_id = ims_product_sale_cockpit.pgroup
 WHERE DI_DATE = '" . $doc_date . "' AND BRANCH = '" . $branch . "' AND TRD_G_KEYIN > 0  
 GROUP BY  BRANCH,PGROUP,pgroup_name 
 ORDER BY PGROUP ";

//$myfile = fopen("sql_get.txt", "w") or die("Unable to open file!");
//fwrite($myfile, "[" . $sql_get . " / " . $doc_date);
//fclose($myfile);

$return_arr = array();

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
    $return_arr[] = array("pgroup_name" => $result['pgroup_name'],
        "TRD_G_KEYIN" => $result['TRD_G_KEYIN']);
}

//$myfile = fopen("qry_file_pie.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_get);
//fclose($myfile);

echo json_encode($return_arr);

