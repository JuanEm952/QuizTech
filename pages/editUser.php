<?php
include('serverConnection.php');
session_start();
if(!$_SESSION['logged_email']){
    header("Location:notUserMessage.php");
}
if($_SESSION['logged_email'] != "quiztechjj@gmail.com"){
    header("Location:index.php");
}
else{
    if(isset($_GET['id'])){
        $flag=false;
        $conn = connection();
        $id = $_GET['id'];
        $selectUserSQL = "SELECT * FROM `User` WHERE user_id='$id'";
        $selectUserRetval = mysqli_query($conn,$selectUserSQL);
        if(mysqli_num_rows($selectUserRetval) != 1){
            disConnect($conn);
            header("Location:adminEditUsers.php");
        }else{
            $flag=true;
        }
    }
}
if(isset($_POST['user_name']) && isset($_POST['userBirthday']) ){
    $conn = connection();
    $id = $_GET['id'];
    $name= $_POST['user_name'];
    $userBirthday = $_POST['userBirthday'];
    $category = $_POST['userFavCategory'];
    $imgUrl=$_POST['newImageLink'];

    $updateUserSQL = "UPDATE `User` SET `full_name`='$name',`birthday`='$userBirthday' ,`favorite_category`='$category' ,`picture_link`='$imgUrl' WHERE user_id='$id'";
    if(mysqli_query($conn,$updateUserSQL)){
        disConnect($conn); ?>
        <script>
            alert("Change has been done successfully");
            window.open('adminEditUsers.php','_self');
        </script>
    <?php
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit User</title>
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
<?php include('adminNavBar.php');?>
<?php if($flag){$row = $selectUserRetval->fetch_assoc() ?>
<div class="d-flex justify-content-center ">
    <h1 class="text-muted">Edit <?php echo htmlspecialchars($row["full_name"]); ?></h1>
</div>

<div class="row ml-2 mr-2 d-flex justify-content-center">
    <div class="card border-primary mb-3 col-lg-4 col-md-5 mb-4 correct-item removePadding no_decoration" >
        <div class="card-header bg-primary HeaderText"><?php echo htmlspecialchars($row["full_name"]); ?></div>
        <div class="card-body">
            <div class="d-flex justify-content-center">
                <img class="w-75" src="<?php echo $row["picture_link"]; ?>"/>
            </div>
            <p class="mt-3">Email : <?php echo $row["email"]; ?></p>
            <p>User Favorite Category : <?php echo $row["favorite_category"]; ?></p>
            <p>Birthday : <?php echo $row["birthday"]; ?></p>
        </div>
    </div>
</div>
<form class="container" action="" method="post" id="editUserForm">
    <!-- hidden input contain User image url -->
    <input type="hidden" id="valueImgUrl" name="newImageLink" value="<?php echo $row["picture_link"];?>" >
    <table class="table table-hover">
        <thead>
        <tr class="table-active">
            <th scope="col">Picture</th>
            <th scope="col">Name</th>
            <th scope="col">Favorite Category</th>
            <th scope="col">Birthday</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="img_th"><img src="<?php echo $row["picture_link"];?>" class="img-thumbnail radius-div shadow pointer leaderboard-img"  id="userImage" data-toggle="modal" data-target="#exampleModal"/></td>
            <td><input class="form-control radius-div border-0 shadow" type="text" value="<?php echo htmlspecialchars($row['full_name']); ?>" name="user_name"></td>
            <td>
                <select class="form-control radius-div border-0 shadow" name="userFavCategory">
                    <option selected value="<?php echo $row['favorite_category']; ?> "><?php echo $row['favorite_category']; ?> </option>
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
            <td><input class="form-control radius-div border-0 shadow" type="date" value="<?php echo $row['birthday']; ?>" name="userBirthday"> </td>
        </tr>
        <?php }disConnect($conn); ?>
        </tbody>
    </table>
    <div class="mt-4 col text-center">
        <p style="color: red;" id="validErr"></p>
        <a onclick="submitForm()" class="btn btn-primary mb-5">Done</a>
    </div>
</form>

<script>

    // The function checks if the user inserts invalid image link
    function addImageUrl(){
        urlImage = document.getElementById("imageUrl").value;
        var myImg = document.getElementById("userImage");
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

    // The function checks if the user uploaded an image to the Profile if not, then a default image gets uploaded instead.
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
        var userName = document.getElementsByName("user_name")[0].value;
        var flag = true;
        if(userName.length < 3){
            validErr.innerHTML = "The user name must contain at least 3 characters.";
            flag = false;
        }
        else if(userName.length > 20){
            validErr.innerHTML = "The user name can only contain up to 20 characters.";
            flag = false;
        }
        else{
            validErr.innerHTML = "";
        }
        if(flag){
            document.forms["editUserForm"].submit();
        }
    }

</script>
</body>
</html>
