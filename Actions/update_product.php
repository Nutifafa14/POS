<?php
include("../config/database.php");

$product_id   = $_POST['product_id'];
$product_name = $_POST['product_name'];
$category     = $_POST['category'];
$price        = $_POST['price'];
$quantity     = $_POST['quantity'];
$barcode      = $_POST['barcode'];

if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0){

    $upload_dir = "../uploads/products/";

    // Create folder if it doesn't exist
    if(!is_dir($upload_dir)){
        mkdir($upload_dir, 0755, true);
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));

    if(in_array($ext, $allowed)){
        $image_name = uniqid('product_') . '.' . $ext;
        move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $image_name);

        // Update WITH new image
        $query = "UPDATE products SET 
                    product_name='$product_name', 
                    category='$category', 
                    price='$price', 
                    quantity='$quantity', 
                    barcode='$barcode', 
                    image='$image_name'
                  WHERE product_id='$product_id'";
    }

} else {

    // Update WITHOUT changing image
    $query = "UPDATE products SET 
                product_name='$product_name', 
                category='$category', 
                price='$price', 
                quantity='$quantity', 
                barcode='$barcode'
              WHERE product_id='$product_id'";
}

mysqli_query($conn, $query);
header("Location: ../pages/products.php");
?>
```

---

Your `actions/` folder should now look like this:
```
actions/
├── add_product.php
├── update_product.php   ← new file
├── add_to_cart.php
├── remove_item.php
└── process_sale.php