<?php
include('serverConnection.php');
include('navBar.php');
$flag = false;
session_start();
if(isset($_POST['search_input']) && strlen($_POST['search_input']) >=3){
    $search = $_POST['search_input'];
    $conn = connection();
    $sql = "SELECT * FROM `Quiz` WHERE quiz_name LIKE '%$search%'";
    $retval = mysqli_query($conn,$sql);
    if(mysqli_num_rows($retval) == 0){
        echo "<div style='margin: 15px;'>Your search did not match any Quiz<br><b>Suggestions:</b><br>Try different keywords. <br>Try more general keywords.</div>";
    }
    else{
        $flag = true;
    }
}
else{ ?>
    <script>
        alert("You have to enter 3 characters minimum!");
        window.open('index.php','_self');
    </script>
<?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Main</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/category.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>
<body>
<!-- nav bar -->
<?php  ?>
<div class="row" style="margin-left:0; margin-right:0;">
    <div class="col-lg-2 col-md-2 bg-white bg-shadow mt-3" style="padding-right:0;"></div>
    <!-- A card gets created according to the users details about the quiz, from the Database -->
    <div class="col-lg-8 col-md-8 mt-5">
        <div class="row ml-2 mr-2 d-flex justify-content-center">
            <?php if($flag){  while($row = $retval->fetch_assoc()) {?>
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
                <?php } }
            disConnect($conn);?>
        </div>
    </div>
    <div class="col-lg-2 col-md-2 bg-white bg-shadow mt-3" style="padding-right:0;"></div>
</div>
</body>
</html>