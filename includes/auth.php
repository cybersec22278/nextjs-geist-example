<?php
require_once 'config.php';

function login($username, $password) {
    $users = file(DB_USERS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($users as $user) {
        $data = explode('|', $user);
        if ($data[0] === $username && verifyHash($password, $data[1])) {
            $_SESSION['user_id'] = uniqid();
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $data[2];
            return true;
        }
    }
    return false;
}

function register($username, $password, $email) {
    // Validate input
    if (empty($username) || empty($password) || empty($email)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }

    // Username validation
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        return ['success' => false, 'message' => 'Username must be 3-20 characters and contain only letters, numbers, and underscores'];
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }

    // Password strength validation
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters long'];
    }

    // Check if username exists
    if (userExists($username)) {
        return ['success' => false, 'message' => 'Username already exists'];
    }

    // Check if email exists
    if (emailExists($email)) {
        return ['success' => false, 'message' => 'Email already registered'];
    }

    // Add user to database
    try {
        addUser($username, $password, $email);
        return ['success' => true, 'message' => 'Registration successful'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
    session_start();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// CSRF Protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Rate Limiting
function checkRateLimit($ip, $action, $limit = 5, $timeframe = 300) {
    $file = "database/ratelimit_{$action}.txt";
    
    if (!file_exists($file)) {
        file_put_contents($file, '');
    }
    
    $attempts = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $current_time = time();
    $recent_attempts = 0;
    
    // Count recent attempts
    foreach ($attempts as $attempt) {
        list($stored_ip, $timestamp) = explode('|', $attempt);
        if ($stored_ip === $ip && ($current_time - $timestamp) < $timeframe) {
            $recent_attempts++;
        }
    }
    
    // Clean up old entries
    $new_attempts = array_filter($attempts, function($attempt) use ($current_time, $timeframe) {
        list(, $timestamp) = explode('|', $attempt);
        return ($current_time - $timestamp) < $timeframe;
    });
    
    // Add new attempt
    $new_attempts[] = "$ip|$current_time";
    file_put_contents($file, implode("\n", $new_attempts) . "\n");
    
    return $recent_attempts < $limit;
}

// Input Validation
function validateInput($input, $type) {
    switch ($type) {
        case 'username':
            return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $input);
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL);
        case 'password':
            return strlen($input) >= 8;
        default:
            return false;
    }
}
