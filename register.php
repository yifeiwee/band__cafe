<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
configureSecureSession();
session_start();
setSecurityHeaders();

$error_message = '';
$success_message = '';

if (isset($_POST['register'])) {
    // CSRF token validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error_message = "Invalid security token. Please try again.";
        logSecurityEvent("CSRF token validation failed for registration attempt", "WARNING");
    } elseif (empty($_POST['username']) || empty($_POST['password'])) {
        $error_message = "Username and password are required.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        // Validate username
        if (strlen($username) < 3 || strlen($username) > 50) {
            $error_message = "Username must be between 3 and 50 characters.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $error_message = "Username can only contain letters, numbers, and underscores.";
        } else {
            // Validate password strength
            $passwordValidation = validatePasswordStrength($password);
            if (!$passwordValidation['valid']) {
                $error_message = $passwordValidation['message'];
            } else {
                // Check if username already exists using prepared statement
                $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
                if (!$stmt) {
                    $error_message = "System error. Please try again later.";
                    error_log("Database prepare error: " . $mysqli->error);
                } else {
                    $stmt->bind_param("s", $username);
                    if (!$stmt->execute()) {
                        $error_message = "System error. Please try again later.";
                        error_log("Database execute error: " . $stmt->error);
                    } else {
                        $stmt->store_result();
                        if ($stmt->num_rows > 0) {
                            $error_message = "Username already taken.";
                        } else {
                            // Hash password securely
                            $hash = password_hash($password, PASSWORD_DEFAULT);
                            
                            // Insert new user using prepared statement
                            $insert_stmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                            if (!$insert_stmt) {
                                $error_message = "System error. Please try again later.";
                                error_log("Database prepare error: " . $mysqli->error);
                            } else {
                                $insert_stmt->bind_param("ss", $username, $hash);
                                if ($insert_stmt->execute()) {
                                    $success_message = "Registration successful. <a href='login.php' class='underline'>Log in</a>";
                                    logSecurityEvent("New user registered: $username", "INFO");
                                } else {
                                    $error_message = "Registration failed. Please try again.";
                                    error_log("Database insert error: " . $insert_stmt->error);
                                }
                                $insert_stmt->close();
                            }
                        }
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Band Cafe - Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .floating-animation {
            animation: floating 6s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="font-sans text-gray-800 flex items-center justify-center min-h-screen p-4 register-page">
    <!-- Background decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-white/10 rounded-full blur-3xl floating-animation"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-slate-300/20 rounded-full blur-3xl floating-animation" style="animation-delay: -3s;"></div>
        <div class="absolute top-1/2 right-1/3 w-48 h-48 bg-slate-300/15 rounded-full blur-2xl floating-animation" style="animation-delay: -1.5s;"></div>
    </div>

    <div class="w-full max-w-md relative register-card">
        <?php
        ob_start();
        ?>
        <!-- Logo and title section -->
        <div class="text-center mb-8 register-header">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-3xl mb-6 logo-container">
                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">Join Band Cafe</h1>
            <p class="text-white/80 text-lg">Create your account to get started</p>
        </div>

        <!-- Error/Success messages -->
        <?php if ($error_message): ?>
            <div class="mb-6 p-4 bg-red-500/20 border border-red-400/30 text-red-100 rounded-2xl backdrop-blur-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                    </svg>
                    <span><?php echo $error_message; ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="mb-6 p-4 bg-green-500/20 border border-green-400/30 text-green-100 rounded-2xl backdrop-blur-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <span><?php echo $success_message; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Registration form -->
        <form method="post" action="register.php" class="space-y-6">
            <?php echo csrfTokenField(); ?>
            <?php
            $id = 'username';
            $label = 'Username';
            $placeholder = 'Choose a unique username';
            include 'components/input.php';
            ?>
            <?php
            $id = 'password';
            $label = 'Password';
            $type = 'password';
            $placeholder = 'Create a secure password';
            include 'components/input.php';
            ?>
            <?php
            $text = 'Create Account';
            $type = 'submit';
            $name = 'register';
            $color = 'success';
            $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>';
            include 'components/button.php';
            ?>
        </form>
        
        <!-- Login link -->
        <div class="mt-8 text-center register-footer">
            <p class="text-white/80">Already have an account?</p>
            <a href="login.php" class="inline-flex items-center mt-2 text-white font-semibold hover:text-white/80 transition-colors duration-200">
                <span>Sign In</span>
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                </svg>
            </a>
        </div>
        
        <?php
        $content = ob_get_clean();
        $variant = 'glass';
        $maxWidth = 'max-w-md';
        include 'components/card.php';
        ?>    </div>
    <!-- <script src="assets/js/script.js"></script> --> <!-- Disabled JS for debugging -->
</body>
</html>
