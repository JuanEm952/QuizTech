<?php
include 'serverConnection.php';
session_start();
//For first question, score will not be there.
if(!isset($_SESSION['score'])){
    $_SESSION['score'] = -1;
}
if(!isset($_SESSION['streak'])){
    $_SESSION['streak'] = 0;
}
if(!isset($_SESSION['correctAnswers'])){
    $_SESSION['correctAnswers'] = 0;
}
if(!isset($_SESSION['wrongAnswers'])){
    $_SESSION['wrongAnswers'] = 0;
}
if($_POST){
    $quiz_id = $_POST['quiz_id'];
    $conn = connection();
    //We need total question in process file too
    $query = "SELECT * FROM `Question` WHERE quiz_id='$quiz_id' ";
    $total_questions = mysqli_num_rows(mysqli_query($conn,$query));

    //We need to capture the question number from where form was submitted
    $number = $_POST['number'];
    //Here we are storing the selected option by user
    $selected_choice = $_POST['selectedChoice'];

    //What will be the next question number
    $next = $number+1;

    //Determine the correct choice for current question
    $query = "SELECT * FROM `Question` WHERE quiz_id='$quiz_id' AND question_number = '$number' AND correct_answer = '$selected_choice'";
    $result = mysqli_num_rows(mysqli_query($conn,$query));


    //Increase the score if selected choice is correct
    if($result == 1){
        $timeLeft = $_POST['timeLeft'];
        $streak = $_SESSION['streak'];
        //This percentage help us to assign the points depends on speed of answering, and streak of correct answer
        $percentage = ($streak/10) + ($timeLeft*2);
        $_SESSION['streak']++;
        $_SESSION['correctAnswers']++;
        // 1000 is default amount of points
        $lastScore = $_SESSION['score'];
        $_SESSION['score'] = $lastScore + (800+(500*$percentage));
    }
    else{
        $_SESSION['streak']=0;
        $_SESSION['wrongAnswers']++;
    }
    //Redirect to next question or final score page.
    if($number == $total_questions){
        header("LOCATION: quizStatistics.php?quiz_id=".$quiz_id);
    }else{
        header("LOCATION: quizGame.php?n=". $next ."&quiz_id=".$quiz_id);
    }

}
?>