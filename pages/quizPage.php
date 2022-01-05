<?php
include('serverConnection.php');
session_start();
$flag = false;
$leadFlag = false;
$questionFlag = true;

if(isset($_SESSION['score'])){
    unset($_SESSION['score']);
}
if(isset($_SESSION['streak'])){
    unset($_SESSION['streak']);
}
if(isset($_SESSION['correctAnswers'])){
    unset($_SESSION['correctAnswers']);
}
if(isset($_SESSION['wrongAnswers'])){
    unset($_SESSION['wrongAnswers']);
}

if(!$_SESSION['logged_email']){
    header("Location:notUserMessage.php");
}
else{
    if(isset($_GET['quiz_id'])){
        $conn = connection();
        $userEmail = $_SESSION['logged_email'];
        // Prevent SQL injection
        $quiz_id = $conn->real_escape_string($_GET['quiz_id']);
        $sql = "SELECT * FROM `Quiz` WHERE quiz_id='$quiz_id'";
        $quiz = mysqli_query($conn,$sql);
        // if the user opened link with wrong quiz_id number or the quiz_id not found
        if(mysqli_num_rows($quiz) != 1){
            disConnect($conn);
            header("Location:categories.php");
        }
        else{
            $flag = true;
            // check if the quiz has question
            $questionSql = "SELECT * FROM `Question` WHERE quiz_id='$quiz_id'";
            $question = mysqli_query($conn,$questionSql);
            if(mysqli_num_rows($question) == 0){
                $questionFlag = false;
            }
            // Sort leaderboard by users with the most points to a specific quiz.
            $leaderBoardSql = "SELECT * FROM Statistics WHERE quiz_id='$quiz_id' ORDER BY points Desc limit 10";
            $leaderBoardRetval = mysqli_query($conn,$leaderBoardSql);
            if(mysqli_num_rows($leaderBoardRetval)>0){
                $leadFlag = true;
            }

            // Get the user ID
            $userIdSql = "SELECT `user_id` FROM `User` WHERE email='$userEmail'";
            $retvalID = mysqli_query($conn,$userIdSql)->fetch_assoc();
            $user_id = $retvalID["user_id"];
            // Check if the current user has created this quiz
            $isThisUserCreatedThisQuizSql = "SELECT * FROM `Quiz` WHERE quiz_id='$quiz_id' AND user_id='$user_id'";
            $isThisUserCreatedThisQuizRetval = mysqli_num_rows(mysqli_query($conn,$isThisUserCreatedThisQuizSql));
            // check if the user has played this quiz before
            $userIsPlayedQuizSql = "SELECT * FROM `UserQuizConn` WHERE user_id='$user_id' AND quiz_id='$quiz_id'";
            $userIsPlayedQuizRetval = mysqli_num_rows(mysqli_query($conn,$userIsPlayedQuizSql));

            if(isset($_GET['clickedStart'])){

                if($userIsPlayedQuizRetval == 0){ // if this return 0 that mean the user hasn't played the quiz
                    echo "<script> if (window.confirm('This is your first time playing this quiz and this is your only chance to affect on your level and statistics, Be careful!'))
                    {
                        window.open('quizGame.php?quiz_id=$quiz_id&n=1','_self');
                    }
                    </script>";
                }
                else{
                    echo "<script> if (window.confirm('This is not your first time playing this quiz so that means that it will not affect on your level and statistics, Enjoy!'))
                    {
                        window.open('quizGame.php?quiz_id=$quiz_id&n=1','_self');
                    }
                    </script>";
                }
            }
        }
    }
    else{
        header("Location:categories.php");
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Quiz</title>
        <link rel="stylesheet" href="../css/bootstrap.css">
        <link rel="stylesheet" href="../css/category.css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>

        <style>
            input:disabled{
                background-color: white !important;
            }
            .leaderboard-img{
                width: 50%;
                padding-left: 0px;
            }
            th{
                width: 25%;
            }
        </style>
    </head>
    <body>
    <?php
    if($_SESSION['logged_email'] == "quiztechjj@gmail.com"){
        include('adminNavBar.php');
    }
    else{
        include('navBar.php');
    }?>

    <?php if($flag){  $row = $quiz->fetch_assoc();{ ?>
    <!--  div displays the quiz details -->
    <div class="d-flex justify-content-center ">
        <h1 class="text-muted"><?php echo htmlspecialchars($row["quiz_name"]); ?></h1>
    </div>
    <div class="row" style="margin-left:0; margin-right:0;">
        <div class="col-lg-3 col-md-3 bg-white bg-shadow mt-3" style="padding-right:0;">
            <div class="list-group d-flex justify-content-center ">
                <?php if($leadFlag){ ?>
                <table class="table table-hover">
                    <span class="d-flex justify-content-center ">
                        <h1 class="text-muted">Leaderboard</h1>
                    </span>
                    <tbody>
                    <?php
                        while($leadRow = $leaderBoardRetval->fetch_assoc()) {
                            $currentUserId = $leadRow['user_id'];
                            $leaderBoardUserSql = "SELECT * FROM `User` WHERE user_id='$currentUserId'";
                            $userRetval = mysqli_query($conn,$leaderBoardUserSql);
                            $userRow = $userRetval->fetch_assoc();
                            ?>
                            <tr>
                                <th class="ml-0"><img class="leaderboard-img" src="<?php echo $userRow["picture_link"];?>"/></th>
                                <td class="ml-0">
                                    <h4 class="d-flex justify-content-center"><?php echo htmlspecialchars($userRow["full_name"]);?> </h4>
                                    <span class="d-flex justify-content-center"> <?php echo $leadRow["points"]; ?>pts</span>
                                </td>
                            </tr>
                        <?php }}?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Div displays the quiz's questions and answers -->
        <div class="col-lg-6 col-md-6 mt-5">
            <div class="row ml-2 mr-2 d-flex justify-content-center">
                <div class="card border-primary mb-3 col-lg-4 col-md-5 mb-4 correct-item removePadding no_decoration" >
                    <div class="card-header bg-primary HeaderText"><?php echo htmlspecialchars($row["quiz_name"]); ?></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-center">
                            <img class="w-75" src="<?php echo $row["quiz_picture"]; ?>"/>
                        </div>
                        <p class="mt-3">Number of questions : <?php echo $row["num_questions"]; ?></p>
                        <p>Quiz category : <?php echo $row["quiz_category"]; ?></p>
                    </div>
                </div>
                <div class="w-100 mt-2" ></div>
                <a href="quizPage.php?quiz_id=<?php echo $row["quiz_id"]; ?>&clickedStart=true" type="button" class="btn btn-primary w-25"  ><img  src="../images/play.svg" width="25"> Start Quiz</a>
                <div class="w-100 mt-2"></div>
                <button value="quizPage.php?quiz_id=<?php echo $row["quiz_id"]; ?>&clickedStart=true" onclick="startWithML(this.value)" type="button" class="btn btn-primary w-25" data-toggle="tooltip" data-placement="top" title="Starting the quiz using voice recognition based on machine learning lets you to answer the questions by using your voice and saying the number of the option from one to four"><img  src="../images/mlicon.svg" width="25"> Start Quiz Using VR</button>
            </div>

            <div class="mb-5">
            <?php }} ?>
                <!-- check if the quiz has question and if the user has played the quiz before if yes the system will display quiz questions or if this user has created this quiz -->
            <?php if($questionFlag && ($userIsPlayedQuizRetval != 0 || $isThisUserCreatedThisQuizRetval == 1) ){
                while($questionRow = $question->fetch_assoc()) { ?>
            <div class=" d-flex justify-content-center container mt-2 ">

                <div class="mt-4 bg-white  d-flex justify-content-center row radius-div shadow w-75" >
                    <div >
                        <form  class="ml-3 mr-3">
                            <div class="form-group" class="d-flex justify-content-center">
                                <input type="text" class="form-control mt-3 radius-div border-0 shadow" value="<?php echo htmlspecialchars($questionRow['text']); ?>" disabled>
                            </div>
                            <div class="row mt-4">
                                <div class="col-6">
                                    <div class="input-group">
                                        <span class="input-group-append bg-white">
                                            <span class="input-group-text bg-transparent  border-right-0 shadow">
                                                <img width="20"  src="../images/grayCircle.svg">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control text-center border-left-0" value="<?php echo htmlspecialchars($questionRow['answer_1']); ?>" disabled>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group">
                                        <span class="input-group-append bg-white">
                                            <span class="input-group-text bg-transparent  border-right-0 shadow">
                                                <img width="20"  src="../images/grayCircle.svg">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control text-center border-left-0" value="<?php echo htmlspecialchars($questionRow['answer_2']); ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4  mb-3">
                                <div class="col-6">
                                    <div class="input-group">
                                        <span class="input-group-append bg-white">
                                            <span class="input-group-text bg-transparent  border-right-0 shadow">
                                                <img width="20"  src="../images/grayCircle.svg">
                                            </span>
                                        </span>
                                        <input type="text" class="form-control text-center border-left-0" value="<?php echo htmlspecialchars($questionRow['answer_3']); ?>" disabled>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group">
                                        <span class="input-group-append bg-white">
                                            <span class="input-group-text bg-transparent  border-right-0 shadow">
                                                <img width="20"  src="../images/grayCircle.svg">
                                            </span>
                                        </span>
                                    <input type="text" class="form-control text-center border-left-0" value="<?php echo htmlspecialchars($questionRow['answer_4']); ?>" disabled>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php }}disConnect($conn);?>
        </div>
        </div>
        <!-- this div help us to center quiz card div -->
        <div class="col-lg-3 col-md-3 bg-white bg-shadow mt-3" style="padding-right:0;"></div>
    </div>

    </body>
<script>
    $(function(){
        $('[data-toggle="tooltip"]').tooltip();
    });

    //this function starts the quiz using ML
    function startWithML(link){
        // Store in session that the user chose to start the quiz using ML
        sessionStorage.setItem("isML", "true");
        // start quiz in mute to make the system hear the choice of the user
        sessionStorage.setItem("isMute", "0");
        window.open("http://localhost/QuizTech/pages/"+link,"_self");
    }
</script>
</html>
