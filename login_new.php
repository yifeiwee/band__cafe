<?php
session_start(); // Resume session if exists

require 'config.php';

$error_message = '';
$success_message = '';

if (isset($_POST['login'])) {
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Fetch user record
    $stmt = $mysqli->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $hash, $role);
    if ($stmt->fetch()) {
        // Verify password
        if (password_verify($password, $hash)) {
            // Success: set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Invalid credentials.";
        }
    } else {
        $error_message = "User not found.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Band Cafe - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <div class="container mx-auto p-4 flex justify-center items-center min-h-screen">
        <?php
        // Include components
        // Removed redundant includes to prevent undefined variable errors
        // Prepare content for the card
        ob_start();
        ?>
        <div class="mb-6 text-center">
            <p class="text-gray-500 mt-1">Sign in to your account</p>
        </div>
        <?php if ($error_message): ?>
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="login.php" class="space-y-5">
            <?php
            $id = 'username';
            $label = 'Username';
            include 'components/input.php';
            ?>
            <?php
            $id = 'password';
            $label = 'Password';
            $type = 'password';
            include 'components/input.php';
            ?>
            <?php
            $text = 'Log In';
            $type = 'submit';
            $name = 'login';  // Add name attribute
            include 'components/button.php';
            ?>
        </form>
        <p class="mt-5 text-center text-sm text-gray-500">Don't have an account? <a href="register.php" class="text-blue-600 hover:text-blue-800 font-medium">Register</a></p>
        <?php
        $content = ob_get_clean();
        $maxWidth = 'max-w-md';
        include 'components/card.php';
        ?>
    </div>
</body>
</html>
