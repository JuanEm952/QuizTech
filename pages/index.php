<?php
include('serverConnection.php');
session_start();
$isUser = true;
$flag = false;
$leadFlag = false;

if(!isset($_SESSION['logged_email'])){
    $isUser = false;
}
$conn = connection();
if($isUser){
    $userEmail = $_SESSION['logged_email'];
    //get user level and id
    $userLevelAndIDSql = "SELECT * FROM `User` WHERE email='$userEmail'";
    $userLevelAndIDRetval = mysqli_query($conn,$userLevelAndIDSql)->fetch_assoc();
    $userLevelAvg = $userLevelAndIDRetval["level_average"];
    $user_id = $userLevelAndIDRetval["user_id"];
    $userLevel="";
    if($userLevelAvg >= 0 && $userLevelAvg <= 50){
        $userLevel = "easy";
    }
    elseif ($userLevelAvg >= 51 && $userLevelAvg < 80){
        $userLevel = "medium";
    }
    else{
        $userLevel = "hard";
    }
    //get 4 random quizzes that have more than 2 questions and the user didn't played before and the quiz level is matches for the user level
    $rndQuizSql = "SELECT * FROM Quiz WHERE num_questions >= 3 && quiz_level='$userLevel' ORDER BY RAND()";
    $alreadyPlayedQuizzesID = "SELECT quiz_id FROM `userquizconn` WHERE user_id = '$user_id'";
    //chose only 4 quizzes from unplayed quizzes
    $rndQuizRetval = mysqli_query($conn,$rndQuizSql);
    $alreadyPlayedQuizzesRetVal = mysqli_query($conn, $alreadyPlayedQuizzesID);

}
else{
   //if the user is not logged in, the system will display 4 random quizzes
   $rndQuizSql = "SELECT * FROM Quiz WHERE num_questions >= 3 ORDER BY RAND() LIMIT 4";
    $rndQuizRetval = mysqli_query($conn,$rndQuizSql);
}

if(mysqli_num_rows($rndQuizRetval) > 0){
    $flag = true;
}
//Get the quiz_id of leaderboard of the previous weekly event
$prevWeeklyEventQuizIdSQL="SELECT `quiz_id` FROM `Quiz` WHERE is_weeklyEvent=2";
$revWeeklyEventQuizRetval = mysqli_query($conn,$prevWeeklyEventQuizIdSQL)->fetch_assoc();
$leaderboard_quiz_id="";
if($revWeeklyEventQuizRetval){
    $leaderboard_quiz_id = $revWeeklyEventQuizRetval['quiz_id'];
}
//Get the quiz_id of the current weekly event
$weeklyEventQuizIdSQL="SELECT `quiz_id` FROM `Quiz` WHERE is_weeklyEvent=1";
$weeklyEventQuizRetval = mysqli_query($conn,$weeklyEventQuizIdSQL)->fetch_assoc();

if($weeklyEventQuizRetval){
    $quiz_id = $weeklyEventQuizRetval['quiz_id'];
}
else{
    // if no weeklyevent in database users will play default quiz
    $quiz_id=1;
}


// Sort leaderboard by users with the most points to a specific quiz.
$leaderBoardSql = "SELECT * FROM Statistics WHERE quiz_id='$leaderboard_quiz_id' ORDER BY points Desc limit 10";
$leaderBoardRetval = mysqli_query($conn,$leaderBoardSql);
if(mysqli_num_rows($leaderBoardRetval)>0){
    $leadFlag = true;
}

if(isset($_POST['updateLeaderboard']) && $_SESSION['logged_email'] == "quiztechjj@gmail.com"){
    $changeLeaderbaordSQL1 = "UPDATE `Quiz` SET `is_weeklyEvent`=0 WHERE `is_weeklyEvent`=2";
    $changeLeaderbaordSQL2 = "UPDATE `Quiz` SET `is_weeklyEvent`=2 WHERE `is_weeklyEvent`=1";
    mysqli_query($conn,$changeLeaderbaordSQL1);
    mysqli_query($conn,$changeLeaderbaordSQL2);
    header("Location:index.php");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Main</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/category.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <style>

        .leaderboard-img{
            width: 60%;
            padding-left: 0px;
        }
        th{
            width: 25%;
        }
        #countholder{
            font-family: sans-serif;
            color: #fff;
            display: inline-block;
            font-weight: 100;
            text-align: center;
            font-size: 30px;
            background-color: #fff;
            padding:20px;
            border-radius: 6px;
            line-height: 1.4;
        }

        #countholder > div{
            padding: 4px 10px;
            border-radius: 3px;
            background: #eb6864;
            display: inline-block;
        }

        #countholder div > span{
            border-radius: 3px;
            background: #eb6864;
            display: inline-block;
        }

        #countholder .smalltext{
            padding-top: 5px;
            font-size: 16px;
        }
        .margin-top{
            margin-top: 25vh;
            margin-bottom: 25vh;
        }
    </style>
