<?php
    include('serverConnection.php');
    session_start();
    $flagIsEdit = false;
    $questionNum=0;
    $editOldQuestion = false;
    if(!$_SESSION['logged_email']){
        header("Location:notUserMessage.php");
    }
    $conn = connection();
    $userEmail = $_SESSION['logged_email'];
    $userCurrentQuizSql = "SELECT * FROM `User` WHERE email='$userEmail'";
    $userCurrentQuizRetval = mysqli_query($conn,$userCurrentQuizSql)->fetch_assoc();
    $currentQuizID = $userCurrentQuizRetval["current_creating_quiz"];
    $currentUserID = $userCurrentQuizRetval["user_id"];

    //check if the user cant add question (before creating a quiz) or add quiz on an exist quiz
    if($currentQuizID == 0 && (!isset($_GET["edit"])) && (!isset($_POST['update']))) {
        disConnect($conn);
        header("Location:createQuiz.php");
    }
    else {
        $quiz_id = $currentQuizID;

        if(isset($_POST['update']) && $_POST['update'] == true){

            if(isset($_POST['quiz_id']) && isset($_POST['question_id']) ){

                $quiz_id = $_POST['quiz_id'];
                $questionId = $_POST['question_id'];
                // prevents sql injection
                $text = $conn->real_escape_string($_POST['question_text']);
                $answer1 = $conn->real_escape_string($_POST['answer1']);
                $answer2 = $conn->real_escape_string($_POST['answer2']);
                $answer3 = $conn->real_escape_string($_POST['answer3']);
                $answer4 = $conn->real_escape_string($_POST['answer4']);
                $correctAnswer = $_POST['correctAnswer'];
                $time = $_POST['timer'];
                if(isset($_POST['imageUrl'])){
                    $imageUrl = $_POST['imageUrl'];
                    $imageUrl = $conn->real_escape_string($imageUrl);
                    $updateEditedQuestionSQL = "UPDATE `Question` SET `text`='$text',`picture`='$imageUrl',`time`='$time',`answer_1`='$answer1',`answer_2`='$answer2',`answer_3`='$answer3',`answer_4`='$answer4',`correct_answer`='$correctAnswer' WHERE question_id='$questionId'";
                }
                else{
                    $updateEditedQuestionSQL = "UPDATE `Question` SET `text`='$text',`time`='$time',`answer_1`='$answer1',`answer_2`='$answer2',`answer_3`='$answer3',`answer_4`='$answer4',`correct_answer`='$correctAnswer' WHERE question_id='$questionId'";
                }

                mysqli_query($conn,$updateEditedQuestionSQL);
                disConnect($conn);
                header('Location: editQuestion.php?id='.$quiz_id);
            }
            else{
                disConnect($conn);
                header('Location: index.php');
            }
        }
        else if(isset($_GET['edit']) && $_GET['edit'] == "true"){
            if(isset($_GET['quiz_id'])){
                $quiz_id = $_GET['quiz_id'];
                //this query to check if the user can add to current quiz (if he has created the quiz) or if the current user is admin
                $canAddToThisQuizSQL = "SELECT * FROM `Quiz` WHERE quiz_id='$quiz_id' AND user_id='$currentUserID'";
                $canAddToThisQuizRetval =  mysqli_query($conn,$canAddToThisQuizSQL);
                if(mysqli_num_rows($canAddToThisQuizRetval) > 0 || $userEmail == "quiztechjj@gmail.com"){
                    $flagIsEdit = true;
                }
                else{?>
                    <script>
                        alert('You dont have permission to edit this question!')
                        window.open('index.php','_self');
                    </script>
                    <?php
                }
            }
            else{
                disConnect($conn);
                header('Location: index.php');
            }
        }
        else if($currentQuizID != 0){
            $quiz_id = $currentQuizID;
        }
        $quizDetailsSQL = "SELECT * FROM `Quiz` WHERE quiz_id='$quiz_id'";
        $quizDetailsRetval = mysqli_query($conn,$quizDetailsSQL)->fetch_assoc();

        //if the user editing an old question
        if(isset($_GET['question_id'])){
            $questionId = $_GET['question_id'];
            $oldQuestionSQL = "SELECT * FROM `Question` WHERE question_id='$questionId' AND quiz_id='$quiz_id'";
            $oldQuestionRetval = mysqli_query($conn,$oldQuestionSQL);
            // if the question_id
            if(mysqli_num_rows($oldQuestionRetval) > 0){
                $editOldQuestion = true;
                $oldQuestion = $oldQuestionRetval->fetch_assoc();
            }
            else{ ?>
                <script>
                    alert('You cant edit this question')
                    window.open('editQuestion.php?id=<?php echo $quiz_id ?>','_self');
                </script>
                <?php
            }

        }

        //if the user want to finish adding question
        if(isset($_GET['finishQuiz']) && $_GET['finishQuiz'] == "true"){
            finishQuizPhp();
        }
        //get current question num
        $currentQuestionSql = "SELECT * FROM `Question` WHERE quiz_id='$quiz_id' ORDER BY question_number Desc limit 1;";
        $currentQuestionRetval = mysqli_query($conn,$currentQuestionSql)->fetch_assoc();
        if($currentQuestionRetval){
            $questionNum = $currentQuestionRetval["question_number"]+1;
        }
        else{
            $questionNum = 1;
        }


        if(isset($_POST['question_text']) && isset($_POST['answer1']) && isset($_POST['answer2']) && isset($_POST['answer3']) && isset($_POST['answer4'])){

            // prevents sql injection
            $text = $conn->real_escape_string($_POST['question_text']);
            $answer1 = $conn->real_escape_string($_POST['answer1']);
            $answer2 = $conn->real_escape_string($_POST['answer2']);
            $answer3 = $conn->real_escape_string($_POST['answer3']);
            $answer4 = $conn->real_escape_string($_POST['answer4']);
            $correctAnswer = $_POST['correctAnswer'];
            $time = $_POST['timer'];
            $imageUrl = "";

            if(isset($_POST['imageUrl'])){
                $imageUrl = $_POST['imageUrl'];
            }
            $imageUrl = $conn->real_escape_string($imageUrl);

            // insert the question's details into the database
            $addQuestionSql = "INSERT INTO `Question`(`question_number`,`text`, `picture`, `time`, `answer_1`, `answer_2`, `answer_3`, `answer_4`, `correct_answer`, `quiz_id`) VALUES ('$questionNum','$text','$imageUrl','$time','$answer1','$answer2','$answer3','$answer4','$correctAnswer','$quiz_id')";

            mysqli_query($conn,$addQuestionSql);

            disConnect($conn);
            questionNumCnt($quiz_id);
            ?>
            <script>
                alert("Question added successfully");
            </script>
        <?php
            // if the added question to an existing quiz the system will sent him back to editQuestion page
            if($flagIsEdit){ disConnect($conn);?>
                <script>
                    window.open('editQuestion.php?id=<?php echo $quiz_id ?>','_self');
                </script> <?php
            }
            else { ?>
                <script>
                    window.open('addQuestion.php','_self');
                </script> <?php
            }
        }

    }
    // function to finish the quiz, but the user must add minimum 3 question to finish adding question
    function finishQuizPhp(){
        global $quiz_id;
        global $userEmail;
        $conn = connection();
        $numOfQuestionSql = "SELECT  `num_questions` FROM `Quiz` WHERE quiz_id='$quiz_id'";
        $numOfQuestionReval = mysqli_query($conn,$numOfQuestionSql)->fetch_assoc();
        $numOfQuestion = $numOfQuestionReval["num_questions"];

        if( $numOfQuestion < 3){ ?>
            <script>
                alert('You cannot finish creating the quiz if the quiz has less than 3 questions');
                //this command help us to prevent the user to insert the question again in case the user refreshed the page
                window.open('addQuestion.php','_self');
            </script>
            <?php
        }
        else {
            $updateCurrentCreatingQuizSQL = "UPDATE `User` SET `current_creating_quiz`=0 WHERE email='$userEmail'";
            mysqli_query($conn, $updateCurrentCreatingQuizSQL);
            disConnect($conn);
            header_remove();
            header("Location:createQuiz.php");
        }
    }

    function questionNumCnt($quiz_id){
        // take num_question from database to update number of question in this quiz
        $conn = connection();
        $question_num_sql = "SELECT `num_questions` FROM `Quiz` WHERE quiz_id='$quiz_id'";
        $retvalNum = mysqli_query($conn,$question_num_sql);
        $question_num = $retvalNum->fetch_assoc();
        $quiz_question_num = $question_num["num_questions"]+1;
        // update num_question after adding a question
        $updateQuistionNum = "UPDATE `Quiz` SET `num_questions`='$quiz_question_num' WHERE quiz_id='$quiz_id'";
        mysqli_query($conn,$updateQuistionNum);
        disConnect($conn);
    }


