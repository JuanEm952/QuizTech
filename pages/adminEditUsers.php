<?php
    include('serverConnection.php');
    session_start();
    $flag = false;
    if(!$_SESSION['logged_email']){
        header("Location:notUserMessage.php");
    }
    if($_SESSION['logged_email'] != "quiztechjj@gmail.com"){
        header("Location:index.php");
    }
    // if the admin search work this query
    if(isset($_POST['searchTxt']) && isset($_POST['searchBy'])){
        $searchTxt = $_POST['searchTxt'];
        $colName= $_POST['searchBy'];
        $conn = connection();
        $selectQuizSql = "SELECT * FROM Quiz WHERE $colName LIKE '%$searchTxt%' ";
        $selectQuizRetval = mysqli_query($conn,$selectQuizSql);
        if(mysqli_num_rows($selectQuizRetval) > 0){
            $flag = true;
        }
    }// else the system selects all the quizzes
    else{
        $conn = connection();
        if(isset($_GET['sortBy']) && $_GET['sortBy'] != null){
            $sortBy = $_GET['sortBy'];
            $selectQuizSql = "SELECT * FROM `Quiz` ORDER BY `$sortBy` ASC";
            $selectQuizRetval = mysqli_query($conn,$selectQuizSql);
            if($selectQuizRetval){
                if(mysqli_num_rows($selectQuizRetval) > 0){
                    $flag = true;
                }
            }
        }
        if(!$flag){
            $selectQuizSql = "SELECT * FROM `Quiz`";
            $selectQuizRetval = mysqli_query($conn,$selectQuizSql);
            if($selectQuizRetval) {
                if (mysqli_num_rows($selectQuizRetval) > 0) {
                    $flag = true;
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Quizzes</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
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
<?php include('adminNavBar.php');?>
<div class="d-flex justify-content-center ">
    <h1 class="text-muted">Edit Quizzes</h1>

</div>
<div class="d-flex justify-content-center mt-4 mb-4">
    <form action="" method="post" class="w-25 ">
        <input class="form-control text-center radius-div border-0 shadow" name="searchTxt" type="text" placeholder="Search">
        <select class="form-control text-center radius-div border-0 shadow mt-3" name="searchBy">
            <option value="quiz_name">By Name</option>
            <option value="quiz_id">By Quiz ID</option>
            <option value="quiz_category">By Category</option>
            <option value="num_questions">By Number of questions</option>
                <option value="quiz_level">By Level</option>
            <option value="is_weeklyEvent">By Quiz Type ( 0 | 1 | 2 )</option>
            <option value="user_id ">By User ID</option>
        </select>
        <div class="mt-4 mb-2 col text-center">
            <button type="submit"  class="btn btn-primary animate__animated animate__bounce">Search</button>
        </div>
    </form>
</div>
<table class="table table-hover">
    <thead>
    <tr class="table-active">
       <th scope="col"><a href="adminEditQuizzes.php?sortBy=quiz_id">ID</a></th>
        <th scope="col">Picture</th>
        <th scope="col"><a href="adminEditQuizzes.php?sortBy=quiz_name">Name</a></th>
        <th scope="col"><a href="adminEditQuizzes.php?sortBy=quiz_category">Category</a></th>
        <th scope="col"><a href="adminEditQuizzes.php?sortBy=num_questions">Number of questions</a></th>
        <th scope="col"><a href="adminEditQuizzes.php?sortBy=quiz_level">Quiz Level</a></th>
        <th scope="col"><a href="adminEditQuizzes.php?sortBy=is_weeklyEvent">Is WeeklyEvent Quiz</a></th>
        <th scope="col"><a href="adminEditQuizzes.php?sortBy=user_id">User ID</a></th>
        <th scope="col">Edit/Delete</th>
    </tr>
    </thead>
    <tbody>
    <?php if($flag){
        while($row = $selectQuizRetval->fetch_assoc()){
          ?>
    <tr>
        <td><?php echo $row['quiz_id']; ?> </td>
        <td class="img_th"><img class="leaderboard-img" src="<?php echo $row["quiz_picture"];?>"/> </td>
        <td><?php echo htmlspecialchars($row['quiz_name']); ?> </td>
        <td><?php echo $row['quiz_category']; ?> </td>
        <td><?php echo $row['num_questions']; ?> </td>
        <td><?php echo $row['quiz_level']; ?> </td>
        <td><?php echo $row['is_weeklyEvent']; ?> </td>
        <td><?php echo $row['user_id']; ?> </td>
        <td>
            <a class="btn btn-secondary" href="editQuiz.php?id=<?php echo $row['quiz_id']; ?>" style="font-weight: bold;">Edit</a>
            <a class="btn btn-primary" href="deleteQuiz.php?id=<?php echo $row['quiz_id']; ?>" onclick="return confirm('Are you sure you want to delete the quiz?')" style="font-weight: bold;">Delete</a>
        </td>
    </tr>
    <?php
        }
    }disConnect($conn); ?>
    </tbody>
</table>

<script>

</script>
</body>
</html>
