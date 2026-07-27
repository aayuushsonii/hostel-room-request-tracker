<?php
// Database Configuration for XAMPP
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'hostel_db');

// Attempt MySQL Connection using PDO
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    try {
        $pdo_root = new PDO("mysql:host=" . DB_SERVER, DB_USERNAME, DB_PASSWORD);
        $pdo_root->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
        $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch(PDOException $e2) {
        $db_error = "Database Connection Failed: " . $e2->getMessage();
    }
}

// Start Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper Function: Sanitize User Input
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Helper Function: Generate Unique Request Code
function generateRequestCode() {
    return 'REQ-' . date('Y') . '-' . rand(1000, 9999);
}

// Helper Function: Generate Ticket ID
function generateTicketId() {
    return 'TKT-' . rand(100, 999);
}

// Authentication Helpers
function is_student_logged_in() {
    return isset($_SESSION['student_id']);
}

function require_student_login() {
    if (!is_student_logged_in()) {
        header("Location: login.php");
        exit();
    }
}
?>
