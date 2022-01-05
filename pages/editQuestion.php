<?php
include('serverConnection.php');
session_start();
$conn = connection();
if(!$_SESSION['logged_email']){
    header("Location:notUserMessage.php");
}
if(!isset($_GET['id'])){
    header("Location:index.php");
}
else{
    // check if Get[quiz_id] exist in the database
    $flag = false;
    $id = $_GET['id'];
    $selectQuizSQL = "SELECT * FROM `Quiz` WHERE quiz_id='$id'";
    $selectQuizRetval = mysqli_query($conn, $selectQuizSQL);
    if (mysqli_num_rows($selectQuizRetval) != 1) {
        disConnect($conn);
        // this header sends the admin to adminEditQuizzes page , and to regular users in adminEditQuizzes sends them to the main page
        header("Location:adminEditQuizzes.php");
    } else {
        $flag = true;
    }
}

if($_SESSION['logged_email'] != "quiztechjj@gmail.com"){
    $userEmail = $_SESSION['logged_email'];
    $quizId = $_GET['id'];
    //get user id
    $userIdSql = "SELECT `user_id` FROM `User` WHERE email='$userEmail'";
    $retvalID = mysqli_query($conn,$userIdSql)->fetch_assoc();
    $user_id = $retvalID["user_id"];

    // get user id that created this quiz to check if this user own this quiz
    $quizUserIdSQL = "SELECT * FROM `Quiz` WHERE quiz_id='$quizId'";
    $quizUserIdRetval = mysqli_query($conn,$quizUserIdSQL)->fetch_assoc();
    if($quizUserIdRetval['user_id'] != $user_id){
        disConnect($conn);
        header("Location:index.php");
    }
}
$quizId = $_GET['id'];
// get all question from the database
$allQuestionSQL = "SELECT * FROM `Question` WHERE quiz_id='$quizId'";
$allQuestionRetval = mysqli_query($conn,$allQuestionSQL);
$quizData = $selectQuizRetval->fetch_assoc();

// if the user searched this query will work
if(isset($_POST['searchTxt']) && isset($_POST['searchBy'])){
    $searchTxt = $_POST['searchTxt'];
    $colName= $_POST['searchBy'];
    $conn = connection();
    $allQuestionSQL = "SELECT * FROM Question WHERE $colName LIKE '%$searchTxt%' AND quiz_id='$quizId'";
    $allQuestionRetval = mysqli_query($conn,$allQuestionSQL);
    if(mysqli_num_rows($selectQuizRetval) > 0){
        $flag = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Quizzes</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
</head>
<body>
<?php if($_SESSION['logged_email'] == "quiztechjj@gmail.com") {
    include('adminNavBar.php');
}else{
    include('navBar.php');
}?>
<div class="d-flex justify-content-center ">
    <h1 class="text-muted">Edit <?php echo $quizData['quiz_name']; ?> Questions</h1>

</div>
<div class="d-flex justify-content-center mt-4 mb-4">
    <form action="" method="post" class="w-25 ">
        <input class="form-control text-center radius-div border-0 shadow" name="searchTxt" type="text" placeholder="Search">
        <select class="form-control text-center radius-div border-0 shadow mt-3" name="searchBy">
            <option value="text">By Question Name</option>
            <option value="time">By Time</option>
            <option value="answer_1">By Answer 1</option>
            <option value="answer_2">By Answer 2</option>
            <option value="answer_3">By Answer 3</option>
            <option value="answer_4">By Answer 4</option>
            <option value="correct_answer ">By Correct answer</option>
        </select>
        <div class="mt-4 mb-2 col text-center">
            <button type="submit"  class="btn btn-primary animate__animated animate__bounce">Search</button>
        </div>
    </form>
</div>
<table class="table table-hover">
    <thead>
    <tr class="table-active">
        <th scope="col">Number</th>
        <th scope="col">Question</th>
        <th scope="col">time</th>
        <th scope="col">Answer 1</th>
        <th scope="col">Answer 2</th>
        <th scope="col">Answer 3</th>
        <th scope="col">Answer 4</th>
        <th scope="col">Correct Answer</th>
        <th scope="col">Edit/Delete</th>
    </tr>
    </thead>
    <tbody>
    <?php if($flag){
        while($row = $allQuestionRetval->fetch_assoc()){
            ?>
            <tr>
                <td><?php echo $row['question_number']; ?> </td>
                <td><?php echo htmlspecialchars($row['text']); ?> </td>
                <td><?php echo $row['time']; ?> </td>
                <td><?php echo htmlspecialchars($row['answer_1']); ?> </td>
                <td><?php echo htmlspecialchars($row['answer_2']); ?> </td>
                <td><?php echo htmlspecialchars($row['answer_3']); ?> </td>
                <td><?php echo htmlspecialchars($row['answer_4']); ?> </td>
                <td><?php echo $row['correct_answer']; ?> </td>
                <td>
                    <a class="btn btn-secondary" href="addQuestion.php?edit=true&quiz_id=<?php echo $quizId; ?>&question_id=<?php echo $row['question_id']; ?>" style="font-weight: bold;">Edit</a>
                    <a class="btn btn-primary" href="deleteQuestion.php?id=<?php echo $row['quiz_id']; ?>&num=<?php echo $row['question_number']; ?>" onclick="return confirm('Are you sure you want to delete the question?')" style="font-weight: bold;">Delete</a>
                </td>
            </tr>
            <?php
        }
    }disConnect($conn); ?>
    </tbody>
</table>
<div class="mt-4 mb-2 col text-center">
    <a href="addQuestion.php?edit=true&quiz_id=<?php echo $quizId; ?>"  class="btn btn-primary animate__animated animate__bounce"><i class="bi bi-plus-circle-fill"></i> Add more question</a>
</div>
<script>

</script>
</body>
</html> 
