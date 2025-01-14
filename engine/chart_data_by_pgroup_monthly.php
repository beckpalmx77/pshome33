<?php
header('Content-Type: application/json');

include("../config/connect_db.php");

$month = $_POST["month"];
$year = $_POST["year"];
$branch = $_POST["branch"];
$PGROUP = $_POST["PGROUP"];

//$myfile = fopen("param-1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $month  . "| Year = " . $year . "| Branch" . $branch );
//fclose($myfile);

$sql_get = " SELECT PGROUP,DI_MONTH,DI_MONTH_NAME,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as  TRD_G_KEYIN
 FROM ims_product_sale_cockpit 
 WHERE DI_YEAR = '" . $year . "'   
 AND BRANCH like '%" . $branch . "'
 AND PGROUP like '%" . $PGROUP . "'
 GROUP BY  PGROUP,DI_MONTH,DI_MONTH_NAME 
 ORDER BY DI_MONTH
";

$return_arr = array();

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
  $return_arr[] = array("DI_MONTH_NAME" => $result['DI_MONTH_NAME'],
      "TRD_G_KEYIN" => $result['TRD_G_KEYIN']);
}

//$myfile = fopen("bar-qry_file1" . $PGROUP . ".txt", "w") or die("Unable to open file!");
//fwrite($myfile, $total);
//fclose($myfile);

echo json_encode($return_arr);

