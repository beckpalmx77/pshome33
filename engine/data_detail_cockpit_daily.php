<?php
header('Content-Type: application/json');

include("../config/connect_db.php");


$date = date("d/m/Y");

//$myfile = fopen("get_date.txt", "w") or die("Unable to open file!");
//fwrite($myfile,  $date);
//fclose($myfile);

 $sql_get = "SELECT BRANCH,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as  TRD_G_KEYIN
 FROM ims_product_sale_cockpit 
 WHERE DI_DATE = '" .$date . "'
 GROUP BY  BRANCH
 ORDER BY BRANCH";

//$myfile = fopen("sql_get.txt", "w") or die("Unable to open file!");
//fwrite($myfile,  $sql_get);
//fclose($myfile);

$data = array();

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    $data[] = array(
        "BRANCH" => $row['BRANCH'],
        "TRD_G_KEYIN" => $row['TRD_G_KEYIN']
    );
}

$response = array(
    "draw" => 1,
    "iTotalRecords" => 4,
    "iTotalDisplayRecords" => 4,
    "aaData" => $data
);

echo json_encode($response);

