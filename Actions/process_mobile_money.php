<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Ensure we don't include files multiple times if required from another script
require_once("../config/database.php");
require_once("../config/paystack.php");

$phone = $_POST['mobile_number'] ?? $_POST['customer_phone'] ?? '';
if(empty($phone)) {
    header("Location: ../pages/sales.php?error=" . urlencode("Phone number is required."));
    exit();
}

$total = 0;

foreach($_SESSION['cart'] as $item){
    $total += $item['price'] * $item['quantity'];
}

// Paystack amount is in pesewas (multiply cedis by 100)
$amount = $total * 100;

// Initialize Paystack transaction using the standard URL instead of dummy prefix logic
$callback_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI'])) . "/pages/verify_payment.php";

$fields = [
    'email'          => 'pos@grocenix.com', 
    'amount'         => $amount,
    'currency'       => 'GHS',
    'channels'       => ['mobile_money'],
    'callback_url'   => $callback_url
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, PAYSTACK_PAYMENT_URL . '/transaction/initialize');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if(isset($result['status']) && $result['status'] === true && isset($result['data']['authorization_url'])){
    // Save reference and direct to Paystack hosted page to handle real mobile money provider selection natively
    $_SESSION['paystack_reference'] = $result['data']['reference'];
    $_SESSION['pending_payment_method'] = 'Mobile Money';
    header("Location: " . $result['data']['authorization_url']);
    exit();
}

$error = $result['message'] ?? 'Payment initiation failed';
header("Location: ../pages/sales.php?error=" . urlencode($error));
exit();
?>