?>
<html xmlns="http://www.w3.org/1999/html">
    <head>
        <title>Add Question</title>
        <link rel="stylesheet" href="../css/bootstrap.css">
        <link rel="stylesheet" href="../css/profile.css">
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
          body {
              background-color: #ececec !important;
          }
          @media (max-width: 576px) {
              .quizCard{
                  display: none;
              }
          }
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
                            <input type="url" class="form-control text-center radius-div border-0 shadow" onkeyup="addModalImage()" placeholder="Link" id="imageUrl">
                        </div>
                    </form>
                </div>
                <div class="d-flex justify-content-center">
                    <img src="../images/addQuizImg.svg" class="radius-div userAvatarImg" id="modalImage"/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" onclick="addImageUrl()" data-dismiss="modal" class="btn btn-primary">Add Image</button>
                </div>
            </div>
        </div>
    </div>
        <?php
        if($_SESSION['logged_email'] == "quiztechjj@gmail.com"){
            include('adminNavBar.php');
        }
        else{
            include('navBar.php');
        }?>
        <!-- div for quiz details-->
        <div class="quizCard">
            <div class="card col-lg-3 col-md-5 col-sm-6 mt-1 shadow">
                <div class="row no-gutters">
                    <div class="col-sm-5">
                        <img class="w-100 m-1 mt-2" src="<?php echo $quizDetailsRetval['quiz_picture']?>" alt="Suresh Dasari Card">
                    </div>
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $quizDetailsRetval['quiz_name']?></h5>
                            <p class="card-text">Category: <?php echo $quizDetailsRetval['quiz_category']?></p>
                            <p class="card-text">Number Question: <?php echo $quizDetailsRetval['num_questions']?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class=" d-flex justify-content-center container mt-1">
          <div class="mt-4 bg-white  d-flex justify-content-center row radius-div shadow w-75" >
              <div id="addQuestionDiv">
                  <!-- div for creating a quiz -->
                  <h2 class="header mt-3 d-flex justify-content-center animate__animated animate__tada text-primary">Add Question</h2>
                  <form action="" id="submitQuestionForm" method="post" class="ml-3 mr-3 mt-3">
                      <div class="form-group" class="d-flex justify-content-center">
                          <input type="text" class="form-control radius-div border-0 shadow" onkeyup="disableFinishBtn()" id="exampleInputEmail1" name="question_text" placeholder="Question label" <?php if($editOldQuestion){  ?>value="<?php echo $oldQuestion['text']; ?>" <?php } ?>>
                      </div>
                      <div class="row mt-4">
                          <div class="col-6 ">
                          <div class="input-group ">
                              <span class="input-group-append bg-white border-right-0">
                                  <span class="input-group-text bg-transparent border-right-0 shadow">
                                      <img src="../images/falseIcon.svg" width="20" id="option1">
                                  </span>
                              </span>
                              <input type="text" class="form-control text-center border-left-0 " onkeyup="disableFinishBtn()" name="answer1" placeholder="Option 1" <?php if($editOldQuestion){  ?>value="<?php echo $oldQuestion['answer_1']; ?>" <?php } ?>>
                          </div>
                          </div>
                          <div class="col-6">
                          <div class="input-group">
                              <span class="input-group-append bg-white">
                                  <span class="input-group-text bg-transparent  border-right-0 shadow">
                                      <img src="../images/falseIcon.svg" width="20" id="option2">
                                  </span>
                              </span>
                              <input type="text" class="form-control text-center border-left-0" onkeyup="disableFinishBtn()" name="answer2" placeholder="Option 2" <?php if($editOldQuestion){  ?>value="<?php echo $oldQuestion['answer_2']; ?>" <?php } ?>>
                          </div>
                          </div>
                      </div>
                      <div class="row mt-4">
                          <div class="col-6">
                          <div class="input-group">
                              <span class="input-group-append bg-white">
                                  <span class="input-group-text bg-transparent  border-right-0 shadow">
                                      <img width="20"  src="../images/falseIcon.svg"  id="option3">
                                  </span>
                              </span>
                              <input type="text" class="form-control text-center border-left-0" onkeyup="disableFinishBtn()" name="answer3" placeholder="Option 3" <?php if($editOldQuestion){  ?>value="<?php echo $oldQuestion['answer_3']; ?>" <?php } ?>>
                          </div>
                          </div>
                          <div class="col-6">
                          <div class="input-group">
                              <span class="input-group-append bg-white">
                                  <span class="input-group-text bg-transparent  border-right-0 shadow">
                                      <img width="20"  src="../images/falseIcon.svg"  id="option4">
                                  </span>
                              </span>
                              <input type="text" class="form-control text-center border-left-0" onkeyup="disableFinishBtn()" name="answer4" placeholder="Option 4" <?php if($editOldQuestion){  ?>value="<?php echo $oldQuestion['answer_4']; ?>" <?php } ?>>
                          </div>
                          </div>
                      </div>
                      <!-- //returns an error in case the user didnt fill the required text fields! -->
                      <p id="OptionErr" class='mt-2' style='color: red; display: none;'> You must fill all Text and Option fields and the question label must be at least 3 characters</p>
                      <div class="row mt-4">
                          <div class="col-6">
                              <div class="input-group d-flex justify-content-center">
                              <span class="input-group-append bg-white">
                                  <span class="input-group-text bg-transparent  border-right-0 shadow">
                                      <img width="20" class="pointer" src="../images/timeIcon.svg">
                                  </span>
                              </span>
                                  <select class="shadow" name="timer" >
                                      <?php if($editOldQuestion){  ?>
                                        <option selected value="<?php echo $oldQuestion['time']; ?>"><?php echo $oldQuestion['time']; ?> Sec</option>
                                      <?php } ?>
                                      <option value="10">10 Sec</option>
                                      <option value="20">20 Sec</option>
                                      <option value="30">30 Sec</option>
                                      <option value="60">60 Sec</option>
                                  </select>
                              </div>
                          </div>
                          <div class="col-6">
                              <div class="input-group d-flex justify-content-center">
                                  <span class="input-group-append bg-white">
                                      <span class="input-group-text bg-transparent w-100 border-right-0 shadow pointer" data-toggle="modal" data-target="#exampleModal">
                                          <img width="20" class=" mr-2"  src="../images/addQuizImg.svg"> <span id="imageTitle">Add Image</span>
                                          <!-- hidden input for an image-->
                                          <input type="hidden" id="imageUrlField" name="imageUrl" >
                                      </span>
                                  </span>
                              </div>
                          </div>

                      </div>
                              <div class="input-group d-flex justify-content-center mt-4">
                                  <label>Choose correct answer</label>
                                  <div class="w-100"></div>
                                  <span class="input-group-append bg-white">
                                      <span class="input-group-text bg-transparent  border-right-0 shadow">
                                          <img width="35" class="pointer" src="../images/chooseAnswer.svg">
                                      </span>
                                  </span>
                                  <select class="shadow" name="correctAnswer" onchange="correctAnswerImage()" id="selectOption">
                                      <?php if($editOldQuestion){  ?>
                                          <option selected value="<?php echo $oldQuestion['correct_answer']; ?>">Option <?php echo $oldQuestion['correct_answer']; ?></option>
                                      <?php } else{?>
                                          <option value="">Select</option>
                                      <?php } ?>
                                      <option value="1">Option 1</option>
                                      <option value="2">Option 2</option>
                                      <option value="3">Option 3</option>
                                      <option value="4">Option 4</option>
                                  </select>
                                  <div class="w-100"></div>
                                  <!-- returns an error in case the user didnt choose a correct option! -->
                                  <p id="CorrectErr" class='mt-2' style='color: red; display: none;'> Please select the correct option!</p>
                              </div>
                      <div class="d-flex justify-content-center mb-5 mt-3">
                          <?php if($editOldQuestion){  ?>
                              <a onclick="submitAddQuestion()" class="btn btn-secondary mt-3 mr-2">Update question</a>
                              <!-- hidden data to update current question -->
                              <input type="hidden" name="update" value="true">
                              <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                              <input type="hidden" name="question_id" value="<?php echo $questionId; ?>">
                          <?php }else{ ?>
                              <a id="addQuestionBtn" onclick="submitAddQuestion()" style="display: none;" class="btn btn-secondary mt-3 mr-2">Add Question</a>
                          <?php }?>
                          <a id="finishBtn" onclick="submitFinish()" class="btn btn-primary mt-3">Finish</a>
                      </div>
              </form>
          </div>
        </div>
        </div>
    </body>

    <script>
        correctAnswerImage();
        function correctAnswerImage(){

            select = document.getElementById("selectOption").value;
            option1 = document.getElementById("option1");
            option2 = document.getElementById("option2");
            option3 = document.getElementById("option3");
            option4 = document.getElementById("option4");

            //false option link
            option1.src = "http://localhost/QuizTech/images/falseIcon.svg";
            option2.src = "http://localhost/QuizTech/images/falseIcon.svg";
            option3.src = "http://localhost/QuizTech/images/falseIcon.svg";
            option4.src = "http://localhost/QuizTech/images/falseIcon.svg";

            if(select === "1")
            {
                option1.src = "http://localhost/QuizTech/images/trueIcon.svg";//correct option link
            }
            else if(select === "2")
            {
                option2.src = "http://localhost/QuizTech/images/trueIcon.svg";//correct option link
            }
            else if(select === "3")
            {
                option3.src = "http://localhost/QuizTech/images/trueIcon.svg";//correct option link
            }
            else if(select === "4")
            {
                option4.src = "http://localhost/QuizTech/images/trueIcon.svg";//correct option link
            }
        }

        // The function checks if user uploaded an image to represent the quiz, if not then the function sets the image box to the default image
        function addModalImage(){
            var image = document.getElementById("imageUrl").value;
            if(image === ""){
                document.getElementById("modalImage").src = "http://localhost/QuizTech/images/addQuizImg.svg";
                document.getElementById("imageTitle").innerHTML = "Add Image";
            }
            else{
                document.getElementById("imageTitle").innerHTML = "Image added<img src='../images/trueIcon.svg' class='ml-2' width='20'/>";
                document.getElementById("modalImage").src = image;
                document.getElementById("valueImgUrl").value = image;
            }
        }

        //The function checks if the link of the image is valid
        function addImageUrl(){
            urlImage = document.getElementById("imageUrl").value;
            var modalImage = document.querySelector("#modalImage");
            if( urlImage !== "" ){
                if(modalImage.naturalWidth === 0){
                    alert("This link is not an image");
                    document.getElementById("imageUrl").value = "";
                    addModalImage();
                }
                else{
                    document.getElementById("imageUrlField").value  = urlImage;
                }
            }
        }

        function submitAddQuestion(){
            var flag = true;
            var question_text = document.getElementsByName("question_text")[0].value;
            var answer1 = document.getElementsByName("answer1")[0].value;
            var answer2 = document.getElementsByName("answer2")[0].value;
            var answer3 = document.getElementsByName("answer3")[0].value;
            var answer4 = document.getElementsByName("answer4")[0].value;
            var correctAnswer = document.getElementsByName("correctAnswer")[0].value;

            //Check if the question text length is bigger than 3 and if the answers is not filled.
            if(question_text.length < 3 || answer1 === ""  || answer2 === "" || answer3 === "" || answer4 === ""){
                document.getElementById("OptionErr").style.display = "block";
                flag =false;
            }
            else{
                document.getElementById("OptionErr").style.display = "none";
            }
            // Check if the user selected a correct option/answer
            if( correctAnswer === ""){
                document.getElementById("CorrectErr").style.display = "block";
                flag = false;
            }
            else{
                document.getElementById("CorrectErr").style.display = "none";
            }
            // if the flag is true, the form gets submitted
            if(flag){
                document.forms["submitQuestionForm"].submit();
            }
        }

        //if the user writes something in question fields, the function displays add question btn else displays finish btn
        function disableFinishBtn(){
            var question_text = document.getElementsByName("question_text")[0].value;
            var answer1 = document.getElementsByName("answer1")[0].value;
            var answer2 = document.getElementsByName("answer2")[0].value;
            var answer3 = document.getElementsByName("answer3")[0].value;
            var answer4 = document.getElementsByName("answer4")[0].value;
            if(question_text !== "" || answer1 !== "" || answer2 !== "" || answer3 !== "" || answer4 !== ""){
                document.getElementById("finishBtn").style.display ="none";
                document.getElementById("addQuestionBtn").style.display = "block";
            }
            else {
                document.getElementById("finishBtn").style.display ="block";
                document.getElementById("addQuestionBtn").style.display = "none";
            }
        }

        //this function check if the user is editing or adding a question to an old quiz and the user want to finish
        function submitFinish() {
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            // if the user is in editing mode and want to finish the system open editQuestion page
            if (urlParams.has('edit')) {
                window.open('editQuestion.php?id=<?php echo $quiz_id ?>', '_self');
            } else {
                window.open('addQuestion.php?finishQuiz=true', '_self');
            }
        }

    </script>
</html>
