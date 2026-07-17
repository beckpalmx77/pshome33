<?php
$file = 'db/house_dbs.sql';
if (file_exists($file)) {
    $content = file_get_contents($file);

    // 1. Check if S612 insert is already in the file
    if (strpos($content, 'report_house_payment_split_monthly_2') === false) {
        $target = "INSERT INTO `menu_sub` VALUES (92, 'S312', 'M007', 'ตรวจสอบการ เข้า-ออก (Visitor)', 'ตรวจสอบการ เข้า-ออก (Visitor)', 'manage_visitor_logs', '_self', 'fa fa-clock-o', 80, 'User', '_self');";
        $insert = "INSERT INTO `menu_sub` VALUES (92, 'S312', 'M007', 'ตรวจสอบการ เข้า-ออก (Visitor)', 'ตรวจสอบการ เข้า-ออก (Visitor)', 'manage_visitor_logs', '_self', 'fa fa-clock-o', 80, 'User', '_self');\nINSERT INTO `menu_sub` VALUES (93, 'S612', 'M006', 'สรุปจำนวนบ้านที่ชำระค่าส่วนกลาง (ตามปี)', 'สรุปจำนวนบ้านที่ชำระค่าส่วนกลาง (ตามปี)', 'report_house_payment_split_monthly_2', '_self', 'fa fa-home', 58, 'User', '_self');";
        
        $pos = strpos($content, $target);
        if ($pos !== false) {
            $content = str_replace($target, $insert, $content);
            echo "Added S612 to menu_sub in SQL dump.\n";
        } else {
            // Try without backticks around menu_sub
            $target = "INSERT INTO menu_sub VALUES (92, 'S312', 'M007', 'ตรวจสอบการ เข้า-ออก (Visitor)', 'ตรวจสอบการ เข้า-ออก (Visitor)', 'manage_visitor_logs', '_self', 'fa fa-clock-o', 80, 'User', '_self');";
            $insert = "INSERT INTO menu_sub VALUES (92, 'S312', 'M007', 'ตรวจสอบการ เข้า-ออก (Visitor)', 'ตรวจสอบการ เข้า-ออก (Visitor)', 'manage_visitor_logs', '_self', 'fa fa-clock-o', 80, 'User', '_self');\nINSERT INTO menu_sub VALUES (93, 'S612', 'M006', 'สรุปจำนวนบ้านที่ชำระค่าส่วนกลาง (ตามปี)', 'สรุปจำนวนบ้านที่ชำระค่าส่วนกลาง (ตามปี)', 'report_house_payment_split_monthly_2', '_self', 'fa fa-home', 58, 'User', '_self');";
            $pos = strpos($content, $target);
            if ($pos !== false) {
                $content = str_replace($target, $insert, $content);
                echo "Added S612 to menu_sub in SQL dump (no backticks version).\n";
            } else {
                echo "Warning: target for S312 insert not found. Could not insert S612 menu.\n";
            }
        }
    } else {
        echo "S612 already exists in menu_sub section of SQL dump.\n";
    }

    // 2. Add S612 to ims_permission rows in SQL dump
    // Let's find rows with S610 and replace S610 with S610,S612
    $content = str_replace("S610,", "S610,S612,", $content);
    echo "Updated ims_permission strings in SQL dump.\n";

    file_put_contents($file, $content);
    echo "Saved changes to $file.\n";
} else {
    echo "File $file not found.\n";
}
?>
