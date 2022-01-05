<?php
include('serverConnection.php');
session_start();
if(!$_SESSION['logged_email']){
    header("Location:notUserMessage.php");
}

$conn = connection();
$userEmail = $_SESSION['logged_email'];
$quizId = $_GET['id'];
//get user id
$userIdSql = "SELECT `user_id` FROM `User` WHERE email='$userEmail'";
$retvalID = mysqli_query($conn,$userIdSql)->fetch_assoc();
$user_id = $retvalID["user_id"];

// get user id that created this quiz to check if this user own this quiz
$quizUserIdSQL = "SELECT * FROM `Quiz` WHERE quiz_id='$quizId'";
$quizUserIdRetval = mysqli_query($conn,$quizUserIdSQL)->fetch_assoc();
if(($quizUserIdRetval['user_id'] != $user_id) && $userEmail != "quiztechjj@gmail.com"){
    disConnect($conn);
    header("Location:index.php");
}
if(isset($_GET['id']) && isset($_GET['num'])){
    $questionNumber = $_GET['num'];
    $sql = "DELETE FROM `Question` WHERE question_number='$questionNumber' AND quiz_id='$quizId'";
    mysqli_query($conn,$sql);

    // update the question list and after deleting, sort the question number
    $questionListSQL = "SELECT * FROM `Question` WHERE quiz_id='$quizId' AND question_number > $questionNumber";
    $questionListRetval = mysqli_query($conn,$questionListSQL);
    //if the number of rows are 0 , no need to update question numbers
    if(mysqli_num_rows($questionListRetval) != 0){
        while($question = $questionListRetval->fetch_assoc()){
            $number = $question['question_number'];
            $newNumber = $number-1;
            $updateQuestionNumber = "UPDATE `Question` SET `question_number`='$newNumber' WHERE question_number='$number' AND quiz_id='$quizId'";
            mysqli_query($conn,$updateQuestionNumber);
        }
    }
    // we have to update number of question after deleting
    $getAllQuestionSQL ="SELECT * FROM `Question` WHERE quiz_id='$quizId'";
    $getAllQuestionRetval = mysqli_num_rows(mysqli_query($conn, $getAllQuestionSQL));

    $updateNumberOfQuestionSQL = "UPDATE `Quiz` SET `num_questions`='$getAllQuestionRetval' WHERE quiz_id='$quizId'";
    mysqli_query($conn, $updateNumberOfQuestionSQL);

    disConnect($conn);
    header("Location:editQuestion.php?id=".$quizId);
}
else{
    header("Location:editQuestion.php?id=".$quizId);
}
?>