<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/nowpayments.php';

// Require login
requireLogin();

$error = '';
$success = '';
$username = $_SESSION['username'];
$balance = getUserBalance($username);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
        if ($amount && $amount >= 5) { // Minimum deposit $5
            try {
                $payment = createPayment($amount);
                if ($payment && isset($payment['payment_id'])) {
                    // Store payment info in session for verification
                    $_SESSION['pending_payment'] = [
                        'payment_id' => $payment['payment_id'],
                        'amount' => $amount
                    ];
                    header("Location: " . $payment['payment_url']);
                    exit();
                } else {
                    $error = 'Failed to create payment. Please try again.';
                }
            } catch (Exception $e) {
                $error = 'Payment service error. Please try again later.';
            }
        } else {
            $error = 'Minimum deposit amount is $5';
        }
    }
}

// Get payment history
$payments = getPaymentHistory($username);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Stresser</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <div class="logo">Stresser</div>
            <div class="nav-section">
                <h3>MAIN</h3>
                <a href="index.php">Home</a>
                <a href="purchase.php">Purchase</a>
            </div>
            <div class="nav-section">
                <h3>ATTACKS</h3>
                <a href="hub.php">Hub</a>
            </div>
            <div class="nav-section">
                <h3>OTHERS</h3>
                <a href="profile.php">Profile</a>
                <a href="payments.php" class="active">Payments</a>
                <a href="contact.php">Contact</a>
                <a href="terms.php">Terms</a>
                <a href="documentation.php">Documentation</a>
            </div>
        </nav>

        <main class="content">
            <header>
                <h1>Payments</h1>
                <div class="user-menu">
                    <span><?php echo htmlspecialchars($username); ?></span>
                    <a href="logout.php">Logout</a>
                </div>
            </header>

            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="payment-section">
                <div class="balance-card">
                    <h2>Current Balance</h2>
                    <div class="balance-amount">$<?php echo number_format($balance, 2); ?></div>
                </div>

                <div class="deposit-card">
                    <h2>Make a Deposit</h2>
                    <form method="POST" action="payments.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label for="amount">Amount (USD)</label>
                            <input type="number" id="amount" name="amount" min="5" step="0.01" required>
                            <small>Minimum deposit: $5.00</small>
                        </div>

                        <button type="submit" class="btn-primary">Deposit Now</button>
                    </form>
                </div>

                <div class="payment-history">
                    <h2>Payment History</h2>
                    <?php if (empty($payments)): ?>
                        <p>No payment history available.</p>
                    <?php else: ?>
                        <table class="payment-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['date']); ?></td>
                                        <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($payment['status']); ?>">
                                                <?php echo htmlspecialchars($payment['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <aside class="summary">
            <div class="summary-card">
                <h3>Summary</h3>
                <div class="balance">
                    <span>Balance</span>
                    <span>$<?php echo number_format($balance, 2); ?></span>
                </div>
                <div class="stats">
                    <div class="stat">
                        <span>Total Deposits</span>
                        <span><?php echo count($payments); ?></span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</body>
</html>
