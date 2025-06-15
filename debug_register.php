<?php
// Enhanced registration script with detailed error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<pre>DEBUG: Starting registration process\n";

require 'config.php';

$error_message = '';
$success_message = '';

echo "DEBUG: Database connection status: " . ($mysqli->connect_error ? "FAILED - " . $mysqli->connect_error : "SUCCESS") . "\n";

if (isset($_POST['register'])) {
    echo "DEBUG: POST data received\n";
    echo "DEBUG: POST contents: " . print_r($_POST, true) . "\n";
    
    if (empty($_POST['username']) || empty($_POST['password'])) {
        echo "DEBUG: Empty username or password\n";
        $error_message = "Username and password are required.";
    } else {
        $username = $mysqli->real_escape_string($_POST['username']);
        $password = $_POST['password'];
        
        echo "DEBUG: Processing username: $username\n";
        echo "DEBUG: Password length: " . strlen($password) . "\n";

        // Check if username already exists
        echo "DEBUG: Checking if username exists\n";
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        if (!$stmt) {
            echo "DEBUG: Prepare failed for SELECT: " . $mysqli->error . "\n";
            $error_message = "Database error: " . $mysqli->error;
        } else {
            $stmt->bind_param("s", $username);
            if (!$stmt->execute()) {
                echo "DEBUG: Execute failed for SELECT: " . $stmt->error . "\n";
                $error_message = "Database error: " . $stmt->error;
            } else {
                $stmt->store_result();
                echo "DEBUG: Username check - rows found: " . $stmt->num_rows . "\n";
                
                if ($stmt->num_rows > 0) {
                    echo "DEBUG: Username already exists\n";
                    $error_message = "Username already taken.";
                } else {
                    echo "DEBUG: Username available, proceeding with registration\n";
                    
                    // Hash password securely
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    echo "DEBUG: Password hashed successfully: " . (strlen($hash) > 0 ? "YES" : "NO") . "\n";
                    echo "DEBUG: Hash length: " . strlen($hash) . "\n";
                    
                    // Insert new user
                    echo "DEBUG: Preparing INSERT statement\n";
                    $insert_stmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                    if (!$insert_stmt) {
                        echo "DEBUG: Prepare failed for INSERT: " . $mysqli->error . "\n";
                        $error_message = "Database error: " . $mysqli->error;
                    } else {
                        echo "DEBUG: Binding parameters\n";
                        $insert_stmt->bind_param("ss", $username, $hash);
                        
                        echo "DEBUG: Executing INSERT\n";
                        if ($insert_stmt->execute()) {
                            echo "DEBUG: INSERT successful! New user ID: " . $mysqli->insert_id . "\n";
                            $success_message = "Registration successful. <a href='login.php' class='underline'>Log in</a>";
                        } else {
                            echo "DEBUG: INSERT failed: " . $insert_stmt->error . "\n";
                            echo "DEBUG: MySQL error code: " . $insert_stmt->errno . "\n";
                            $error_message = "Error: " . $insert_stmt->error;
                        }
                        $insert_stmt->close();
                    }
                }
            }
            $stmt->close();
        }
    }
}

echo "DEBUG: Final error message: $error_message\n";
echo "DEBUG: Final success message: $success_message\n";
echo "</pre>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Band Cafe - Debug Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        pre { background: #f4f4f4; padding: 10px; margin: 10px; border: 1px solid #ddd; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    </style>
</head>
<body class="font-sans text-gray-800 flex flex-col items-center min-h-screen p-4">
    
    <div class="w-full max-w-md bg-white/90 backdrop-blur-sm rounded-2xl p-8 shadow-2xl mt-8">
        <h1 class="text-2xl font-bold text-center mb-6">Debug Registration</h1>
        
        <!-- Error/Success messages -->
        <?php if ($error_message): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded-lg">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-700 rounded-lg">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Registration form -->
        <form method="post" action="debug_register.php" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username *</label>
                <input 
                    id="username" 
                    type="text" 
                    name="username" 
                    required
                    placeholder="Choose a unique username"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password *</label>
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    required
                    placeholder="Create a secure password"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>
            <button 
                type="submit" 
                name="register"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors duration-200"
            >
                Create Account (Debug Mode)
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="register.php" class="text-blue-600 hover:underline">← Back to Normal Register</a> |
            <a href="debug_login.php" class="text-blue-600 hover:underline">Debug Info</a>
        </div>
    </div>
</body>
</html>