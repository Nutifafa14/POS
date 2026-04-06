<?php
include("../config/database.php");

$product_name = $_POST['product_name'];
$category     = $_POST['category'];
$price        = $_POST['price'];
$quantity     = $_POST['quantity'];
$barcode      = $_POST['barcode'];

// Handle image upload
$image_name = null;

if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0){

    // Create uploads folder if it doesn't exist
    $upload_dir = "../uploads/products/";
    if(!is_dir($upload_dir)){
        mkdir($upload_dir, 0755, true);
    }

    // Validate it's actually an image
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));

    if(in_array($ext, $allowed)){
        $image_name = uniqid('product_') . '.' . $ext;
        move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $image_name);
    }
}

$query = "INSERT INTO products(product_name, category, price, quantity, barcode, product_image)
          VALUES('$product_name', '$category', '$price', '$quantity', '$barcode', '$image_name')";

mysqli_query($conn, $query);

header("Location: ../pages/products.php");
?>