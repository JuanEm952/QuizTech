<?php
include('serverConnection.php');
    $emailFlag=0;
    $errFlag =0;
    if(isset($_POST['forgot_pass_email'])){
        $email = $_POST['forgot_pass_email'];
        checkEmailIsExist($email);
        // creates a random code from 6 numbers and sends it to the email
        if($emailFlag == 1){
            $conn = connection();
            $code = mt_rand(100000, 999999);
            $sql="UPDATE `User` SET `forgot_password_code`=$code WHERE email='$email'";
            $retval = mysqli_query($conn,$sql);

            if(!$retval){
                echo "Somthing wrong " . mysqli_error($conn);
                disConnect($conn);
            }
            else{
                disConnect($conn);
                session_start();
                $_SESSION['user_email_forget_password'] = $email;
                header("Location:forgetPasswordVerification.php");
            }
        }
        else{
            $errFlag = 1;
        }
    }

    // check if the email exists
    function checkEmailIsExist($email){
        global $emailFlag;
        $conn = connection();
        $sql = "SELECT * FROM `User` WHERE email='$email' limit 1";
        $retval = mysqli_query($conn, $sql);
        if(mysqli_num_rows($retval) == 1){
            $emailFlag = 1;
        }
        disConnect($conn);
    }

    // returns an error in case the email doesnt exist in the database
    function err(){
        global $errFlag;
        if($errFlag == 1){
            echo "<p style='color:red;'> Your Email doesn't exist<p>";
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
<!-- Requires the user to insert his/her email to send the verification code to his/her email -->
<h1 class="header mt-5 d-flex justify-content-center animate__animated animate__tada">QuizTech</h1>
<div class="text-white d-flex justify-content-center container mt-5 ">
    <div class="w-50 bg-white mt-4 d-flex justify-content-center row radius-div shadow">
        <h2 class="mt-4 text-secondary">Forget Password</h2>
        <div class="w-100"></div>
        <div class="alert alert-dismissible alert-info w-75 mt-3">
            <strong>Enter Your email address</strong> associated with your account and we'll send you a verification code!
        </div>
        <form action="" method="post" class="mt-4 w-75">
            <input class="form-control text-center radius-div border-0 shadow" name="forgot_pass_email" type="text" placeholder="Email">
            <div class="w-100 mt-4" ></div>
            <?php err(); ?>
            <div class="mt-4 mb-2 col text-center ">
                <button id="myBtn" type="submit" onclick="load()" class="btn btn-primary animate__animated animate__bounce">Submit</button>
                <div class="d-flex justify-content-center mt-2">
                    <div id="loading" class="spinner-border text-primary " style="display: none;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function load(){
        document.getElementById("loading").style.display = "block";
    }
</script>
</body>
</html>
