<?php

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';

function sendMail($userEmail,$subject,$body){
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'quiztechjj@gmail.com';
    $mail->Password   = '123qwejojO';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    //Recipients
    $mail->setFrom('QuizTech@gmail.com', 'Quiz Tech');//Add a recipient
    $mail->addAddress($userEmail);

    //Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();
}