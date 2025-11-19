<?php
require 'config.php';
configureSecureSession();
session_start();

// Log the logout
if (isset($_SESSION['username'])) {
    logSecurityEvent("User logged out: " . $_SESSION['username'], "INFO");
}

session_unset();
session_destroy();

// Clear the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

header("Location: login.php");
exit();
?>
