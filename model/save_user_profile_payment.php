<?php
require_once '../config/connect_db.php'; // หรือ path ที่ถูกต้องของคุณ

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_POST['userId'] ?? '';
    $displayName = $_POST['displayName'] ?? '';
    $pictureUrl = $_POST['pictureUrl'] ?? '';
    $line_phone = $_POST['line_phone'] ?? '';
    $line_email = "-";

/*
    $myfile = fopen("a-profile.txt", "w") or die("Unable to open file!");
    fwrite($myfile, " Row Record = " . $userId . " Display Name = " . $displayName . " pictureUrl = " . $pictureUrl . " line_phone = " . $line_phone);
    fclose($myfile);
*/

    if (!empty($userId)) {
        // UPDATE หรือ INSERT ถ้าไม่มี record
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ims_house_line_user WHERE line_user_id = :userId");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $exists = $stmt->fetchColumn();

        if ($exists) {
            $sql = "UPDATE ims_house_line_user SET                    
                    line_user_name = :displayName, line_picture_profile = :pictureUrl                    
                    WHERE line_user_id = :userId";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':displayName', $displayName);
            $stmt->bindParam(':pictureUrl', $pictureUrl);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
        }

        echo 'success';

    } else {
        echo 'invalid';
    }
}

