<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $f_name = $_POST['f_name'] ?? '';
    $l_name = $_POST['l_name'] ?? '';
    $line_phone = $_POST['line_phone'] ?? '';
    $line_user_id = $_POST['line_user_id'] ?? '';
    $password = $_POST['password'] ?? '';
    $displayName = $_POST['displayName'] ?? '';
    $line_picture_profile_show = $_POST['profilePic'] ?? '';
    $pictureUrl = $_POST['pictureUrl'] ?? '';

    $detail = $f_name . " " . $l_name;
/*
    $myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $f_name  . " | " . $l_name . " | " . $line_phone
    . " | " . $line_user_id . " | " . $displayName . " | " . $pictureUrl);
    fclose($myfile);
*/
    // อัปเดตตาราง ims_house_line_user
    $sql1 = "UPDATE ims_house_line_user 
             SET f_name = :f_name, l_name = :l_name, line_phone = :line_phone
             WHERE line_user_id = :line_user_id";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bindParam(':f_name', $f_name, PDO::PARAM_STR);
    $stmt1->bindParam(':l_name', $l_name, PDO::PARAM_STR);
    $stmt1->bindParam(':line_phone', $line_phone, PDO::PARAM_STR);
    $stmt1->bindParam(':line_user_id', $line_user_id, PDO::PARAM_STR);

    if ($stmt1->execute()) {
        // อัปเดตตาราง ims_user ถ้ามี line_phone ตรงกับ user_id
        $sql2 = "UPDATE ims_user 
                 SET first_name = :f_name, last_name = :l_name
                 WHERE user_id = :line_phone";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bindParam(':f_name', $f_name, PDO::PARAM_STR);
        $stmt2->bindParam(':l_name', $l_name, PDO::PARAM_STR);
        $stmt2->bindParam(':line_phone', $line_phone, PDO::PARAM_STR);

        $stmt2->execute(); // ไม่จำเป็นต้องเช็คผลลัพธ์ หากไม่ critical

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE ims_user SET password = :password WHERE user_id = :line_phone";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':line_phone', $line_phone);
            $stmt->execute();
        }

/*
        $sql3 = "UPDATE ims_house_payment 
             SET detail = :detail , line_picture_profile_show = :line_picture_profile_show
             WHERE line_user_id = :line_user_id";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bindParam(':detail', $detail, PDO::PARAM_STR);
        $stmt3->bindParam(':line_picture_profile_show', $pictureUrl, PDO::PARAM_STR);
        $stmt3->bindParam(':line_user_id', $line_user_id, PDO::PARAM_STR);
        $stmt3->execute();
*/


        echo 1;
    } else {
        echo 0;
    }
}
