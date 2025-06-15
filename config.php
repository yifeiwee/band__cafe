<?php
// config.php: database connection settings
$host = 'localhost';  // Docker service name for MySQL container
$db   = 'bandcafe';  // your database name (matches docker-compose)
$user = 'root';   // your DB username
$pass = '';   // your DB password

// Create a MySQLi connection
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}
?>
