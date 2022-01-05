    <?php
session_start();
include('serverConnection.php');


if(!$_SESSION['logged_email']){
    header("Location:notUserMessage.php");
}
else{
    $conn = connection();
    $userEmail = $_SESSION['logged_email'];
    $userCurrentQuizSql = "SELECT * FROM `User` WHERE email='$userEmail'";
    $userCurrentQuizRetval = mysqli_query($conn,$userCurrentQuizSql)->fetch_assoc();
    $currentQuiz = $userCurrentQuizRetval["current_creating_quiz"];
    if($currentQuiz != 0){
        disConnect($conn);
        header("Location:addQuestion.php");
    }

    if(isset($_POST['quizName']) && isset($_POST['quizCategory']) && isset($_POST['quizLevel'])){
        $userIdSql = "SELECT `user_id` FROM `User` WHERE email='$userEmail'";
        $retvalID = mysqli_query($conn,$userIdSql);
        $id = $retvalID->fetch_assoc();
        $user_id = $id["user_id"];
        $name= $_POST['quizName'];
        $category = $_POST['quizCategory'];
        $level = $_POST['quizLevel'];
        $imgUrl="";
        //Check if the user uploaded an image to the quiz if not then the default image gets uploaded instead.
        if(isset($_POST['quizImageUrl']) && $_POST['quizImageUrl'] != null){
            $imgUrl=$_POST['quizImageUrl'];
        }else {
            $imgUrl = "http://localhost/QuizTech/images/webLogo.svg";
        }

        // Check if the user is admin , if so the system insert the quiz
        if($user_id == 1){
            //this query updates previous weekly event quiz to a normal quiz
            $updatePrevQuizFromWeeklyEventSQL = "UPDATE `Quiz` SET `is_weeklyEvent`=0 WHERE is_weeklyEvent=1";
            mysqli_query($conn, $updatePrevQuizFromWeeklyEventSQL);
            // insert information about the quiz that the user created into the Database
            $insertQuiz = "INSERT INTO `Quiz`(`quiz_name`, `quiz_picture`, `quiz_category`, `quiz_level`, `num_questions`,`is_weeklyEvent`, `user_id`) VALUES ('$name','$imgUrl','$category','$level',0,1,'$user_id')";
        }
        else{
            // insert information about the quiz that the user created into the Database
            $insertQuiz = "INSERT INTO `Quiz`(`quiz_name`, `quiz_picture`, `quiz_category`,  `quiz_level`, `num_questions`, `is_weeklyEvent`, `user_id`) VALUES ('$name','$imgUrl','$category','$level',0,0,'$user_id')";
        }

       $retval = mysqli_query($conn, $insertQuiz);
       $quiz_id = mysqli_insert_id($conn);
       //return from database number of created quiz
        $getNumCreatedSql = "SELECT `num_created_quizzes` FROM `User` WHERE email='$userEmail'";
        $getNumCreatedRetval = mysqli_query($conn, $getNumCreatedSql)->fetch_assoc();
        $numQuiz = $getNumCreatedRetval['num_created_quizzes']+1;
       // update number of created quiz in user table and current creating quiz
        $numCreatedSql = "UPDATE `User` SET `current_creating_quiz`='$quiz_id', `num_created_quizzes`='$numQuiz' WHERE email='$userEmail'";
        mysqli_query($conn, $numCreatedSql);

       disConnect($conn);
       header("Location:addQuestion.php");

    }
}
?>
<html>
    <head>
        <title>Create Quiz</title>
        <link rel="stylesheet" href="../css/profile.css">
        <link rel="stylesheet" href="../css/bootstrap.css">
        <!-- Animation library -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
        <!-- bootstrap javascript library -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <style>
          .radius-div{
            border-radius: 10px;
          }
          .pointer{cursor: pointer;}
          body{overflow: auto;}
        </style>
    </head>
    <body>
      <!-- add image popup window -->
      <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Quiz Image</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="" method="post" id="imageUrlForm">
              <div class="form-group">
                <label for="message-text" class="col-form-label">Image URL:</label>
                <input type="url" class="form-control text-center radius-div border-0 shadow" name="imageLink" onkeyup="addModalImage()" placeholder="Link" id="imageUrl">
              </div>
            </form>
          </div>
            <div class="d-flex justify-content-center">
                <img src="../images/webLogo.svg" class="radius-div userAvatarImg pointer" id="modalImage"/>
            </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" onclick="addImageUrl()" data-dismiss="modal" class="btn btn-primary">Add Image</button>
          </div>
        </div>
      </div>
    </div>

        <?php
        if($userEmail == "quiztechjj@gmail.com"){
            include('adminNavBar.php');
        }
        else{
            include('navBar.php');
        }?>
        <!-- div for create quiz information -->
        <div class=" d-flex justify-content-center container">
          <div class="mt-4 bg-primary  d-flex justify-content-center row radius-div shadow" >
              <h2 class="mt-3 text-white">Click to add image</h2>
              <div class="w-100"></div><!-- div for new line -->
              <img src="../images/addQuizImg.svg" class="img-thumbnail p-3 mt-4 radius-div shadow pointer"  width="35%" id="quizImage" data-toggle="modal" data-target="#exampleModal"/>
              <div class="w-100"></div><!-- div for new line -->
              <form class="mt-4 w-50" action="" method="post" id="quizDetailsForm">
                  <!-- hidden input contain quiz image url -->
                  <input type="hidden" id="valueImgUrl" name="quizImageUrl">
                  <input type="text" class="form-control text-center radius-div border-0 shadow" name="quizName" autocomplete="off" id="exampleInputEmail1" aria-describedby="emailHelp"  placeholder="Quiz Name">
                  <p id="nameErr" style='color: white;'></p>
                  <select class="form-control mt-3 radius-div border-0 shadow" name="quizCategory">
                      <option selected value="">Select Category</option>
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
                  <p id="categoryErr" style='color: white; display: none;'> You must select a category</p>
                  <select class="form-control mt-3 radius-div border-0 shadow" name="quizLevel">
                      <option selected value="">Select Level</option>
                      <option value="easy">Easy</option>
                      <option value="medium">Medium</option>
                      <option value="hard">Hard</option>
                  </select>
                  <p id="levelErr" style='color: white; display: none;'> You must select a quiz level</p>
                <div class="mt-4 col text-center">
                  <a onclick="submitCreateQuizForm()" class="btn btn-secondary shadow animate__animated animate__bounce">Done</a>
                </div>
              </form>
          </div>
        </div>
    </body>
    <script>

        // The function checks if the user inserts invalid image link
    function addImageUrl(){
        urlImage = document.getElementById("imageUrl").value;
        var myImg = document.getElementById("quizImage");
        var modalImage = document.querySelector("#modalImage");
        if( urlImage !== "" ){
            if(modalImage.naturalWidth === 0){
                alert("This link is not an image");
                myImg.src = "http://localhost/QuizTech/images/webLogo.svg";
                document.getElementById("imageUrl").value = "http://localhost/QuizTech/images/webLogo.svg";
                addModalImage();
            }
            else{
                myImg.src = urlImage;
            }

        }
    }

    // The function checks if the user uploaded an image to the quiz if not, then a default image gets uploaded instead.
    function addModalImage(){
        var image = document.getElementById("imageUrl").value;
        if(image === ""){
            document.getElementById("modalImage").src = "http://localhost/QuizTech/images/webLogo.svg";
        }
        else{
            document.getElementById("modalImage").src = image;
            document.getElementById("valueImgUrl").value = image;
        }
    }
    function submitCreateQuizForm(){
        var flag = true;
        var quizName = document.getElementsByName("quizName")[0].value;
        var quizCategory = document.getElementsByName("quizCategory")[0].value;
        var quizLevel = document.getElementsByName("quizLevel")[0].value;
        if(quizName.length < 3){
            document.getElementById("nameErr").innerHTML = "The quiz name must contain at least 3 characters.";
            flag = false;
        }
        else if(quizName.length > 30){
            document.getElementById("nameErr").innerHTML = "The quiz name can only contain up to 30 characters.";
            flag = false;
        }
        else{
            document.getElementById("nameErr").innerHTML = "";
        }
        if(quizCategory === ""){
            document.getElementById("categoryErr").style.display = "block";
            flag = false;
        }
        else{
            document.getElementById("categoryErr").style.display = "none";
        }
        if(quizLevel === ""){
            document.getElementById("levelErr").style.display = "block";
            flag = false;
        }
        else{
            document.getElementById("levelErr").style.display = "none";
        }
        if(flag){
            document.forms["quizDetailsForm"].submit();
        }
    }
    </script>
</html>
