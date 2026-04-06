<?php

include("../config/database.php");

$name = $_POST['name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$address = $_POST['address'];

$query = "INSERT INTO customers(name, phone, email, address)
VALUES('$name','$phone','$email','$address')";

mysqli_query($conn, $query);

header("Location: ../pages/customers.php");

?>