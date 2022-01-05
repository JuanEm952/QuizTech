<?php
include('serverConnection.php');
session_start();

if(!$_SESSION['logged_email']){
    header("Location:notUserMessage.php");
}

if((!$_SESSION['score']) || (!isset($_GET['quiz_id']))){
    header("Location:categories.php");
}
else {
    $flag = false;
    $conn = connection();
    $userEmail = $_SESSION['logged_email'];
    $quiz_id = $_GET['quiz_id'];
    $wrongAnswer = $_SESSION['wrongAnswers'];
    if( $_SESSION['score'] == -1){
        $score = 0;
        $correctAnswer=0;
    }
    else{
        $score = $_SESSION['score'];
        $correctAnswer = $_SESSION['correctAnswers'];
    }
    $userIdSql = "SELECT `user_id` FROM `User` WHERE email='$userEmail'";
    $retvalID = mysqli_query($conn,$userIdSql)->fetch_assoc();
    $user_id = $retvalID["user_id"];

    // check if the user has played this quiz before
    $userIsPlayedQuizSql = "SELECT * FROM `UserQuizConn` WHERE user_id='$user_id' AND quiz_id='$quiz_id'";
    $userIsPlayedQuizRetval = mysqli_query($conn,$userIsPlayedQuizSql);// if this return 0 that mean the user hasn't played the quiz

    // Algorithm that checks if the user has played this quiz before if so the quiz points will not count to level and statistics
    if(mysqli_num_rows($userIsPlayedQuizRetval) == 0){
        $userHasPlayedTheQuizSql = "INSERT INTO `UserQuizConn`(`user_id`, `quiz_id`) VALUES ('$user_id','$quiz_id')";
        mysqli_query($conn,$userHasPlayedTheQuizSql);

        $insertSql = "INSERT INTO `Statistics`(`points`, `correct_answer`, `wrong_answer`, `quiz_id`, `user_id`) VALUES ('$score','$correctAnswer','$wrongAnswer','$quiz_id','$user_id')";
        mysqli_query($conn,$insertSql);

        //return from database number of played quiz
        $getNumPlayedSql = "SELECT * FROM `User` WHERE email='$userEmail'";
        $getNumPlayedRetval = mysqli_query($conn, $getNumPlayedSql)->fetch_assoc();
        $numQuiz = $getNumPlayedRetval['num_played_quizzes']+1;
        // update number of played quiz in user table
        $numPlayedSql = "UPDATE `User` SET `num_played_quizzes`='$numQuiz' WHERE email='$userEmail'";
        mysqli_query($conn, $numPlayedSql);
        //this algorithm calculate the level of the user by percentage of correct answer in a specific quiz and makes an average of all played quizzes
        $numberOfQuestion = $correctAnswer+$wrongAnswer;
        $currentQuizLevel = ($correctAnswer/$numberOfQuestion)*100;
        $userLevelAverage =  $getNumPlayedRetval['level_average'];
        $numPlayedBefore = $numQuiz -1;
        $sumOfAllQuizLevel = (($userLevelAverage * $numPlayedBefore) + $currentQuizLevel)/$numQuiz;
        $updateUserLevel = "UPDATE `User` SET `level_average`=$sumOfAllQuizLevel WHERE email='$userEmail'";
        mysqli_query($conn, $updateUserLevel);
    }
    $leaderBoardSql = "SELECT * FROM Statistics WHERE quiz_id='$quiz_id' ORDER BY points Desc limit 10";
    $leaderBoardRetval = mysqli_query($conn,$leaderBoardSql);
    if(mysqli_num_rows($leaderBoardRetval) > 0){
        $flag = true;
    }
    //return from database number of played quiz for user profile
    $getNumPlayedProfileSql = "SELECT * FROM `User` WHERE email='$userEmail'";
    $getNumPlayedProfileRetval = mysqli_query($conn, $getNumPlayedProfileSql)->fetch_assoc();
    $numQuizProfile = $getNumPlayedProfileRetval['num_played_quiz_profile']+1;

    //update number of played quiz for user profile
    $numPlayedProfileSql = "UPDATE `User` SET `num_played_quiz_profile`='$numQuizProfile' WHERE email='$userEmail'";
    mysqli_query($conn, $numPlayedProfileSql);

    //get average of users points for current quiz
    $avgUsersPointsSql = "SELECT AVG(points) FROM `Statistics` WHERE quiz_id='$quiz_id'";
    $avgUsersPointsRetval = mysqli_query($conn, $avgUsersPointsSql)->fetch_assoc();
    $avgPoints = $avgUsersPointsRetval['AVG(points)'];
}
?>

