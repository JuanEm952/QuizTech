<?php
include('serverConnection.php');
include('../phpmailer/sendMail.php');
session_start();
$verificationFlag = 0;
if(!$_SESSION['user_email']){
  header("Location:register.php");
}

// sends the user a verification code again to the user's email.
if(isset($_GET['sendVerificatoinCodeAgain'])){
  $userEmail = $_SESSION['user_email'];
  $code = getVerificationCode($userEmail);
  $body = "Thanks for signing up! Your account has been created, you have to enter this code to verify your email <b>$code</b>";
  sendMail($userEmail,"Account Verification",$body);
}
// Once the user creates an account and verification code sent to his email to verify account!
if($_SESSION['user_email'] && (!isset($_POST['authCode']))){
  $userEmail = $_SESSION['user_email'];
  $code = getVerificationCode($userEmail);
  $body = "Thanks for signing up! Your account has been created, you have to enter this code to verify your email <b>$code</b>";
  sendMail($userEmail,"Account Verification",$body);
}
// Checks if the user typed the correct verification code, if so, the account gets verified!
if(isset($_POST['authCode']) ){
    $userCode = $_POST['authCode'];
    $userEmail = $_SESSION['user_email'];
    $code = getVerificationCode($userEmail);
    if($code == $userCode){
      $conn = connection();
      $sql = "UPDATE `User` SET `is_verified`=1 WHERE email='$userEmail'";
      $retval = mysqli_query($conn,$sql);
      disConnect($conn);
        header('Location:accountCreatedSuccessfully.php');
    }
    else{
      $verificationFlag = 1;
    }
}
//returns user verification code from the database
function getVerificationCode($email){
    $conn = connection();
    $sql = "SELECT `verification_code` FROM `User` WHERE email='$email'";
    $retval = mysqli_query($conn, $sql);
    $value= $retval->fetch_object();
    $code = $value->verification_code;
    disConnect($conn);
    return $code;
}
// an error gets displayed if the verification code that the user inserted in incorrect
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
    <title>Authentication</title>
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
    <div class="w-50 bg-white mt-4 d-flex justify-content-center row radius-div shadow" id="verificationDiv">
      <h2 class="mt-4 text-secondary">Authentication</h2>
        <div class="w-100"></div>
        <div class="alert alert-dismissible alert-info w-75 mt-3">
            <strong>A Verification Code</strong> has been sent to your <strong>Email </strong>Check your inbox <strong>Warning!</strong> in case the Verification Code has not been sent to your Email you can send a <strong>Verification Code Again!</strong>
        </div>
        <form class="mt-4 w-75" action="" method="post">
              <input class="form-control text-center radius-div border-0 shadow" type="text" name="authCode" placeholder="Enter code pin">
              <?php verificationErr(); ?>
              <div class="w-100 mt-4" ></div>
              <div class="mt-4 mb-2 col text-center">
                  <button type="submit"  class="btn btn-primary animate__animated animate__bounce">Done</button>
              </div>
        </form>
        <div class="w-100"></div>
        <a href="?sendVerificatoinCodeAgain=true" class="text-primary mr-auto ml-2 mb-2" >Send Verification Code again</a>
    </div>
  </div>
</body>
</html>
