<?php
session_start();
include("../config/database.php");
include("../config/paystack.php");

if(!isset($_SESSION['paystack_reference'])){
    header("Location: ../pages/sales.php");
    exit();
}

$reference = $_SESSION['paystack_reference'];

// Verify the transaction with Paystack
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, PAYSTACK_PAYMENT_URL . '/transaction/verify/' . $reference);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . PAYSTACK_SECRET_KEY
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$status = $result['data']['status'] ?? 'pending';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verifying Payment - Grocenix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 text-center" style="max-width:500px; margin:auto;">
        <i class="bi bi-phone display-3 text-primary mb-3"></i>
        <h4>Waiting for Payment</h4>
        <p class="text-muted">The customer should approve the payment prompt on their phone.</p>

        <?php if($status == 'success'): ?>
            <div class="alert alert-success">Payment confirmed! Processing sale...</div>
            <script>
                setTimeout(() => {
                    window.location.href = '../actions/process_sale.php?payment_method=Mobile+Money';
                }, 2000);
            </script>
        <?php elseif($status == 'failed'): ?>
            <div class="alert alert-danger">Payment failed. Please try again.</div>
            <a href="sales.php" class="btn btn-danger">Back to POS</a>
        <?php else: ?>
            <div class="alert alert-warning">Payment pending — waiting for customer approval...</div>
            <button onclick="location.reload()" class="btn btn-primary">
                Check Again
            </button>
            <a href="sales.php" class="btn btn-outline-secondary ms-2">Cancel</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>