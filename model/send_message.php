<?php
include("../config/connect_db.php");
include("../api/line_api.php");

if ($_POST["action"] === 'SEND_CONTACT') {
    $f_name = $_POST["f_name"];
    $l_name = $_POST["l_name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $time_contact = $_POST["time"];

    $date = new DateTime();
    $date->setTimestamp(time());
    $timestamp = $date->format(DateTime::RFC1123);

    //echo $date->format(DateTime::RFC1123) . "\n";

    $str = "\r\n" . " Ref : " . $f_name . "\r\n ผู้โอนเงิน : " . $l_name . "\r\n" . " ยอดโอนเงิน : " . $email . "\r\n"
        . $timestamp;

    //$str = "\r\n" . " ผู้ติดต่อ : " . $f_name . " - " . $l_name . "\r\n" . " อีเมล์ " . $email . "\r\n"
    //. " โทรศัพท์ " . $phone . "\r\n" . " เวลาที่สะดวก " . $time_contact . "\r\n" . " ส่งข้อความนี้เมื่อ " . $timestamp;

    $sql = "INSERT INTO afront_contact(f_name,l_name,email,phone,time_contact,create_date,update_date) 
            VALUES (:f_name,:l_name,:email,:phone,:time_contact,:create_date,:update_date)";
    $query = $conn->prepare($sql);
    $query->bindParam(':f_name', $f_name, PDO::PARAM_STR);
    $query->bindParam(':l_name', $l_name, PDO::PARAM_STR);
    $query->bindParam(':phone', $phone, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':time_contact', $time_contact, PDO::PARAM_STR);
    $query->bindParam(':create_date', $timestamp, PDO::PARAM_STR);
    $query->bindParam(':update_date', $timestamp, PDO::PARAM_STR);
    $query->execute();
    $lastInsertId = $conn->lastInsertId();

    if ($lastInsertId) {

        $res = notify_message($str, $token);

        echo 1;
    } else {
        echo 0;
    }

}







