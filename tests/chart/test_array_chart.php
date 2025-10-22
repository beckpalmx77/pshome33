<?php

include('../../config/connect_db.php');

$data = array();

$year = '2021';
$BRANCH = 'CP-BB';

for ($loop = 0; $loop < 12; $loop++) {

    $month = $loop + 1;

    $sql_count = "SELECT DI_MONTH 
FROM ims_product_sale_cockpit 
WHERE PGROUP = 'P1' AND DI_MONTH = " . $month . "
AND BRANCH = '" . $BRANCH . "' 
AND DI_YEAR = '" . $year . "'";

    $nRows = $conn->query($sql_count)->fetchColumn();
    if ($nRows > 0) {

        $sql_get = "SELECT sum(CAST(TRD_QTY AS DECIMAL(10,2))) as  TRD_QTY,sum(CAST(  TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
            FROM ims_product_sale_cockpit 
            WHERE PGROUP = 'P1' AND DI_MONTH = " . $month . "
            AND DI_YEAR = '" . $year . "' 
            AND BRANCH = '" . $BRANCH . "' 
            GROUP BY DI_YEAR,DI_MONTH
            ORDER BY DI_YEAR, CAST(DI_MONTH AS UNSIGNED)";

        $statement = $conn->query($sql_get);
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {

            $data[] = $result['TRD_G_KEYIN'] . ",";

        }

    } else {

            $data[] = "0,";

    }

    $product_summary_array = json_encode($data);
    file_put_contents("product_summary_array.json", $data);
    echo json_encode($data);

}