<?php
require 'config.php';

$error_message = '';
$success_message = '';

if (isset($_POST['register'])) {
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Check if username already exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $error_message = "Username already taken.";
    } else {
        // Hash password securely
        $hash = password_hash($password, PASSWORD_DEFAULT); // strong one-way hash
        // Insert new user
        $stmt = $mysqli->prepare("INSERT INTO users (username,password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hash);
        if ($stmt->execute()) {
            $success_message = "Registration successful. <a href='login.php' class='underline'>Log in</a>";
        } else {
            $error_message = "Error: " . $mysqli->error;
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Band Cafe - Register</title>
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
            <p class="text-gray-500 mt-1">Create a new account</p>
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
        <form method="post" action="register.php" class="space-y-5">
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
            $text = 'Register';
            $type = 'submit';
            $name = 'register';  // Add name attribute
            include 'components/button.php';
            ?>
        </form>
        <p class="mt-5 text-center text-sm text-gray-500">Already have an account? <a href="login.php" class="text-blue-600 hover:text-blue-800 font-medium">Log In</a></p>
        <?php
        $content = ob_get_clean();
        $maxWidth = 'max-w-md';
        include 'components/card.php';
        ?>
    </div>
</body>
</html>
