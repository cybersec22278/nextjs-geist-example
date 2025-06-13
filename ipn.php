<?php
require_once 'includes/config.php';
require_once 'includes/nowpayments.php';

// Get the raw POST data
$rawData = file_get_contents('php://input');
$payload = json_decode($rawData, true);

// Get the NOWPayments signature from headers
$signature = $_SERVER['HTTP_X_NOWPAYMENTS_SIG'] ?? '';

// Verify the signature
if (!$nowpayments->verifyIpnSignature($payload, $signature)) {
    http_response_code(400);
    exit('Invalid signature');
}

// Process the payment notification
if (isset($payload['payment_status']) && isset($payload['payment_id'])) {
    $paymentId = $payload['payment_id'];
    $status = $payload['payment_status'];
    $amount = $payload['price_amount'] ?? 0;
    $orderId = $payload['order_id'] ?? '';

    // Update payment status in database
    updatePaymentStatus($paymentId, $status);

    // If payment is completed, update user's balance
    if ($status === 'finished' || $status === 'confirmed') {
        // Extract username from order_id (assuming format: username_timestamp)
        $username = explode('_', $orderId)[0];
        
        // Get current balance
        $currentBalance = getUserBalance($username);
        
        // Add the new amount
        $newBalance = $currentBalance + $amount;
        
        // Update user's balance
        updateUserBalance($username, $newBalance);
    }

    // Log the IPN request
    $logFile = 'database/ipn_log.txt';
    $logData = date('Y-m-d H:i:s') . " | Payment ID: $paymentId | Status: $status | Amount: $amount\n";
    file_put_contents($logFile, $logData, FILE_APPEND);

    http_response_code(200);
    echo 'OK';
} else {
    http_response_code(400);
    echo 'Invalid payload';
}
