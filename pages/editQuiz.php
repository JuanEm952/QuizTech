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
        ?>
        <script>
            alert('You dont have permission to edit this quiz')
            window.open('index.php','_self');
        </script>
        <?php
    }
}
// if the user submit to edit the quiz
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $id = $_GET['id'];
    $name= $_POST['quiz_name'];
    $category = $_POST['quizCategory'];
    $imgUrl=$_POST['quizImageUrl'];
    $quizLevel = $_POST['quizLevel'];

    if($_SESSION['logged_email'] == "quiztechjj@gmail.com") {
        //the admin can change number of question and select the type of the quiz (weeklyevent | regualr quiz | Leaderboard quiz)
        $numQuestion = $_POST['num_question'];
        $isWeekly = $_POST['is_weeklyevent'];
        if($isWeekly == 1){
            // if the admin wants to make this quiz as weeklyevent(1)
            $removeOldWeeklyevent = "UPDATE `Quiz` SET `is_weeklyEvent`=0 WHERE `is_weeklyEvent`=1";
            mysqli_query($conn,$removeOldWeeklyevent);
        }
        if($isWeekly == 2){
            // if the admin wants to make this quiz as leaderboard(2)
            $removeOldLeaderboard= "UPDATE `Quiz` SET `is_weeklyEvent`=0 WHERE `is_weeklyEvent`=2";
            mysqli_query($conn,$removeOldLeaderboard);
        }

        $updateQuizSQL = "UPDATE `Quiz` SET `quiz_name`='$name',`quiz_picture`='$imgUrl',`quiz_category`='$category',`quiz_level`='$quizLevel',`num_questions`='$numQuestion',`is_weeklyEvent`='$isWeekly' WHERE quiz_id='$id'";
        if(mysqli_query($conn,$updateQuizSQL)){
            disConnect($conn);?>
            <script>
                alert("Change has been done successfully");
                window.open('adminEditQuizzes.php','_self');
            </script>
        <?php
        }
    }
    else{
        $updateQuizSQL = "UPDATE `Quiz` SET `quiz_name`='$name',`quiz_picture`='$imgUrl',`quiz_category`='$category',`quiz_level`='$quizLevel' WHERE quiz_id='$id'";
        if(mysqli_query($conn,$updateQuizSQL)){
            disConnect($conn);?>
            <script>
                alert("Change has been done successfully");
                window.open('profile.php','_self');
            </script>
        <?php
        }
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Quiz</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/category.css">
    <link rel="stylesheet" href="../css/profile.css">
    <!-- bootstrap javascript library -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <style>
        .leaderboard-img{
            width: 40%;
            padding-left: 0px;
        }
        .img_th{
            width: 15%;
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
<?php if($_SESSION['logged_email'] == "quiztechjj@gmail.com") {
    include('adminNavBar.php');
}else{
    include('navBar.php');
}?>

<?php if($flag){$row = $selectQuizRetval->fetch_assoc() ?>
<div class="d-flex justify-content-center ">
    <h1 class="text-muted">Edit <?php echo htmlspecialchars($row["quiz_name"]); ?></h1>
</div>

<div class="row ml-2 mr-2 d-flex justify-content-center">
    <div class="card border-primary mb-3 col-lg-4 col-md-5 mb-4 correct-item removePadding no_decoration" >
        <div class="card-header bg-primary HeaderText"><?php echo htmlspecialchars($row["quiz_name"]); ?></div>
        <div class="card-body">
            <div class="d-flex justify-content-center">
                <img style="max-height: 300px" src="<?php echo $row["quiz_picture"]; ?>"/>
            </div>
            <p class="mt-3">Number of questions : <?php echo $row["num_questions"]; ?></p>
            <p>Quiz category : <?php echo $row["quiz_category"]; ?></p>
        </div>
    </div>
</div>
<div class="row mt-2 d-flex justify-content-center">
    <div class="card mb-3 col-lg-4 col-md-5 mb-4 " >
        <a href="editQuestion.php?id=<?php echo $row["quiz_id"];?>" type="button" class="btn btn-primary ">Show Question</a>
    </div>
</div>
<form class="container" action="" method="post" id="editQuizForm">
    <!-- hidden input contain quiz image url -->
    <input type="hidden" id="valueImgUrl" name="quizImageUrl" value="<?php echo $row["quiz_picture"];?>" >
    <table class="table table-hover">
        <thead>
        <tr class="table-active">
            <th scope="col">Picture</th>
            <th scope="col">Name</th>
            <th scope="col">Category</th>
            <th scope="col">Quiz Level</th>
            <?php if($_SESSION['logged_email'] == "quiztechjj@gmail.com") {?>
                <th scope="col">Number of questions</th>
                <th scope="col">Is WeeklyEvent Quiz</th>
            <?php }?>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td class="img_th"><img src="<?php echo $row["quiz_picture"];?>" class="img-thumbnail radius-div shadow pointer leaderboard-img"  id="quizImage" data-toggle="modal" data-target="#exampleModal"/></td>
                <td><input class="form-control radius-div border-0 shadow" type="text" value="<?php echo htmlspecialchars($row['quiz_name']); ?>" name="quiz_name"></td>
                <td>
                    <select class="form-control radius-div border-0 shadow" name="quizCategory">
                        <option selected value="<?php echo $row['quiz_category']; ?>"><?php echo $row['quiz_category']; ?> </option>
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
                </td>

                <td>
                    <select class="form-control radius-div border-0 shadow" name="quizLevel">
                        <option selected value="<?php echo $row['quiz_level']; ?> "><?php echo $row['quiz_level']; ?> </option>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </td>
                <?php if($_SESSION['logged_email'] == "quiztechjj@gmail.com") {?>
                    <td><input class="form-control radius-div border-0 shadow" type="number" value="<?php echo $row['num_questions']; ?>" name="num_question"> </td>
                    <td>
                        <select class="form-control radius-div border-0 shadow" name="is_weeklyevent" >
                            <option selected value="<?php echo $row['is_weeklyEvent']; ?> "><?php if($row['is_weeklyEvent'] == 0){echo "Regular Quiz";}elseif ($row['is_weeklyEvent'] == 1){echo "WeeklyEvent Quiz";}else{echo "Leaderboard Quiz";}  ?> </option>
                            <option value="0">Regular Quiz</option>
                            <option value="1">WeeklyEvent Quiz</option>
                            <option value="2">Leaderboard Quiz</option>
                        </select>
                    </td>
                <?php }?>
            </tr>
                <?php }disConnect($conn); ?>
        </tbody>
    </table>
    <div class="mt-4 col text-center">
        <p style="color: red;" id="validErr"></p>
        <a onclick="submitForm()" class="btn btn-primary ">Done</a>
    </div>
</form>

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

    // this function check input validity and submit the form
    function submitForm(){
        var validErr = document.getElementById("validErr");
        var quizName = document.getElementsByName("quiz_name")[0].value;
        var flag = true;
        if(quizName.length < 3){
            validErr.innerHTML = "The quiz name must contain at least 3 characters.";
            flag = false;
        }
        else if(quizName.length > 30){
            validErr.innerHTML = "The quiz name can only contain up to 30 characters.";
            flag = false;
        }
        else{
            validErr.innerHTML = "";
        }
        if(flag){
            document.forms["editQuizForm"].submit();
        }
    }

</script>
</body>
</html>
