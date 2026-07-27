<?php
require_once 'config.php';

// Unset all session variables
$_SESSION = array();

// Destroy session
if (session_id() != '' || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}
session_destroy();

// Redirect to Login page
header("Location: login.php");
exit();
?>
