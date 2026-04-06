<?php
// Load .env variables (a simple env loader for vanilla PHP without Composer)
$envPath = __DIR__ . '/../.env';
$envVars = [];
if (file_exists($envPath)) {
    $envVars = parse_ini_file($envPath);
}

define('PAYSTACK_SECRET_KEY', $envVars['PAYSTACK_SECRET_KEY'] ?? 'sk_live_');
define('PAYSTACK_PUBLIC_KEY', $envVars['PAYSTACK_PUBLIC_KEY'] ?? 'pk_live_');
define('PAYSTACK_PAYMENT_URL', $envVars['PAYSTACK_PAYMENT_URL'] ?? 'https://api.paystack.co');
?>