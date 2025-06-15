<?php
// config.php: database connection settings
$host = 'localhost';
$db   = 'bandcafe';  // your database name
$user = 'root';   // your DB username
$pass = '';   // your DB password

// Create a MySQLi connection
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}
?>
