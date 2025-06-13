<?php
// Database files
define('DB_USERS', 'database/users.txt');
define('DB_ATTACKS', 'database/attacks.txt');
define('DB_ANNOUNCEMENTS', 'database/announcements.txt');

// NowPayments API Configuration
define('NOWPAYMENTS_API_KEY', 'YOUR-API-KEY'); // Replace with your API key
define('NOWPAYMENTS_IPN_SECRET', 'YOUR-IPN-SECRET'); // Replace with your IPN secret
define('NOWPAYMENTS_API_URL', 'https://api.nowpayments.io/v1');

// Ensure database directory exists
if (!file_exists('database')) {
    mkdir('database', 0755, true);
}

// Create database files if they don't exist
$dbFiles = [DB_USERS, DB_ATTACKS, DB_ANNOUNCEMENTS];
foreach ($dbFiles as $file) {
    if (!file_exists($file)) {
        file_put_contents($file, '');
    }
}

// Helper Functions
function getUserBalance($username) {
    $users = file(DB_USERS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($users as $user) {
        $data = explode('|', $user);
        if ($data[0] === $username) {
            return floatval($data[3]); // Balance is stored in 4th position
        }
    }
    return 0.00;
}

function getUserAttacks($username) {
    $attacks = file(DB_ATTACKS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $count = 0;
    foreach ($attacks as $attack) {
        $data = explode('|', $attack);
        if ($data[0] === $username) {
            $count++;
        }
    }
    return $count;
}

function getTotalUsers() {
    return count(file(DB_USERS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
}

function getAnnouncements() {
    $announcements = [];
    $lines = file(DB_ANNOUNCEMENTS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $data = explode('|', $line);
        $announcements[] = [
            'title' => $data[0],
            'content' => $data[1],
            'date' => $data[2]
        ];
    }
    return array_reverse($announcements); // Most recent first
}

// Security Functions
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function generateHash($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyHash($password, $hash) {
    return password_verify($password, $hash);
}

// Database Functions
function addUser($username, $password, $email) {
    $hash = generateHash($password);
    $data = "$username|$hash|$email|0.00\n"; // username|password_hash|email|balance
    file_put_contents(DB_USERS, $data, FILE_APPEND);
}

function userExists($username) {
    $users = file(DB_USERS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($users as $user) {
        $data = explode('|', $user);
        if ($data[0] === $username) {
            return true;
        }
    }
    return false;
}

function emailExists($email) {
    $users = file(DB_USERS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($users as $user) {
        $data = explode('|', $user);
        if ($data[2] === $email) {
            return true;
        }
    }
    return false;
}

function updateUserBalance($username, $amount) {
    $users = file(DB_USERS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $newContent = '';
    
    foreach ($users as $user) {
        $data = explode('|', $user);
        if ($data[0] === $username) {
            $data[3] = number_format($amount, 2);
            $newContent .= implode('|', $data) . "\n";
        } else {
            $newContent .= $user . "\n";
        }
    }
    
    file_put_contents(DB_USERS, $newContent);
}

// Error Handling
function displayError($message) {
    return "<div class='error'>$message</div>";
}

function displaySuccess($message) {
    return "<div class='success'>$message</div>";
}
