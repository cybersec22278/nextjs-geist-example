<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$balance = getUserBalance($username);
$total_attacks = getUserAttacks($username);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stresser Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <div class="logo">Stresser</div>
            <div class="nav-section">
                <h3>MAIN</h3>
                <a href="index.php" class="active">Home</a>
                <a href="purchase.php">Purchase</a>
            </div>
            <div class="nav-section">
                <h3>ATTACKS</h3>
                <a href="hub.php">Hub</a>
            </div>
            <div class="nav-section">
                <h3>OTHERS</h3>
                <a href="profile.php">Profile</a>
                <a href="payments.php">Payments</a>
                <a href="contact.php">Contact</a>
                <a href="terms.php">Terms</a>
                <a href="documentation.php">Documentation</a>
            </div>
        </nav>

        <main class="content">
            <header>
                <h1>Home</h1>
                <div class="user-menu">
                    <span><?php echo htmlspecialchars($username); ?></span>
                    <a href="logout.php">Logout</a>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Attacks</h3>
                    <div class="stat-value"><?php echo number_format($total_attacks); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="stat-value"><?php echo number_format(getTotalUsers()); ?></div>
                </div>
            </div>

            <div class="announcements">
                <h2>Announcements</h2>
                <?php foreach(getAnnouncements() as $announcement): ?>
                <div class="announcement">
                    <div class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                    <div class="announcement-content"><?php echo htmlspecialchars($announcement['content']); ?></div>
                    <div class="announcement-date">Sent at <?php echo htmlspecialchars($announcement['date']); ?></div>
                </div>
                <?php endforeach; ?>
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
                        <span>Total Attacks</span>
                        <span><?php echo number_format($total_attacks); ?></span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</body>
</html>
