<?php
    include('serverConnection.php');
    $emailFlag=0;
    if(isset($_POST['register_fullName']) && isset($_POST['register_email']) && isset($_POST['register_pass']) && isset($_POST['register_con_pass'])  && isset($_POST['register_birthday'])){
        $conn = connection();
        //prevents SQL injection
        $fullname =$conn->real_escape_string( $_POST['register_fullName']);
        $email = $conn->real_escape_string($_POST['register_email']);
        $birthday = $_POST['register_birthday'];
        $password = $conn->real_escape_string($_POST['register_pass']);
        $confirm_pass = $conn->real_escape_string($_POST['register_con_pass']);
        $category = $_POST['register_category'];

        checkEmailIsExist($email);
        if($emailFlag != 1){
            $password = md5($password);
            $code = mt_rand(100000, 999999);
            //insert the user's information into the datebase
            $sql = "INSERT INTO `User`(`full_name`, `email`, `birthday`, `password`, `is_verified`, `verification_code` , `forgot_password_code`, `favorite_category`, `picture_link`,`current_creating_quiz`, `num_created_quizzes`, `highest_score`, `num_played_quizzes`) VALUES ('$fullname','$email','$birthday','$password',0,$code,0,'$category','http://localhost/QuizTech/images/user_avatar.svg',0,0,0,0)";
            $retval = mysqli_query($conn,$sql);
            if(!$retval){
                echo "Somthing wrong " . mysqli_error($conn);
                disConnect($conn);
            }
            else{
                disConnect($conn);
                session_start();
                $_SESSION['user_email'] = $email;
                header("Location:mailAuthentication.php");
            }
        }
    }
    // Check if the user's email already exists in the database.
    function checkEmailIsExist($email){
        global $emailFlag;
        global $flag;
        $conn = connection();
        $sql = "SELECT * FROM `User` WHERE email='$email'";
        $retval = mysqli_query($conn, $sql);
        if(mysqli_num_rows($retval)){
            $emailFlag = 1;
            $flag = false;
        }
        disConnect($conn);
    }
    function emailIsExist(){
        global $emailFlag;
        if($emailFlag == 1){
            echo "<p style='color:red;'> This email is already exist!<p>";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
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
<!-- Register form -->
<h1 class="header mt-5 d-flex justify-content-center animate__animated animate__tada">QuizTech</h1>
<div class="text-white d-flex justify-content-center container mt-4 ">
    <div class="w-50 mt-3 bg-white d-flex justify-content-center row radius-div shadow">
      <h2 class="mt-3 text-secondary">Register</h2>
      <div class="w-100"></div>
        <form action="" method="post" id="RegisterForm" class="mt-3 w-75">
              <input class="form-control text-center radius-div border-0 shadow" type="text" name="register_fullName" placeholder="FullName">
                <p id="fullNameErr" style='color: red;'></p>
              <div class="w-100 mt-4" ></div>
              <input class="form-control text-center radius-div border-0 shadow" type="text" name="register_email" placeholder="Email">
                <p id="emailErr" style='color: red;'></p>
                <?php emailIsExist(); ?>
              <div class="w-100 mt-4"></div>
              <input class="form-control text-center radius-div border-0 shadow" type="date" name="register_birthday" placeholder="Birthday">
                <p id="birthdayErr" style='color: red;'></p>
              <div class="w-100 mt-4"></div>
              <input class="form-control text-center radius-div border-0 shadow" type="password" name="register_pass" placeholder="Password">
            <p id="passErr" style='color: red; display: none;'> Your password must contain :<br>
                Minimum one Capital letter <br>
                Minimum one Small letter<br>
                Minimum one Digit<br>
                8 characters minimum<br>
                Maximum 20 Character</p>
              <div class="w-100 mt-4"></div>
              <input class="form-control text-center radius-div border-0 shadow" type="password" name="register_con_pass" placeholder="Confirm Password">
                    <p id="conPassErr" style='color: red; display: none;'> Password doesn't match</p>
              <div class="w-100 mt-4"></div>
              <select name="register_category" class="form-control mt-2 radius-div border-0 shadow" id="categoryName">
                  <option value="" selected>Select Category</option>
                  <option value="Art">Art</option>
                  <option value="Computer">Computer</option>
                  <option value="Design">Design</option>
                  <option value="Education">Education</option>
                  <option value="For Kids">For Kids</option>
                  <option value="History">History</option>
                  <option value="Just For Fun">Just For Fun</option>
                  <option value="Language">Language</option>
                  <option value="Movies">Movies</option>
                  <option value="Music">Music</option>
                  <option value="Programming Language">Programming Language</option>
                  <option value="Sports">Sports</option>
                  <option value="Other">Other</option>
              </select>
            <p id="categoryErr" style='color: red; display: none;'> You must select category</p>
              <div class="w-100 mt-4"></div>

              <div class="mt-3 mb-2 col text-center">
                  <a onclick="registerSubmitForm()" class="btn btn-primary animate__animated animate__bounce">Done</a>
                  <div class="d-flex justify-content-center mt-2">
                      <div id="loading" class="spinner-border text-primary " style="display: none;" role="status">
                          <span class="sr-only">Loading...</span>
                      </div>
                  </div>
              </div>
        </form>
        <div class="w-100"></div>
        <a href="login.php" class="text-primary mr-auto ml-2 mb-2" >Already Registered?</a>
    </div>
  </div>
</body>

<script>
    function registerSubmitForm(){
        var flag = true;
        var register_fullName = document.getElementsByName("register_fullName")[0].value;
        var register_email = document.getElementsByName("register_email")[0].value;
        var register_pass = document.getElementsByName("register_pass")[0].value;
        var register_birthday = document.getElementsByName("register_birthday")[0].value;
        var register_con_pass = document.getElementsByName("register_con_pass")[0].value;
        var register_category = document.getElementsByName("register_category")[0].value;
        var regex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
        var regexPass = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{8,})/;
        var currentYear = new Date().getFullYear();
        var minAllowed = new Date().getFullYear()-6;
        var userBirthday = new Date( register_birthday );
        // check if the full name length is at least 3 characters
        if(register_fullName.length < 3) {
            document.getElementById("fullNameErr").innerHTML = "Write your real full name";
            flag = false;
        }
        else if(register_fullName.length > 20){
                document.getElementById("fullNameErr").innerHTML = "The full name can only be up to 20 characters";
        }
        else{
            document.getElementById("fullNameErr").innerHTML = " ";
        }
        //Check if the email is a valid email address
        if(!register_email.match(regex)){
            document.getElementById("emailErr").style.display = "block";
            flag = false;
        }
        else{
            document.getElementById("emailErr").style.display = "none";
        }
        // Check if the password meets the requirements
        if (!register_pass.match(regexPass)) {
            document.getElementById("passErr").style.display = "block";
            flag = false;
        }
        else if(register_pass > 20 ){
            document.getElementById("passErr").style.display = "block";
            flag = false;
        }
        else {
            document.getElementById("passErr").style.display = "none";
        }
        // Check if the confirm password matches the password
        if (register_pass !== register_con_pass) {
            document.getElementById("conPassErr").style.display = "block";
            flag = false;
        } else {
            document.getElementById("conPassErr").style.display = "none";
        }
        // Check if the user selected a category.
        if(register_category === ""){
            document.getElementById("categoryErr").style.display = "block";
            flag = false;
        }
        else{
            document.getElementById("categoryErr").style.display = "none";
        }
        // Check if the user's birthday is filled.
        if(register_birthday === ""){
            document.getElementById("birthdayErr").innerHTML = "Enter your birthday date!";
            flag = false;
        }
        // Check if the user's birthday is valid or make sense.
        else if(userBirthday.getFullYear() > minAllowed){
            document.getElementById("birthdayErr").innerHTML = "You have to be older than 6!";
            flag = false;
        }
        else if(userBirthday.getFullYear() < currentYear - 100){
            document.getElementById("birthdayErr").innerHTML = "Insert a proper age number!";
            flag = false;
        }
        else{
            document.getElementById("birthdayErr").style.display = "none";
        }
        // if the flag is true, the form gets submitted successfully
        if(flag){
            load();
            document.forms["RegisterForm"].submit();
        }
    }
    function load(){
        document.getElementById("loading").style.display = "block";
    }
</script>
</html>
