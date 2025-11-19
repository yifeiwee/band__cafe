<?php
// config.php: database connection settings

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
}

// Database configuration from environment variables
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'db';
$db   = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'bandcafe_db';
$user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? '1234';

// Environment settings
$appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production';
$appDebug = filter_var($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?? 'false', FILTER_VALIDATE_BOOLEAN);

// Configure error reporting based on environment
if ($appDebug && $appEnv !== 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
}

// Include security functions
require_once __DIR__ . '/includes/security.php';

// Create a MySQLi connection
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    // Log error without exposing details
    error_log('Database connection failed: ' . $mysqli->connect_error);
    die('Database connection error. Please contact the administrator.');
}

// Set charset to prevent SQL injection
$mysqli->set_charset('utf8mb4');
?>
