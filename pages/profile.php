<?php
    include('serverConnection.php');
    session_start();
    if(!$_SESSION['logged_email']){
        header("Location:notUserMessage.php");
    }
    else {
        $ind=0;
        $conn = connection();
        $userEmail = $_SESSION['logged_email'];

        //get user level
        $userLevelSql = "SELECT `level_average` FROM `User` WHERE email='$userEmail'";
        $userLevelRetval = mysqli_query($conn,$userLevelSql)->fetch_assoc();
        $userLevelAvg = $userLevelRetval["level_average"];
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

        $sql = "SELECT * FROM `User` WHERE email='$userEmail' limit 1";
        $retval = mysqli_query($conn, $sql);
        if (!$retval) {
            header("Location:notUserMessage.php");
        }
        else{
            $userInfo = $retval->fetch_assoc();
        }
        //get the number of created quiz for current user
        $getNumCreatedSql = "SELECT `num_created_quizzes` FROM `User` WHERE email='$userEmail'";
        $getNumCreatedRetval = mysqli_query($conn, $getNumCreatedSql)->fetch_assoc();
        $numCreatedQuiz = $getNumCreatedRetval['num_created_quizzes'];

        //get user id
        $userIdSql = "SELECT `user_id` FROM `User` WHERE email='$userEmail'";
        $retvalID = mysqli_query($conn,$userIdSql)->fetch_assoc();
        $user_id = $retvalID["user_id"];

        //get highest score for current user
        $highScoreSql = "SELECT `points` FROM `Statistics` WHERE user_id='$user_id' ORDER BY points Desc limit 1";
        $highScoreRetval = mysqli_query($conn,$highScoreSql)->fetch_assoc();
        $highestScore="";
        if($highScoreRetval) {
            $highestScore = $highScoreRetval["points"];
        }

        //return from database number of played quiz
        $getNumPlayedSql = "SELECT `num_played_quiz_profile` FROM `User` WHERE email='$userEmail'";
        $getNumPlayedRetval = mysqli_query($conn, $getNumPlayedSql)->fetch_assoc();
        $numQuiz = $getNumPlayedRetval['num_played_quiz_profile'];

        //this query gets from database all the quizzes that the current user created
        $createQuizSQL="SELECT * FROM `Quiz` WHERE user_id='$user_id'";
        $createQuizRetval = mysqli_query($conn, $createQuizSQL);
        if(mysqli_num_rows($createQuizRetval) == 0){?>
            <script>
               var buttonFlag = true;
            </script>
        <?php
        }
        else{?>
            <script>
                var buttonFlag = false;
            </script>
        <?php
        }
        disConnect($conn);
    }
    //Check if the user changed his profile picture, if so, then the user's profile picture in the database gets updated!
    if(isset($_POST['newImageLink']) && $_POST['newImageLink'] != null){
        $conn = connection();
        $userEmail = $_SESSION['logged_email'];
        if(isset($_POST['newImageLink'])) {
            $newImageLink = $_POST['newImageLink'];
            $editImageSql = "UPDATE `User` SET `picture_link`='$newImageLink' WHERE email='$userEmail'";
            $retval = mysqli_query($conn, $editImageSql);
        }
        disConnect($conn);
        header("Location:profile.php");
    }
    // Updates the user's name in the database in case the user decided to change his/her name.
    if(isset($_POST['newName'])){
        $conn = connection();
        $userEmail = $_SESSION['logged_email'];
        $newName = $_POST['newName'];
        if(strlen($newName) > 2 && strlen($newName) < 21){
            $editNameSql = "UPDATE `User` SET `full_name`='$newName' WHERE email='$userEmail'";
            $retval = mysqli_query($conn, $editNameSql);
        }
        else {
            echo "<script>confirm('Your full name must contain more than 3 letters!');</script>"; // TODO IMPORTANT
        }
        disConnect($conn);
        header("Location:profile.php");
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/profile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
</head>
<body class="body">
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
            <!-- The user gets to choose any avatar from the options below -->
            <div class="modal-body">
                <form action="" method="post" id="imageUrlForm">
                    <div class="form-group">
                        <label for="message-text" class="col-form-label">Image URL:</label>
                        <select type="url" class="form-control text-center radius-div border-0 shadow" onchange="addModalImage()" name="newImageLink" id="imageUrl">
                            <option value="" selected>Select Avatar</option>
                            <option value="http://localhost/QuizTech/images/avatars/Male1.svg">Male | Short hair | Brown beard | Glasses | Proud</option>
                            <option value="http://localhost/QuizTech/images/avatars/Male2.svg">Male | Short hair | No beard | Glasses | Happy</option>
                            <option value="http://localhost/QuizTech/images/avatars/Male3.svg">Male | Pink hair | No beard | Glasses | Crazy</option>
                            <option value="http://localhost/QuizTech/images/avatars/Male4.svg">Male | Bown hair | Short beard | Glasses | Tired</option>
                            <option value="http://localhost/QuizTech/images/avatars/Male5.svg">Male | Hat | White long beard | Glasses | Old but gold</option>
                            <option value="http://localhost/QuizTech/images/avatars/Male6.svg">Male | Pink hair | Red beard | no Glasses | Amazed</option>
                            <option value="http://localhost/QuizTech/images/avatars/Male7.svg">Male | Brown hair | black mustache  | no Glasses | Friendly</option>
                            <option value="http://localhost/QuizTech/images/avatars/Male8.svg">Male | Light Pink | Red mustache | no Glasses | Surprised</option>
                            <option value="http://localhost/QuizTech/images/avatars/Female1.svg">Female | Long hair | Elegant | Happy</option>
                            <option value="http://localhost/QuizTech/images/avatars/Female2.svg">Female | Long white hair | Glasses | Friendly</option>
                            <option value="http://localhost/QuizTech/images/avatars/Female3.svg">Female | Short hair | Glasses | Annoyed</option>
                            <option value="http://localhost/QuizTech/images/avatars/Female4.svg">Female | Long brown hair | Glasses | Nerdy</option>
                            <option value="http://localhost/QuizTech/images/avatars/Female5.svg">Female | Medium blonde hair | Glasses | Crazy</option>
                            <option value="http://localhost/QuizTech/images/avatars/Female6.svg">Female | Blonde hair | no Glasses | Friendly</option>
                            <option value="http://localhost/QuizTech/images/avatars/Female7.svg">Female | Black long hair | Glasses | Happy</option>
                            <option value="http://localhost/QuizTech/images/avatars/Female8.svg">Female | Gray Long hair | no Glasses | Crazy</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="d-flex justify-content-center">
                <img src="../images/user_avatar.svg" class="radius-div userAvatarImg pointer" id="modalImage"/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" onclick="addImageUrl()" data-dismiss="modal" class="btn btn-primary">Add Image</button>
            </div>
        </div>
    </div>
</div>
<?php
if($_SESSION['logged_email'] == "quiztechjj@gmail.com"){
    include('adminNavBar.php');
}
else{
    include('navBar.php');
}?>
  <div class="row h-100" style="margin-left:0; margin-right:0;">
      <!-- user information part -->

      <div class="col-lg-6 col-md-6 bg-primary bg-shadow" style="padding-right:0;">

          <div class="float-right mt-2">
              <button type="button" class="btn btn-light btnRadius btn-block" onclick="onEdit()" id="editBtn" >Edit</button>
          </div>
          <div class="d-flex justify-content-center">
              <div class="text-white d-inline-block w-75">
                  <div class="w-100 mt-5" ></div>
                  <div class=" d-flex justify-content-center ">
                      <img src="<?php echo $userInfo["picture_link"]?>" class="radius-div userAvatarImg pointer" id="quizImage" data-toggle="modal" data-target="#exampleModal"/>

                  </div>
                  <p class="mt-3">Level : <?php echo $userLevel; ?></p>
                  <form action="" id="nameForm" method="post" class="mt-3 ">
                      <input class= "form-control text-center border-0 shadow animate__animated fullNameDisable" name="newName" id="fullNameInput" type="text" placeholder="FullName" value="<?php echo htmlspecialchars($userInfo["full_name"]) ;?>">
                      <div class="w-100 mt-4" ></div>
                      <input class="form-control text-center border-0 shadow font-weight-bold disabledInput" disabled type="text" value="<?php echo $userInfo["email"] ;?>">
                      <div class="w-100 mt-4"></div>
                      <input class="form-control text-center border-0 shadow font-weight-bold disabledInput" disabled type="text" value="<?php echo $userInfo["birthday"] ;?>">
                      <div class="w-100 mt-4"></div>
                      <button id="doneBtn" name="editBtn" onclick="submitEdits()" class="btn btn-light ">Done</button>
                      <a href="logout.php" class="btn btn-light" id="logOutBtn">Log Out</a>
                  </form>
              </div>
          </div>
      </div>
      <!-- statistics part -->
      <div class="col-lg-6 col-md-6  myTestDiv">
          <div class="">
              <div class="text-white bg-primary radius-div shadow p-2 mt-3 mb-3">
                  <div class="d-flex justify-content-center">
                      <p class="f-1 ">Statistics</p>
                  </div>
                  <p class="ml-2 f-2">Played Quizzes: <?php echo $numQuiz;?></p>
                  <p class="ml-2 f-2">Highest Score: <?php echo $highestScore;?></p>
                  <p class="ml-2 f-2">Quizzes you Created: <?php echo $numCreatedQuiz;?></p>
              </div>
          </div>
          <!--all quizzes user created-->
          <div id="multi-item-example" class="carousel slide carousel-multi-item editQuizDiv" data-ride="carousel">

              <!--Controls-->
              <div id="hideControlButtons" class="controls-top d-flex justify-content-center mb-2" >
                  <a class="btn-floating mr-2" href="#multi-item-example" data-slide="prev"><img style="width: 50px;" src="../images/arrow-left-circle-fill.svg" onmouseover="leftHover(this);" onmouseout="leftUnhover(this);"></a>
                  <a class="btn-floating" href="#multi-item-example" data-slide="next"><img style="width: 50px;" src="../images/arrow-right-circle-fill.svg"  onmouseover="rightHover(this);" onmouseout="rightUnhover(this);"></a>
              </div>
              <div class="carousel-inner" role="listbox">
                  <div class="carousel-item active">
                  <?php while($quiz = $createQuizRetval->fetch_assoc()){
                            if($ind%3==0 && $ind != 0){?>
                      </div>
                      <div class="carousel-item ">
                          <?php } ?>
                      <div class="col-md-4" style="float:left;">
                          <div class="card mb-2" style="height: 400px;">
                              <div class="editQuizButtonsDiv mt-2 mb-2">
                                  <a class="editQuizBtn btn btn-outline-secondary mr-2" href="editQuiz.php?id=<?php echo $quiz['quiz_id']; ?>">Edit</a>
                                  <a class="btn btn-primary" href="quizPage.php?quiz_id=<?php echo $quiz["quiz_id"]; ?>" >Play</a>
                              </div>
                              <div class="d-flex justify-content-center">
                                  <h4 class="card-title"><?php echo $quiz["quiz_name"]; ?></h4>
                              </div>
                              <div class="editQuizCardImage">
                                  <img class="card-img-top"  src="<?php echo $quiz["quiz_picture"]; ?>" alt="Card image cap">
                              </div>
                          </div>
                      </div>
                  <?php  $ind++;
                          }?>
                  </div>
              </div>
          </div>
      </div>
  </div>

<script>
    var flag = 0;
    //function that makes the page Convenient to design
    onEdit();
    function onEdit(){
        var fullNameInput = document.getElementById("fullNameInput");
        var doneBtn = document.getElementById("doneBtn");
        var editBtn = document.getElementById("editBtn");
        var logOutBtn = document.getElementById("logOutBtn");
        if(flag == 0){
            fullNameInput.disabled = true;
            editBtn.innerHTML = "Edit";
            editBtn.classList.remove("btn-secondary");
            fullNameInput.classList.remove("animate__pulse");
            fullNameInput.classList.add("font-weight-bold");
            fullNameInput.classList.add("disabledInput");
            editBtn.classList.add("btn-light");
            doneBtn.style.display = "none";
            logOutBtn.style.display = "block";
            flag =1 ;
        }
        else{
            fullNameInput.disabled = false;
            fullNameInput.classList.add("animate__pulse");
            fullNameInput.classList.remove("font-weight-bold");
            fullNameInput.classList.remove("disabledInput");
            editBtn.innerHTML = "Exit";
            editBtn.classList.remove("btn-light");
            editBtn.classList.add("btn-secondary");
            doneBtn.style.display = "block";
            logOutBtn.style.display = "none";
            flag = 0;
        }
    }
    //this flag hides the button controllers if the user has no quizzes created
    if(buttonFlag){
        document.getElementById("hideControlButtons").style.visibility = "hidden";
    }
    //function to add user link to image
    function addImageUrl(){
        document.forms["imageUrlForm"].submit();
    }
    //function to submit two forms
    function submitEdits(){
        document.forms["nameForm"].submit();
    }
    //function to check if the user uploaded an image to his profile, if not then a default image gets replaced instead.
    function addModalImage(){
        var image = document.getElementById("imageUrl").value;
        if(image === ""){
            document.getElementById("modalImage").src = "http://localhost/QuizTech/images/user_avatar.svg";
        }
        else{
            document.getElementById("modalImage").src = image;
        }
    }

    //left and right arrows on hover and unhover
    function leftHover(element) {
        element.setAttribute('src', 'http://localhost/QuizTech/images/arrow-left-circle-fill-dark.svg');
    }

    function leftUnhover(element) {
        element.setAttribute('src', 'http://localhost/QuizTech/images/arrow-left-circle-fill.svg');
    }

    function rightHover(element) {
        element.setAttribute('src', 'http://localhost/QuizTech/images/arrow-right-circle-fill-dark.svg');
    }

    function rightUnhover(element) {
        element.setAttribute('src', 'http://localhost/QuizTech/images/arrow-right-circle-fill.svg');
    }
</script>
</body>
</html>