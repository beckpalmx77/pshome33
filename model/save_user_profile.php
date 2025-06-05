<?php
require_once '../config/connect_db.php'; // หรือ path ที่ถูกต้องของคุณ

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_POST['userId'] ?? '';
    $displayName = $_POST['displayName'] ?? '';
    $pictureUrl = $_POST['pictureUrl'] ?? '';
    $f_name = $_POST['f_name'] ?? '';
    $l_name = $_POST['l_name'] ?? '';
    $line_phone = $_POST['line_phone'] ?? '';
    $detail = $f_name . " " . $l_name;

    if (!empty($userId)) {
        // UPDATE หรือ INSERT ถ้าไม่มี record
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ims_house_line_user WHERE line_user_id = :userId");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $exists = $stmt->fetchColumn();


        $sql1 = "UPDATE ims_house_line_user SET line_user_name = :displayName, line_picture_profile = :pictureUrl
                    ,f_name = :f_name ,l_name = :l_name     
                    WHERE line_user_id = :userId";


        $stmt1 = $conn->prepare($sql1);
        $stmt1->bindParam(':displayName', $displayName);
        $stmt1->bindParam(':pictureUrl', $pictureUrl);
        $stmt1->bindParam(':f_name', $f_name);
        $stmt1->bindParam(':l_name', $l_name);
        $stmt1->bindParam(':userId', $userId);
        $stmt1->execute();


        $sql2 = "UPDATE ims_user SET first_name = :f_name ,last_name = :l_name     
                    WHERE user_id = :line_phone";


        $stmt2 = $conn->prepare($sql2);
        $stmt2->bindParam(':f_name', $f_name);
        $stmt2->bindParam(':l_name', $l_name);
        $stmt2->bindParam(':line_phone', $line_phone);
        $stmt2->execute();


        //$sql3 = "UPDATE ims_house_payment SET line_picture_profile_show = :pictureUrl , detail = :detail WHERE line_user_id = :userId";

        //$stmt3 = $conn->prepare($sql3);
        //$stmt3->bindParam(':pictureUrl', $pictureUrl);
        //$stmt3->bindParam(':detail', $detail);
        //$stmt3->bindParam(':userId', $userId);
        //$stmt3->execute();


        echo 'success';

    } else {
        echo 'invalid';
    }
}

