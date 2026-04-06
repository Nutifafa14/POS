<?php
session_start();

$index = $_GET['index'];

unset($_SESSION['cart'][$index]);

// Re-index array
$_SESSION['cart'] = array_values($_SESSION['cart']);

header("Location: ../pages/sales.php");
?>