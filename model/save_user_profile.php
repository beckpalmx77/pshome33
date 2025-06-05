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

    $myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $pictureUrl  . " | " . $f_name . " | " . $l_name
    . " | " . $line_phone . " | " . $detail . " | " . $displayName . " | " . $userId );
    fclose($myfile);

    if (!empty($userId)) {
        // UPDATE หรือ INSERT ถ้าไม่มี record
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ims_house_line_user WHERE line_user_id = :userId");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $exists = $stmt->fetchColumn();

        if ($exists) {
            $sql = "UPDATE ims_house_line_user SET line_user_name = :displayName, line_picture_profile = :pictureUrl
                    ,f_name = :f_name ,l_name = :l_name     
                    WHERE line_user_id = :userId";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':displayName', $displayName);
        $stmt->bindParam(':pictureUrl', $pictureUrl);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        if ($exists) {
            $sql = "UPDATE ims_user SET first_name = :f_name ,last_name = :l_name     
                    WHERE user_id = :line_phone";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':f_name', $f_name);
        $stmt->bindParam(':l_name', $l_name);
        $stmt->bindParam(':line_phone', $line_phone);
        $stmt->execute();

        if ($exists) {
            $sql = "UPDATE ims_house_payment SET line_picture_profile_show = :pictureUrl , detail = :detail WHERE line_user_id = :userId";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':detail', $detail);
        $stmt->bindParam(':pictureUrl', $pictureUrl);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        echo 'success';

    } else {
        echo 'invalid';
    }
}

