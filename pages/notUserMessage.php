<?php

session_start();

if(isset($_SESSION['user_email'])){
    header("Location:index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Not User</title>
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
<?php include('navBar.php'); ?>
<h1 class="header mt-5 d-flex justify-content-center animate__animated animate__tada">QuizTech</h1>
<div class="text-white d-flex justify-content-center container mt-5 ">
    <div class="w-50 bg-white mt-4 d-flex justify-content-center row radius-div shadow">
        <h2 class="mt-4 text-secondary">Login Required</h2>
        <div class="w-100"></div>
        <div class="alert alert-dismissible alert-warning w-75 mt-3">
            You cannot view this URL as a guest. You must <strong> Register </strong> or <strong> Login </strong> in order to access this URL.
        </div>
        <div class="mt-4 w-75">
            <div class="mt-2 mb-2 col text-center mb-4">
                <button onclick="openLoginPage()" class="btn btn-secondary animate__animated animate__bounce">Go To Login</button>
                <button onclick="openRegisterPage()" class="btn btn-primary animate__animated animate__bounce">Go To Register</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openLoginPage(){
        window.open("http://localhost/QuizTech/pages/login.php", "_self");
    }
    function openRegisterPage(){
        window.open("http://localhost/QuizTech/pages/register.php", "_self");
    }
</script>
</body>
</html>
