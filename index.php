<?php
session_start();
include("config/database.php");

// If already logged in, go to dashboard
if(isset($_SESSION['user'])){
    header("Location: pages/dashboard.php");
    exit();
}

// Otherwise, go to login
header("Location: auth/login.php");
exit();
?>