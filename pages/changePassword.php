<?php
    include('serverConnection.php');
    session_start();
    $oldPassFlag = true;
    if((!$_SESSION['allowToChangePass']) || (!$_SESSION['user_email_forget_password'])){
        header("Location:profile.php");
    }
    else{
        if(isset($_POST['change_pass']) && isset($_POST['change_con_pass'])){
            $userEmail = $_SESSION['user_email_forget_password'];
            $password = $_POST['change_pass'];

             //The password gets updated and it resets it, in case the user forgot his password.
             $conn = connection();
             //before inserting the new password we have to check if the user inserting his old password
             $userLastPasswordSql = "SELECT `password` FROM `User` WHERE email='$userEmail'";
             $userLastPasswordRetval = mysqli_query($conn,$userLastPasswordSql)->fetch_assoc();
             $user_old_pass = $userLastPasswordRetval["password"];
             $password = md5($password);
             if($user_old_pass != $password){
                 $sql = "UPDATE `User` SET `password`='$password' WHERE email='$userEmail'";
                 $retval = mysqli_query($conn,$sql);
                 disConnect($conn);
                 header("Location:accountPasswordResetSuccessfully.php");
             }
             else{// if the user inserts his old password
                 $oldPassFlag = false;
                 disConnect($conn);
             }
        }
    }

    // returns an error in case the user trying to insert his old pass
    function err(){
        global $oldPassFlag;
        if($oldPassFlag == false){
            echo "<p style='color:red;' class='mt-2'> You cant insert your old password!<p>";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Change Password</title>
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
        <h2 class="mt-4 text-secondary">Change Password</h2>
        <div class="w-100"></div>
        <div class="alert alert-dismissible alert-info w-75 mt-3">
            Please enter and confirm your <strong>New</strong> password bellow to access your account.
        </div>
        <form action="" id="PasswordSubmitForm" method="post" class="mt-4 w-75">
            <input class="form-control text-center radius-div border-0 shadow" name="change_pass" type="Password" placeholder="Password">
            <div class="w-100 mt-4" ></div>
            <input class="form-control text-center radius-div border-0 shadow" name="change_con_pass" type="password" placeholder="Confirm Password">
            <div class="w-100 mt-4"></div>
            <p id="PasswordErr" style='color: red;'></p>
            <div class="mt-4 mb-2 col text-center">
                <a onclick="submitChangePass()" class="btn btn-primary animate__animated animate__bounce">Change</a>
                <?php err(); ?>
            </div>
        </form>
    </div>
</div>
</body>
<script>

    function submitChangePass() {
        var flag = true;
        var change_pass = document.getElementsByName("change_pass")[0].value;
        var change_con_pass = document.getElementsByName("change_con_pass")[0].value;
        var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{8,})/;
        //Check if the password meets the requirements
        if (!change_pass.match(regex) || change_pass.length > 20) {
            document.getElementById("PasswordErr").innerHTML = " Your password must contain :<br>Minimum one Capital letter <br>Minimum one Small letter<br>Minimum one Digit<br>8 characters minimum<br>Maximum 20 characters"
            flag = false;
        }
        else {
            document.getElementById("PasswordErr").innerHTML = "";
        }
        if (change_pass !== change_con_pass) {
            document.getElementById("PasswordErr").innerHTML = "Password doesn't match!";
            flag = false;
        } else {
            document.getElementById("PasswordErr").style.display = "";
        }
        if(flag){
            document.forms["PasswordSubmitForm"].submit();
        }
    }
</script>
</html>
