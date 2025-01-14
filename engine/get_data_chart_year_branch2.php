<?php
include("config/connect_db.php");

$year_start = date("Y");
$year = '';
$label1 = '' ;
$label2 = '' ;
$label3 = '' ;
$label4 = '' ;
$data1 = '' ;
$data2 = '' ;
$data3 = '' ;
$data4 = '' ;
$i = 1 ;

$BRANCH = $_POST["branch"];
//$BRANCH = "CP-BB";

//echo $BRANCH;

for ($x = 0; $x <= 1; $x++) {

    $year = $year_start - $x;

    //echo $year;

    $str_return = "[";

    $sql_get = " SELECT DI_YEAR,DI_MONTH,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as  TRD_G_KEYIN
 FROM ims_product_sale_cockpit 
 WHERE PGROUP = '" . $product_group . "' AND DI_YEAR = '" . $year . "' AND BRANCH = '" . $BRANCH . "' 
 GROUP BY DI_MONTH,DI_YEAR 
 ORDER BY CAST(DI_MONTH AS UNSIGNED) ";

    //echo $sql_get;

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);


    foreach ($results as $result) {
        if ($result['DI_MONTH'] == 12) {
            $str_return .= $result['TRD_G_KEYIN'];
        } else {
            $str_return .= $result['TRD_G_KEYIN'] . ",";
        }
    }

    $str_return .= "]";

    //echo $str_return;


    switch ($x) {
        case 0:
            $label1=$year;
            $data1=$str_return;
            break;
        case 1:
            $label2=$year;
            $data2=$str_return;
            break;
    }

    $i++;

}
