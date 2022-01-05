    <?php
    include('serverConnection.php');
    $loginErr = 0;
    session_start();

    //if user already logged in send him to main page
    if(isset($_SESSION['logged_email'])){
        header("Location:index.php");
    }
    if(isset($_POST['login_email']) && isset($_POST['login_pass'])){
        $conn = connection();
        $email = $conn->real_escape_string($_POST['login_email']);
        $password = $conn->real_escape_string( $_POST['login_pass']);
        // database connection
        $password = md5($password);
        $sql = "SELECT * FROM `User` WHERE email='$email' AND password='$password' limit 1";
        $retval = mysqli_query($conn, $sql);
        if(mysqli_num_rows($retval) == 1){
            $value= $retval->fetch_object();
            //Check if the user hasn't verified his account yet
            if($value->is_verified == 0){
                session_start();
                $_SESSION['user_email'] = $email;
                disConnect($conn);
                header("Location:mailAuthentication.php");
            }
            //Sends the user to the profile page
            else{
              session_start();
              $_SESSION['logged_email'] = $email;
              disConnect($conn);
              header("Location:index.php");
            }
        }
        else{
            disConnect($conn);
            $loginErr = 1;
        }
    }
    //if the user typed the incorrect email or password, an error pops up!
    function err(){
        global $loginErr;
        if($loginErr == 1){
            echo "<p style='color:red;'> Email or password is incorrect<p>";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
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
<!-- Login Form -->
<h1 class="header mt-5 d-flex justify-content-center animate__animated animate__tada">QuizTech</h1>
<div class="text-white d-flex justify-content-center container mt-5 ">
    <div class="w-50 bg-white mt-4 d-flex justify-content-center row radius-div shadow">
      <h2 class="mt-4 text-secondary">Login</h2>
      <div class="w-100"></div>
        <form action="" method="post" class="mt-4 w-75">
              <input class="form-control text-center radius-div border-0 shadow" name="login_email" type="text" placeholder="Email">
              <div class="w-100 mt-4" ></div>
              <input class="form-control text-center radius-div border-0 shadow" name="login_pass" type="password" placeholder="Password">
              <div class="w-100 mt-4"></div>
              <?php err(); ?>
             <a href="sendForgetPasswordCode.php" class="text-primary mr-auto ml-2 mb-2" >Forgot Password?</a>
              <div class="mt-4 mb-2 col text-center">
                  <button type="submit"  class="btn btn-primary animate__animated animate__bounce">Done</button>
              </div>
        </form>
        <div class="w-100"></div>
        <a href="register.php" class="text-primary mr-auto ml-2 mb-2" >New User?</a>
    </div>
  </div>
</body>
</html>
