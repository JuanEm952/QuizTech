<?php
function connection(){
    $host='localhost';
    $user='root';
    $password='';
    $database ='quiztech';
    $conn = mysqli_connect($host, $user, $password, $database);
    if(!$conn){
        return false;
    }
    return $conn;
}

function disConnect($conn){
    mysqli_close($conn);
}
?>