</head>
<body>
<?php
if($isUser && $_SESSION['logged_email'] == "quiztechjj@gmail.com"){
    include('adminNavBar.php');
}
else{
    include('navBar.php');
}?>

<!--  div displays the quiz details -->
<div class="d-flex justify-content-center ">
    <h1 class="text-muted">Main</h1>
</div>
<div class="row" style="margin-left:0; margin-right:0;">
    <div class="col-lg-3 col-md-3 bg-white bg-shadow mt-3" style="padding-right:0;">
        <div class="list-group d-flex justify-content-center ">

            <table class="table table-hover">
                <span class="d-flex justify-content-center ">
                    <h1 class="text-muted">Leaderboard</h1>
                </span>
                <tbody>
                <?php
                if($leadFlag){
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
            <?php if($isUser && $_SESSION['logged_email'] == "quiztechjj@gmail.com"){ ?>
            <form class="mt-4 mb-2 col text-center" action="" method="post" id="updateLeaderboardForm">
                <input hidden name="updateLeaderboard">
                <a onclick="submitUpdate()" class="btn btn-primary" >Update Leaderboard</a>
            </form>
            <?php } ?>
        </div>
    </div>
    <!-- Div displays the quiz's questions and answers -->
    <div class="col-lg-6 col-md-6 mt-5">
        <?php if ($isUser){ ?>
        <p class="text-muted align-content-start" style="margin-left:17% ;">Quizzes Level : <?php echo $userLevel; ?></p>
        <?php } ?>
        <div class="row ml-2 mr-2 d-flex justify-content-center">
            <div class="w-100"></div>

            <?php
             if($flag){ if($isUser){
                 // this algorithm works only if the user is logged_in to display the quizzes that doesn't play before
                 $numberChoseQuizzes=0;
                 while($rndQuiz = $rndQuizRetval->fetch_assoc()) {
                     if($numberChoseQuizzes == 4){
                         break;
                     }
                     $isPlayedQuiz= false;
                     while($playedQuiz = $alreadyPlayedQuizzesRetVal->fetch_assoc()){
                         if($rndQuiz["quiz_id"] == $playedQuiz["quiz_id"]){
                             $isPlayedQuiz = true;
                         }
                     }
                     if(!$isPlayedQuiz){?>
                         <!-- Quiz Card -->
                         <a href="quizPage.php?quiz_id=<?php echo $rndQuiz["quiz_id"]; ?>" class="card border-primary mb-3 col-lg-4 col-md-5 mb-4 correct-item removePadding pointer ml-4 no_decoration" style="max-width: 20rem;" >
                             <div class="card-header bg-primary HeaderText"><?php echo htmlspecialchars($rndQuiz["quiz_name"]); ?></div>
                             <div class="card-body">
                                 <div class="d-flex justify-content-center">
                                     <img class="w-75" src="<?php echo $rndQuiz["quiz_picture"]; ?>"/>
                                 </div>
                                 <p class="mt-3">Number of questions : <?php echo $rndQuiz["num_questions"]; ?></p>
                                 <p>Quiz category : <?php echo $rndQuiz["quiz_category"]; ?></p>
                             </div>
                         </a>
                         <?php
                         $numberChoseQuizzes++;
                     }
                 }
             }else{ while($row = $rndQuizRetval->fetch_assoc()) {?>
                    <!-- Quiz Card -->
                    <a href="quizPage.php?quiz_id=<?php echo $row["quiz_id"]; ?>" class="card border-primary mb-3 col-lg-4 col-md-5 mb-4 correct-item removePadding pointer ml-4 no_decoration" style="max-width: 20rem;" >
                        <div class="card-header bg-primary HeaderText"><?php echo htmlspecialchars($row["quiz_name"]); ?></div>
                        <div class="card-body">
                            <div class="d-flex justify-content-center">
                                <img class="w-75" src="<?php echo $row["quiz_picture"]; ?>"/>
                            </div>
                            <p class="mt-3">Number of questions : <?php echo $row["num_questions"]; ?></p>
                            <p>Quiz category : <?php echo $row["quiz_category"]; ?></p>
                        </div>
                    </a>
                <?php } }}
            disConnect($conn);?>
        </div>
        <div class="mb-3"></div>
    </div>
    <!-- countdown timer -->
    <div class="col-lg-3 col-md-3 bg-white bg-shadow mt-3" style="padding-right:0;">
        <div class="rounded bg-gradient-4 text-white shadow  text-center margin-top">
            <h2 class="text-muted">Weekly Event</h2>
            <div id="countholder">
                <div><span class="days" id="days"></span><div class="smalltext">Days</div></div>
                <div><span class="hours" id="hours"></span><div class="smalltext">Hours</div></div>
                <div><span class="minutes" id="minutes"></span><div class="smalltext">Minutes</div></div>
                <div><span class="seconds" id="seconds"></span><div class="smalltext">Seconds</div></div>
            </div>
            <div class="w-100"></div>
            <button id="startQuizBtn" type="button" onclick="startWeeklyEventQuiz()" class="btn btn-primary mb-3"><img src="../images/play.svg" width="25"> Start Quiz</button>
        </div>
    </div>
</div>

<script>
    var curday;
    var secTime;
    var ticker;
    var flag;

    function getSeconds() {
        var nowDate = new Date();
        var dy = 6; //saturday through Saturday, 0 to 6
        var countertime = new Date(nowDate.getFullYear(),nowDate.getMonth(),nowDate.getDate(),20,0,0); //saturday at 8:00 pm

        var curtime = nowDate.getTime(); //current time
        var atime = countertime.getTime(); //countdown time
        var diff = parseInt((atime - curtime)/1000);
        if (diff > 0) { curday = dy - nowDate.getDay() }
        else { curday = dy - nowDate.getDay() -1 } //after countdown time
        if (curday < 0) { curday += 7; } //already after countdown time, switch to next week
        if (diff <= 0) { diff += (86400 * 7) }
        startTimer (diff);
    }
    function startTimer(secs) {
        secTime = parseInt(secs);
        ticker = setInterval("tick()",1000);
        tick(); //initial count display
    }
    function tick() {
        var secs = secTime;
        if (secs>0) {
            secTime--;
        }
        else {
            clearInterval(ticker);
            getSeconds(); //start over
        }
        secs %= 86400;
        var hours= Math.floor(secs/3600);
        secs %= 3600;
        var mins = Math.floor(secs/60);
        secs %= 60;
        flag = sessionStorage.getItem("flag");

        if(curday !== 6 || hours < 22 ){
            sessionStorage.setItem("flag","false");
        }
        else if(curday === 6 && hours > 21 ){
            sessionStorage.setItem("flag","true");
        }
        if(curday === 0 && hours === 0 && mins === 0 && secs === 0){
            document.getElementById("startQuizBtn").disabled = false;
            sessionStorage.setItem("flag","true");
        }
        if(flag === "false"){
            document.getElementById("startQuizBtn").disabled = true;
        }
        else{
            document.getElementById("startQuizBtn").disabled = false;
        }
        document.getElementById("days").innerHTML = curday;
        document.getElementById("hours").innerHTML = ((hours < 10 ) ? "0" : "" ) + hours;
        document.getElementById("minutes").innerHTML = ( (mins < 10) ? "0" : "" ) + mins;
        document.getElementById("seconds").innerHTML = ( (secs < 10) ? "0" : "" ) + secs;
    }
    $( document ).ready(function() {
        getSeconds();
    });

    function startWeeklyEventQuiz(){
        window.open('quizGame.php?n=1&quiz_id=<?php echo $quiz_id;?>','_self');
    }
    function submitUpdate(){
        var isConfirmed = confirm("Are you sure you want to update the Leaderboard ?");
        if(isConfirmed === true){
            document.forms['updateLeaderboardForm'].submit();
        }
    }
</script>
</body>
</html>
