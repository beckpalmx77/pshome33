<?php

include("../config/connect_db.php");

$str_file = "";

$sql_get = " SELECT PGROUP,BRN_CODE,BRN_NAME,DI_MONTH,DI_YEAR
,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY
,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
FROM ims_product_sale_cockpit
WHERE PGROUP = 'P1' AND DI_YEAR = '2022'
GROUP BY PGROUP,BRN_CODE,BRN_NAME,DI_MONTH,DI_YEAR
ORDER BY PGROUP,BRN_CODE,DI_MONTH,DI_YEAR ";

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {

    //echo $result['BRN_NAME'] . " | " . $result['DI_MONTH'] . " | " . $result['DI_YEAR'] . " | " . $result['TRD_G_KEYIN'] . "<br>";

    $F_TRD_QTY = "";
    $F_TRD_G_KEYIN = "";

    switch ($result['DI_MONTH']) {
        case "1":
            $F_TRD_QTY = "TRD_QTY_M1";
            $F_TRD_G_KEYIN = "TRD_G_KEYIN_M1";
            break;
        case "2":
            $F_TRD_QTY = "TRD_QTY_M2";
            $F_TRD_G_KEYIN = "TRD_G_KEYIN_M2";
            break;
        case "3":
            $F_TRD_QTY = "TRD_QTY_M3";
            $F_TRD_G_KEYIN = "TRD_G_KEYIN_M3";
            break;
        case "4":
            $F_TRD_QTY = "TRD_QTY_M4";
            $F_TRD_G_KEYIN = "TRD_G_KEYIN_M4";
            break;
        case "5":
            $F_TRD_QTY = "TRD_QTY_M5";
            $F_TRD_G_KEYIN = "TRD_G_KEYIN_M5";
            break;
        case "6":
            $F_TRD_QTY = "TRD_QTY_M6";
            $F_TRD_G_KEYIN = "TRD_G_KEYIN_M6";
            break;
        case "7":
            $F_TRD_QTY = "TRD_QTY_M7";
            $F_TRD_G_KEYIN = "TRD_G_KEYIN_M7";
            break;
        case "8":
            $F_TRD_QTY = "TRD_QTY_M8";
            $F_TRD_G_KEYIN = "TRD_G_KEYIN_M8";
            break;
        case "9":
            $F_TRD_QTY = "TRD_QTY_M9";
            $F_TRD_G_KEYIN = "TRD_G_KEYIN_M9";
            break;
        case "10":
            $F_TRD_QTY = "TRD_QTY_M10";
            $F_TRD_G_KEYIN = "TRD_G_KEYIN_M10";
            break;
        case "11":
            $F_TRD_QTY = "TRD_QTY_M11";
            $F_TRD_G_KEYIN = "TRD_G_KEYIN_M11";
            break;
        case "12":
            $F_TRD_QTY = "TRD_QTY_M12";
            $F_TRD_G_KEYIN = "TRD_G_KEYIN_M12";
            break;
    }

    $TRD_QTY = $result['TRD_QTY'];
    $TRD_G_KEYIN = $result['TRD_G_KEYIN'];

    $str_file .= $result['DI_MONTH'] . " | F_TRD_QTY  = " . $F_TRD_QTY . "| F_TRD_G_KEYIN = " . $F_TRD_G_KEYIN
        . " | TRD_QTY  = " . $result['TRD_QTY'] . "| TRD_G_KEYIN = " . $result['TRD_G_KEYIN']
        . "\n\r";

    $myfile = fopen("param-1.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $str_file);
    fclose($myfile);


    $sql_find = " SELECT * 
FROM ims_product_brand_monthly 
WHERE PGROUP = '" . $result['PGROUP'] . "'
AND BRN_CODE = '" . $result['BRN_CODE'] . "'
AND BRN_NAME = '" . $result['BRN_NAME'] . "'
AND DI_YEAR = '" . $result['DI_YEAR'] . "'
";
    echo $sql_find . "<br>";

    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows <= 0) {
        $sql_insert = "INSERT INTO ims_product_brand_monthly(" . $F_TRD_QTY . "," . $F_TRD_G_KEYIN . ",PGROUP,BRN_CODE,BRN_NAME,DI_YEAR) 
        VALUES (:TRD_QTY,:TRD_G_KEYIN,:PGROUP,:BRN_CODE,:BRN_NAME,:DI_YEAR)";
        $query_inset = $conn->prepare($sql_insert);
        $query_inset->bindParam(':TRD_QTY', $TRD_QTY, PDO::PARAM_STR);
        $query_inset->bindParam(':TRD_G_KEYIN', $TRD_G_KEYIN, PDO::PARAM_STR);
        $query_inset->bindParam(':PGROUP', $result['PGROUP'], PDO::PARAM_STR);
        $query_inset->bindParam(':BRN_CODE', $result['BRN_CODE'], PDO::PARAM_STR);
        $query_inset->bindParam(':BRN_NAME', $result['BRN_NAME'], PDO::PARAM_STR);
        $query_inset->bindParam(':DI_YEAR', $result['DI_YEAR'], PDO::PARAM_STR);
        //echo " sql_insert = " . $sql_insert . "<br>";
        $query_inset->execute();
    } else {
        $sql_update = "UPDATE ims_product_brand_monthly SET "
            . $F_TRD_QTY . "=:TRD_QTY, " . $F_TRD_QTY . "=:TRD_G_KEYIN             
            WHERE PGROUP = :PGROUP AND BRN_CODE = :BRN_CODE AND BRN_NAME = :BRN_NAME AND DI_YEAR = :DI_YEAR ";
        $query_update = $conn->prepare($sql_update);
        $query_update->bindParam(':TRD_QTY', $TRD_QTY, PDO::PARAM_STR);
        $query_update->bindParam(':TRD_G_KEYIN', $TRD_G_KEYIN, PDO::PARAM_STR);
        $query_update->bindParam(':PGROUP', $result['PGROUP'], PDO::PARAM_STR);
        $query_update->bindParam(':BRN_CODE', $result['BRN_CODE'], PDO::PARAM_STR);
        $query_update->bindParam(':BRN_NAME', $result['BRN_NAME'], PDO::PARAM_STR);
        $query_update->bindParam(':DI_YEAR', $result['DI_YEAR'], PDO::PARAM_STR);
        //echo " sql_update = " . $sql_update . "<br>";
        $query_update->execute();
    }

}

