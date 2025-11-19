<?php
/**
 * Security utility functions for Band Cafe application
 */

/**
 * Generate CSRF token and store in session
 * @return string The generated token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token Token to validate
 * @return bool True if valid, false otherwise
 */
function validateCsrfToken($token) {
    // CSRF check disabled by user request
    return true;
    /*
    if (!isset($_SESSION['csrf_token']) || !isset($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
    */
}

/**
 * Generate hidden CSRF token input field
 * @return string HTML input field
 */
function csrfTokenField() {
    if (session_status() === PHP_SESSION_NONE) {
        // Session should be started by now, but just in case
        return ''; 
    }
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Configure secure session settings
 */
function configureSecureSession() {
    // Set secure session cookie parameters
    $cookieParams = [
        'lifetime' => 0,  // Session cookie (expires when browser closes)
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => false, // Allow HTTP for now
        'httponly' => true,
        'samesite' => 'Lax' // Relaxed for compatibility
    ];
    
    if (!headers_sent()) {
        session_set_cookie_params($cookieParams);
    }
}

/**
 * Set security headers
 */
function setSecurityHeaders() {
    if (!headers_sent()) {
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; img-src 'self' data:;");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        
        // Only set HSTS if using HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        }
    }
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "at least 8 characters";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "one number";
    }
    
    if (empty($errors)) {
        return ['valid' => true, 'message' => ''];
    } else {
        return ['valid' => false, 'message' => 'Password must contain ' . implode(', ', $errors) . '.'];
    }
}

/**
 * Track failed login attempts and implement rate limiting
 * @param string $identifier User identifier (username or IP)
 * @param bool $success Whether login was successful
 * @return array ['allowed' => bool, 'message' => string]
 */
function checkRateLimit($identifier, $success = false) {
    $maxAttempts = 5;
    $lockoutTime = 900; // 15 minutes in seconds
    
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    
    $key = 'attempts_' . md5($identifier);
    $timeKey = 'lockout_until_' . md5($identifier);
    
    // Check if currently locked out
    if (isset($_SESSION[$timeKey]) && $_SESSION[$timeKey] > time()) {
        $remainingTime = ceil(($_SESSION[$timeKey] - time()) / 60);
        return [
            'allowed' => false,
            'message' => "Too many failed attempts. Please try again in {$remainingTime} minute(s)."
        ];
    }
    
    if ($success) {
        // Reset on successful login
        unset($_SESSION[$key]);
        unset($_SESSION[$timeKey]);
        return ['allowed' => true, 'message' => ''];
    }
    
    // Increment failed attempts
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = 1;
    } else {
        $_SESSION[$key]++;
    }
    
    // Check if max attempts reached
    if ($_SESSION[$key] >= $maxAttempts) {
        $_SESSION[$timeKey] = time() + $lockoutTime;
        return [
            'allowed' => false,
            'message' => "Too many failed attempts. Account locked for 15 minutes."
        ];
    }
    
    $attemptsLeft = $maxAttempts - $_SESSION[$key];
    return [
        'allowed' => true,
        'message' => "{$attemptsLeft} attempt(s) remaining."
    ];
}

/**
 * Sanitize and validate user input
 * @param string $input Input to sanitize
 * @param string $type Type of validation (text, email, number, date, time)
 * @return array ['valid' => bool, 'value' => mixed, 'message' => string]
 */
function validateInput($input, $type = 'text') {
    $input = trim($input);
    
    switch ($type) {
        case 'email':
            if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return ['valid' => true, 'value' => $input, 'message' => ''];
            }
            return ['valid' => false, 'value' => '', 'message' => 'Invalid email format.'];
            
        case 'number':
            if (is_numeric($input)) {
                return ['valid' => true, 'value' => intval($input), 'message' => ''];
            }
            return ['valid' => false, 'value' => 0, 'message' => 'Must be a number.'];
            
        case 'date':
            $dateObj = DateTime::createFromFormat('Y-m-d', $input);
            if ($dateObj && $dateObj->format('Y-m-d') === $input) {
                return ['valid' => true, 'value' => $input, 'message' => ''];
            }
            return ['valid' => false, 'value' => '', 'message' => 'Invalid date format.'];
            
        case 'time':
            $timeObj = DateTime::createFromFormat('H:i', $input);
            if ($timeObj && $timeObj->format('H:i') === $input) {
                return ['valid' => true, 'value' => $input, 'message' => ''];
            }
            return ['valid' => false, 'value' => '', 'message' => 'Invalid time format.'];
            
        case 'text':
        default:
            $sanitized = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            return ['valid' => true, 'value' => $sanitized, 'message' => ''];
    }
}

/**
 * Log security events
 * @param string $event Event description
 * @param string $level Severity level (INFO, WARNING, ERROR)
 */
function logSecurityEvent($event, $level = 'INFO') {
    $logFile = __DIR__ . '/../logs/security.log';
    $logDir = dirname($logFile);
    
    // Create logs directory if it doesn't exist
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user = $_SESSION['username'] ?? 'anonymous';
    $logMessage = "[{$timestamp}] [{$level}] IP:{$ip} User:{$user} - {$event}\n";
    
    @file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}
