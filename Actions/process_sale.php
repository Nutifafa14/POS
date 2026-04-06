<?php
session_start();
include("../config/database.php");

$payment_method = $_POST['payment_method'] ?? $_GET['payment_method'] ?? 'Cash';
$total = 0;

foreach($_SESSION['cart'] as $item){
    $total += $item['price'] * $item['quantity'];
}

// Insert sale
$query = "INSERT INTO sales(sale_date, total_amount, payment_method)
VALUES(NOW(), '$total', '$payment_method')";

mysqli_query($conn, $query);

$sale_id = mysqli_insert_id($conn);

// Insert sale items
foreach($_SESSION['cart'] as $item){

$product_id = $item['id'];
$quantity = $item['quantity'];
$price = $item['price'];

// Check stock again before sale
$query = "SELECT quantity FROM products WHERE product_id='$product_id'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

if($quantity > $row['quantity']){
    echo "Error: Not enough stock for product ID ".$product_id;
    exit();
}

$query = "INSERT INTO sales_items(sale_id, product_id, quantity, price)
VALUES('$sale_id','$product_id','$quantity','$price')";

mysqli_query($conn, $query);

// Update stock
$query = "UPDATE products
SET quantity = quantity - $quantity
WHERE product_id='$product_id'";

mysqli_query($conn, $query);

}

// clear cart
unset($_SESSION['cart']);

header("Location: ../pages/receipt.php?sale_id=".$sale_id);

?>