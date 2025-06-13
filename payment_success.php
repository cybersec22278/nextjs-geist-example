<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Require login
requireLogin();

$success = 'Payment processed successfully! Your balance will be updated once the payment is confirmed.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Stresser</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .payment-result-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .payment-result {
            background-color: var(--secondary);
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            border: 1px solid var(--border);
        }

        .payment-result h1 {
            color: #22c55e;
            margin-bottom: 1rem;
        }

        .payment-result p {
            margin-bottom: 2rem;
            color: var(--foreground);
        }

        .payment-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn-primary, .btn-secondary {
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-secondary {
            background-color: var(--border);
            color: var(--foreground);
        }

        .btn-primary:hover, .btn-secondary:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="payment-result-container">
        <div class="payment-result success">
            <h1>Payment Successful!</h1>
            <p><?php echo $success; ?></p>
            <div class="payment-actions">
                <a href="payments.php" class="btn-primary">View Payment History</a>
                <a href="index.php" class="btn-secondary">Return to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
