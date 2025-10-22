<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once "../vendor/autoload.php";

//PHPMailer Object
$mail = new PHPMailer(true); //Argument true in constructor enables exceptions

//From email address and name
$mail->From = "beckpalmx@gmail.com";
$mail->FromName = "beckpalmx";

//To address and name
$mail->addAddress("beckpalmx@hotmail.com", "Recepient Name");
$mail->addAddress("pongsak_p@hotmail.com"); //Recipient name is optional

//Address to which recipient will reply
$mail->addReplyTo("pongsak_p@hotmail.com", "Reply");

//CC and BCC
$mail->addCC("beckpalmx@hotmail.com");
$mail->addBCC("pongsak_p@hotmail.com");

//Send HTML or Plain Text email
$mail->isHTML(true);

$mail->IsSMTP();
$mail->Host = "smtp.gmail.com";
$mail->Port = "587";

// optional
// used only when SMTP requires authentication
$mail->SMTPAuth = true;
$mail->Username = 'information.operate@gmail.com';
$mail->Password = '@qpzmwoxn';

$mail->Subject = "Subject Text";
$mail->Body = "<i>Mail body in HTML</i>";
$mail->AltBody = "This is the plain text version of the email content";

try {
    $mail->send();
    echo "Message has been sent successfully";
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}