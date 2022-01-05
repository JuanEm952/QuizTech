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
        $conn = connection();
        $id = $_GET['id'];
        $sql = "DELETE FROM `User` WHERE user_id='$id'";
        mysqli_query($conn,$sql);

        disConnect($conn);
        header("Location:adminEditUsers.php");
    }
}
?>