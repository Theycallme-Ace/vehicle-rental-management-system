<?php
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check user role
function hasRole($requiredRole) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $allowedRoles = [
        'superadmin' => ['superadmin'],
        'admin' => ['superadmin', 'admin'],
        'manager' => ['superadmin', 'admin', 'manager'],
        'staff' => ['superadmin', 'admin', 'manager', 'staff'],
        'user' => ['superadmin', 'admin', 'manager', 'staff', 'user']
    ];
    
    return in_array($_SESSION['role'], $allowedRoles[$requiredRole] ?? []);
}

// Function to require authentication
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
}

// Function to require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /403.php');
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

// Set default timezone
date_default_timezone_set('Asia/Jakarta');
?>
