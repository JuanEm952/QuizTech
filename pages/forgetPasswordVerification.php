<?php
include('serverConnection.php');
include('../phpmailer/sendMail.php');
    session_start();
    $verificationFlag = 0;
    // Send the user to the forget the password page
    if(!$_SESSION['user_email_forget_password']){
        header("Location:sendForgetPasswordCode.php");
    }
    //Send verification code again.
    if(isset($_GET['sendVerificatoinCodeAgain'])){
        $userEmail = $_SESSION['user_email_forget_password'];
        $code = getVerificationCode($userEmail);
        $body = "Your verification Code is <b>$code</b> fill it in the verification code field box in order to reset your password!";
        sendMail($userEmail,"Forget Password",$body);
    }
    // Check if the user inserted the correct verification code
    if($_SESSION['user_email_forget_password'] && (!isset($_POST['verifyCode']))){
        $userEmail = $_SESSION['user_email_forget_password'];
        $code = getVerificationCode($userEmail);
        $body = "Your verification Code is <b>$code</b> fill it in the verification code field box in order to reset your password!";
        sendMail($userEmail,"Forget Password",$body);
    }

    // checks if the user successfully wrote the verification code
    if(isset($_POST['verifyCode']) ){
        $userCode = $_POST['verifyCode'];
        $userEmail = $_SESSION['user_email_forget_password'];
        $code = getVerificationCode($userEmail);
        if($code == $userCode){
            $conn = connection();
            $sql = "UPDATE `User` SET `forgot_password_code`=0 WHERE email='$userEmail'";
            $retval = mysqli_query($conn,$sql);
            disConnect($conn);
            $_SESSION['allowToChangePass'] = 1;
            header("Location:changePassword.php");
        }
        else{
            $verificationFlag = 1;
        }
    }
    // check if the user wrote the correct verification code
    function getVerificationCode($email){
        $conn = connection();
        $sql = "SELECT `forgot_password_code` FROM `User` WHERE email='$email'";
        $retval = mysqli_query($conn, $sql);
        $value= $retval->fetch_object();
        $code = $value->forgot_password_code;
        disConnect($conn);
        return $code;
    }
    function verificationErr(){
        global $verificationFlag;
        if($verificationFlag == 1){
            echo "<p style='color: red;'> Verification Code doesn't match</p>";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forget Password</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .radius-div{
            border-radius:10px;
        }
        .header{
            font-size: 2.5em;
            color: #fff;
        }
        body{
            background-color: #eb6864;
        }
    </style>
</head>
<body>
<h1 class="header mt-5 d-flex justify-content-center animate__animated animate__tada">QuizTech</h1>
<div class="text-white d-flex justify-content-center container mt-5 ">
    <div class="w-50 bg-white mt-4 d-flex justify-content-center row radius-div shadow">
        <h2 class="mt-4 text-secondary">Verification Code</h2>
        <div class="w-100"></div>
        <div class="alert alert-dismissible alert-success w-75 mt-2">
            <strong>Enter the Verification Code</strong> that has been sent to your email in order to reset your password!
        </div>
        <form class="mt-4 w-75" action="" method="post">
            <input class="form-control text-center radius-div border-0 shadow" type="text" name="verifyCode" placeholder="Enter code pin">
            <?php verificationErr(); ?>
            <div class="w-100 mt-4" ></div>
            <div class="mt-4 mb-2 col text-center">
                <button type="submit"  class="btn btn-primary animate__animated animate__bounce">Submit</button>
            </div>
        </form>
        <div class="w-100"></div>
        <a href="?sendVerificatoinCodeAgain=true" class="text-primary mr-auto ml-2 mb-2" >Send Verification Code again</a>
    </div>
</div>

</body>
</html>

