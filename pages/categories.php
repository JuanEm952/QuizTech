<?php
include('serverConnection.php');

session_start();
$categorySelected = "";
$isUser = false;
$flag = false;

    if(isset($_SESSION['logged_email'])){
        $isUser = true;
    }
    //default quiz when a user not logged in
    else{
        $categorySelected = "Art";
    }
    $conn = connection();
    $sql = "SELECT * FROM `Quiz`";
    $quiz = mysqli_query($conn,$sql);
    if(!$quiz){
        disConnect($conn);
    }
    else{
        $flag = true;
    }
    if(isset($_GET['category'])){
        $categorySelected = $_GET['category'];
    }
    else{
        // if the user logged in show him quizzes from favorite category
        if($isUser){
            $conn = connection();
            $userEmail = $_SESSION['logged_email'];

            $fav_category_sql = "SELECT `favorite_category` FROM `User` WHERE email='$userEmail'";
            $retvalCategory = mysqli_query($conn,$fav_category_sql);
            $fav_category = $retvalCategory->fetch_assoc();
            $categorySelected = $fav_category["favorite_category"];
        }
    }
?>
<html>
<head>
    <title>Categories</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/category.css">
</head>
<body>
<?php include('navBar.php');?>
<div class="d-flex justify-content-center ">
    <h1 class="text-muted">Categories</h1>
</div>
<div class="row" style="margin-left:0; margin-right:0;">
    <div class="col-lg-3 col-md-3 bg-white bg-shadow mt-3" style="padding-right:0;">
        <div class="list-group d-flex justify-content-center ">
            <a href="categories.php?category=Art" class="text-center list-group-item list-group-item-action">Art</a>
            <a href="categories.php?category=Computer" class="text-center list-group-item list-group-item-action">Computer</a>
            <a href="categories.php?category=Design" class="text-center list-group-item list-group-item-action">Design</a>
            <a href="categories.php?category=Education" class="text-center list-group-item list-group-item-action">Education</a>
            <a href="categories.php?category=For Kids" class="text-center list-group-item list-group-item-action">For Kids</a>
            <a href="categories.php?category=History" class="text-center list-group-item list-group-item-action">History</a>
            <a href="categories.php?category=Just For Fun" class="text-center list-group-item list-group-item-action">Just For Fun</a>
            <a href="categories.php?category=Language" class="text-center list-group-item list-group-item-action">Language</a>
            <a href="categories.php?category=Movies" class="text-center list-group-item list-group-item-action">Movies</a>
            <a href="categories.php?category=Music" class="text-center list-group-item list-group-item-action">Music</a>
            <a href="categories.php?category=Programming Language" class="text-center list-group-item list-group-item-action">Programming Language</a>
            <a href="categories.php?category=Sports" class="text-center list-group-item list-group-item-action">Sports</a>
            <a href="categories.php?category=Other" class="text-center list-group-item list-group-item-action">Other</a>
        </div>
    </div>
    <!-- A card gets created according to the users details about the quiz, from the Database -->
    <div class="col-lg-9 col-md-9 mt-5">
        <div class="row ml-2 mr-2 d-flex justify-content-start">
            <?php if($flag){  while($row = $quiz->fetch_assoc()) {
                if(htmlspecialchars($row["quiz_category"]) == $categorySelected && htmlspecialchars($row["num_questions"]) >= 3){?>
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
    </div>
</div>
</body>
</html>