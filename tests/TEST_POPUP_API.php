<?php

if ($_POST["action"] === 'SEND') {


    $txt = $_POST["email_address"];
    $my_file = fopen("A_SEND.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);

    echo "บันทึกข้อมูลสำเร็จ";

}
