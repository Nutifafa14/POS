<?php
session_start();
include("../config/database.php");
include("../config/paystack.php");

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

// Format phone for Paystack — must be 233XXXXXXXXX format
$clean_phone = preg_replace('/[^0-9]/', '', $phone);
$formatted_phone = preg_replace('/^0/', '233', $clean_phone);

// Determine Provider Dynamically
$provider = 'mtn'; // default
$prefix_local = '';

// Get the local equivalent of the number to check the prefix (e.g. 024, 050)
if(strpos($clean_phone, '233') === 0 && strlen($clean_phone) > 3) {
    $prefix_local = '0' . substr($clean_phone, 3, 2);
} else {
    $prefix_local = substr($clean_phone, 0, 3);
}

$vod_prefixes = ['020', '050'];
$tgo_prefixes = ['027', '057', '026', '056'];

if (in_array($prefix_local, $vod_prefixes)) {
    $provider = 'vod';
} elseif (in_array($prefix_local, $tgo_prefixes)) {
    $provider = 'tgo';
}

$fields = [
    'email'          => 'pos@grocenix.com', // a placeholder email
    'amount'         => $amount,
    'currency'       => 'GHS',
    'mobile_money'   => [
        'phone'    => $formatted_phone,
        'provider' => $provider
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

// Paystack MoMo charge typically returns 'pay_offline' or 'pending' when waiting for the user to approve the prompt.
if(isset($result['status']) && $result['status'] === true && isset($result['data']['status'])){
    $valid_statuses = ['send_otp', 'pay_offline', 'pending', 'success'];
    if(in_array($result['data']['status'], $valid_statuses)){
        // Store reference in session for verification later
        $_SESSION['paystack_reference'] = $result['data']['reference'];
        $_SESSION['pending_payment_method'] = 'Mobile Money';
        header("Location: ../pages/verify_payment.php");
        exit();
    }
}

$error = $result['message'] ?? 'Payment initiation failed';
header("Location: ../pages/sales.php?error=" . urlencode($error));
exit();
?>