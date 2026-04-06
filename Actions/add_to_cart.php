<?php
session_start();
include("../config/database.php");

$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

// Get product from database
$query = "SELECT * FROM products WHERE product_id='$product_id'";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

// Check stock availability
if($quantity > $product['quantity']){
    echo "Not enough stock available!";
    exit();
}

// Initialize cart
if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

// Check if product already exists in cart
$found = false;

foreach($_SESSION['cart'] as &$item){
    if($item['id'] == $product_id){
        $item['quantity'] += $quantity;
        $found = true;
        break;
    }
}

// If not found, add new item
if(!$found){
    $item = [
        "name" => $product['product_name'],
        "price" => $product['price'],
        "quantity" => $quantity,
        "id" => $product_id
    ];
    $_SESSION['cart'][] = $item;
}

header("Location: ../pages/sales.php");
?>