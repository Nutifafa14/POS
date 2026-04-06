<?php
session_start();
include("../config/database.php");
include("../config/paystack.php");

$phone = $_POST['customer_phone'];
$total = 0;

foreach($_SESSION['cart'] as $item){
    $total += $item['price'] * $item['quantity'];
}

// Paystack amount is in pesewas (multiply cedis by 100)
$amount = $total * 100;

// Format phone for Paystack — must be 233XXXXXXXXX format
$phone = preg_replace('/^0/', '233', $phone);

$fields = [
    'email'          => 'pos@grocenix.com', // a placeholder email
    'amount'         => $amount,
    'currency'       => 'GHS',
    'mobile_money'   => [
        'phone'    => $phone,
        'provider' => 'mtn' // options: mtn, vod (Vodafone), tgo (AirtelTigo)
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, PAYSTACK_PAYMENT_URL . '/charge');
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

if($result['status'] && $result['data']['status'] == 'send_otp'){
    // Store reference in session for verification later
    $_SESSION['paystack_reference'] = $result['data']['reference'];
    $_SESSION['pending_payment_method'] = 'Mobile Money';
    header("Location: ../pages/verify_payment.php");
} else {
    $error = $result['message'] ?? 'Payment initiation failed';
    header("Location: ../pages/sales.php?error=" . urlencode($error));
}
exit();
?>