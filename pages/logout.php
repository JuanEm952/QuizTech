<?php
session_start();
unset($_SESSION['logged_email']);
header('Location:login.php');
die();

?>