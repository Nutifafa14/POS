<?php
session_start();
include("../config/database.php");

$payment_method = $_POST['payment_method'] ?? $_GET['payment_method'] ?? 'Cash';

// Eliminate dummy logic bypass:
// Ensure Mobile Money goes through Paystack and isn't processed immediately by direct POST or spoofed GET
if ($payment_method === 'Mobile Money') {
    if (!isset($_POST['paystack_reference'])) {
        header("Location: ../pages/sales.php?error=" . urlencode("Payment verification missing. Please use the Paystack modal."));
        exit();
    }
    
    $reference = $_POST['paystack_reference'];
    require_once("../config/paystack.php");
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYSTACK_PAYMENT_URL . '/transaction/verify/' . rawurlencode($reference));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    if (!isset($result['status']) || $result['status'] !== true || $result['data']['status'] !== 'success') {
        header("Location: ../pages/sales.php?error=" . urlencode("Mobile Money Payment failed or was not completed."));
        exit();
    }
}

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