<html>
<head>
    <title>Statistics</title>
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.css">
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
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);
        google.charts.load('current', {'packages':['bar']});
        google.charts.setOnLoadCallback(drawChart2);
        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Task', 'Hours per Day'],
                ['Correct answer',      <?php echo $correctAnswer; ?> ],
                ['Wrong answer',     <?php echo $wrongAnswer; ?>]
            ]);
            var options = {
                title: ''
            };
            var chart = new google.visualization.PieChart(document.getElementById('piechart'));
            chart.draw(data, options);
        }
        function drawChart2() {
            var data2 = google.visualization.arrayToDataTable([
                [' ', 'Players Average Points', 'Your Points'],
                [' ', <?php echo $avgPoints; ?>,  <?php echo ceil($score); ?>]
            ]);
            var options2 = {
                chart: {
                    title: ''
                }
            };
            var chart2 = new google.charts.Bar(document.getElementById('columnchart_material'));
            chart2.draw(data2, google.charts.Bar.convertOptions(options2));
        }
    </script>
</head>
<body>
<!-- background finish sound effect -->
<audio autoplay><source src="../soundEffect/crash.m4a"></audio>
<?php include('navBar.php');?>
<div class="row" style="margin-left:0; margin-right:0;">
    <!-- div for text result -->
    <div class="col-lg-3 col-md-3 bg-white bg-shadow mt-3" style="padding-right:0;">
        <div class="container">
            <h2>Your Result</h2>
            <p>Your <strong>Score</strong> is <?php echo ceil($score); ?>  </p>
            <p>Your <strong>Correct Answer</strong> is <?php echo $correctAnswer; ?>  </p>
            <p>Your <strong>Wrong Answer</strong> is <?php echo $wrongAnswer; ?>  </p>
            <?php
              unset($_SESSION['score']);
              unset($_SESSION['streak']);
              unset($_SESSION['correctAnswers']);
              unset($_SESSION['wrongAnswers']);
            ?>
        </div>
    </div>
    <!-- Div for the user's final quiz results after completing the quiz -->
    <div class="col-lg-5 col-md-5 mt-5">
        <div class="row ml-2 mr-2 d-flex justify-content-center">
            <h4>Percentage of users average points to your points</h4>
            <div id="columnchart_material" style="width: 800px; height: 500px;"></div>
            <h4 class="mt-2">Wrong/Correct Answer</h4>
            <div id="piechart" style="width: 900px; height: 500px;"></div>
        </div>
    </div>
    <!-- div for leaderboard -->
    <div class="col-lg-4 col-md-4 bg-white bg-shadow mt-3" style="padding-right:0;">
        <div class="list-group d-flex justify-content-center ">
            <table class="table table-hover">
                <?php if($flag){ ?>
                <div class="d-flex justify-content-center ">
                    <h1 class="text-muted">Leaderboard</h1>
                </div>
                <tbody>
                <!-- Div displays leaderboard details for each quiz -->
                <?php
                while($row = $leaderBoardRetval->fetch_assoc()) {
                    $currentUserId = $row['user_id'];
                    $leaderBoardUserSql = "SELECT * FROM `User` WHERE user_id='$currentUserId'";
                    $userRetval = mysqli_query($conn,$leaderBoardUserSql);
                    $userRow = $userRetval->fetch_assoc();
                    ?>
                    <tr>
                        <th class="ml-0"><img class="leaderboard-img" src="<?php echo $userRow["picture_link"];?>"/></th>
                        <td class="ml-0">
                            <h4 class="d-flex justify-content-center"><?php echo htmlspecialchars($userRow["full_name"]);?> </h4>
                            <span class="d-flex justify-content-center"> <?php echo $row["points"]; ?>pts</span>
                        </td>
                    </tr>
                <?php }}
                disConnect($conn);?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    //
    sessionStorage.removeItem("isML");
    //when back button clicked redirect to category page
    window.onload = function() {
        window.history.pushState({page: 1}, "", "");
        window.onpopstate = function (event) {
            if (event) {
                window.location.href = 'categories.php';
                // Code to handle back button or prevent from navigation
            }
        }
    };
</script>
</body>
</html>