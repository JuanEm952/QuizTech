<?php
include('serverConnection.php');

session_start();
//Set Question Number
$flag = true;
$question="";

if(!$_SESSION['logged_email']){
    header("Location:notUserMessage.php");
}
else{
    if(isset($_GET['quiz_id']) && isset($_GET['n'])){
        $conn = connection();
        $userEmail = $_SESSION['logged_email'];
        $number = $conn->real_escape_string($_GET['n']);
        $quiz_id = $conn->real_escape_string($_GET['quiz_id']);
        //Query for the Question
        $query = "SELECT * FROM `Question` WHERE `question_number` = '$number' AND `quiz_id`='$quiz_id' ";

        // Get the question
        $result = mysqli_query($conn,$query);
        $question = mysqli_fetch_assoc($result);

        // Get Total questions
        $query = "SELECT * FROM `Question` WHERE `quiz_id`='$quiz_id' ";
        $total_questions = mysqli_num_rows(mysqli_query($conn,$query));

    }
    else{
        header("Location:categories.php");
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Game</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="stylesheet" href="../css/gameStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">
    <!-- bootstrap javascript library -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <!-- machine learning library -->
    <script src="https://unpkg.com/ml5@latest/dist/ml5.min.js"></script>
    <style>
        .bar{
            width:0%;
            height : 15px;
            background-color:white;
            animation-name: progress;
            animation-delay: 3s;
            animation-duration: <?php echo $question['time'] ?>s;
            animation-timing-function: linear;
        }
    </style>
</head>
<body id="body">
<!-- image popup window -->
<div id="myModal" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="img01">
    <div id="caption"></div>
</div>
<!-- background audio -->
<audio src="../soundEffect/<?php echo $question['time'] ?>sec.m4a"></audio>
<div id="bar" class="bar"></div>
<div class=" d-flex justify-content-between">
    <div id="questionNumDiv" class="question-number m-2">
        <span style="font-size: 35px; font-weight: bolder;"><?php echo $number ?></span>/<?php echo $total_questions ?>
    </div>
    <div id="muteDiv" class="mute-div m-2">
        <button class="btn btn-light " onclick="mute()"><i id="volumIcon" class="" style="font-size: 35px"></i></button>
    </div>
</div>
<div class="bodyDiv">

    <span class="cntDown" id="countdown"></span>
    <div class="myDiv p-4" id="questionDiv">
        <form id="questionForm" action="process.php" method="post">
            <!-- check if the question have a image -->
            <?php if($question['picture'] != null || $question['picture'] != "" ){ ?>
                <div class="d-flex justify-content-center mb-5 " >
                    <img id="myImg" class="questionImage" src="<?php echo $question['picture']; ?>" data-toggle="modal" data-target="#exampleModal" />
                </div>
            <?php } ?>
            <div class="d-flex justify-content-center">
                <!-- htmlspecialchars function Removing the effect of HTML tags in string -->
                <p class="animate__animated animate__slideInDown mb-3 questionText"><?php echo htmlspecialchars($question['text']); ?></p>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-12 mt-2">
                    <div class="input-group ">
                        <button type="text" class="btn btn-light form-control text-center animate__animated animate__slideInLeft delayAnimate h-auto" onclick="submitQuestion(this.value)" value="1"><?php echo htmlspecialchars($question['answer_1']); ?></button>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 mt-2">
                    <div class="input-group">
                        <button type="text" class="btn btn-light form-control text-center animate__animated animate__slideInRight delayAnimate h-auto" onclick="submitQuestion(this.value)" value="2"><?php echo htmlspecialchars($question['answer_2']); ?></button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-12 mt-2">
                    <div class="input-group ">
                        <button type="text" class="btn btn-light form-control text-center animate__animated animate__slideInLeft delayAnimate h-auto" onclick="submitQuestion(this.value)" value="3"><?php echo htmlspecialchars($question['answer_3']); ?></button>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 mt-2">
                    <div class="input-group">
                        <button type="text" class="btn btn-light form-control text-center animate__animated animate__slideInRight delayAnimate h-auto" onclick="submitQuestion(this.value)" value="4"><?php echo htmlspecialchars($question['answer_4']); ?></button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            <input type="hidden" name="number" value="<?php echo $number; ?>">
            <input type="hidden" name="timeLeft" value="">
            <input type="hidden" id="choice" name="selectedChoice" value="">
        </form>
    </div>
</div>
<script>
    // Options for the SpeechCommands18w model, the default probabilityThreshold is 0
    let classifier;
    if(sessionStorage.getItem("isML") === "true"){
        const options = { probabilityThreshold: 0.99 };
        classifier = ml5.soundClassifier('SpeechCommands18w', options, modelReady);
    }

    function modelReady() {
        // classify sound
        classifier.classify(gotResult);
    }
    //this function checks the user choice
    function gotResult(error, results){
        if(error){
            console.error(error);
        }
        if(results[0].label === 'one'){
            submitQuestion(1);
        }
        if(results[0].label === 'two'){
            submitQuestion(2);
        }
        if(results[0].label === 'three'){
            submitQuestion(3);
        }
        if(results[0].label === 'four'){
            submitQuestion(4);
        }
    }
    const audio = document.querySelector("audio");
    //prevent user to back to previous question and redirect to quizPage
    redirectOnBack();
     function redirectOnBack() {
        window.history.pushState({page: 1}, "", "");
        window.onpopstate = function (event) {
            if (event) {
                var quiz_id = <?php echo $quiz_id; ?>;
                window.location.href = 'quizPage.php?quiz_id='+quiz_id;
                // Code to handle back button or prevent from navigation
            }
        }
    }

    //check if user has muted the music before
    if(sessionStorage.getItem("isMute")==="1"){
        sessionStorage.setItem("isMute", "1");
        audio.volume = 0.5;
        document.getElementById("volumIcon").className = "bi bi-volume-up-fill";
    }
    else{
        sessionStorage.setItem("isMute", "0");
        audio.volume = 0;
        document.getElementById("volumIcon").className = "bi bi-volume-mute-fill";
    }

    // Countdown till the quiz starts
    var seconds = 3, $seconds = document.querySelector('#countdown');
    (function countdown() {
        var tick = new Audio('../soundEffect/countDownTick.m4a');
        if(seconds !== 0){
            tick.volume = 0.5;
            tick.play();
        }
        $seconds.textContent = seconds
        if(seconds --> 0) setTimeout(countdown, 1000)

    })();
    setTimeout(function(){
        document.getElementById("countdown").style.display = "none";
        document.getElementById("questionDiv").style.display = "block";
        document.getElementById("questionNumDiv").style.display = "block";
        document.getElementById("muteDiv").style.display = "block";
        audio.play();
    }, 3000);

    //Given time to complete the question if the time runs out, the question gets skipped.
    setTimeout(function(){
        submitQuestion( 0);
    }, <?php echo $question['time']?>000+3000);

    // Submit form
    function submitQuestion(btnValue){
        document.getElementById("choice").value = btnValue;
        // to check how much time left
        var bar = document.getElementById('bar').offsetWidth;
        var body = document.getElementById('body').offsetWidth;
        document.getElementsByName("timeLeft")[0].value = 1-(bar/body);
        document.forms["questionForm"].submit();
    }

    function mute(){
        // 0 = mute | 1 = not muted
        if( sessionStorage.getItem("isMute") === "1"){
            sessionStorage.setItem("isMute", "0");
            audio.volume = 0;
            document.getElementById("volumIcon").className = "bi bi-volume-mute-fill";
        }
        else{
            sessionStorage.setItem("isMute", "1");
            audio.volume = 0.5;
            document.getElementById("volumIcon").className = "bi bi-volume-up-fill";
        }
    }

    //question image zoom onclick
    var modal = document.getElementById("myModal");

    // Get the image and insert it inside the modal - use its "alt" text as a caption
    var img = document.getElementById("myImg");
    var modalImg = document.getElementById("img01");
    var captionText = document.getElementById("caption");
    img.onclick = function(){
        modal.style.display = "block";
        modalImg.src = this.src;
        captionText.innerHTML = this.alt;
    }

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }
</script>
</body>
</html>
