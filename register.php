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
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-gray-100">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-semibold text-gray-800">Band Cafe</h1>
                <p class="text-gray-500 mt-1">Create a new account</p>
            </div>
            <form method="post" action="register.php" class="space-y-5">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input id="username" type="text" name="username" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" type="password" name="password" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <button type="submit" name="register" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">Register</button>
            </form>
            <p class="mt-5 text-center text-sm text-gray-500">Already have an account? <a href="login.php" class="text-blue-600 hover:text-blue-800 font-medium">Log In</a></p>
        </div>
    </div>
</body>
</html>
<?php
require 'config.php';
if (isset($_POST['register'])) {
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Check if username already exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<p style='color:red;'>Username already taken.</p>";
    } else {
        // Hash password securely
        $hash = password_hash($password, PASSWORD_DEFAULT); // strong one-way hash
        // Insert new user
        $stmt = $mysqli->prepare("INSERT INTO users (username,password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hash);
        if ($stmt->execute()) {
            echo "<p style='color:green;'>Registration successful. <a href='login.php'>Log in</a></p>";
        } else {
            echo "<p style='color:red;'>Error: " . $mysqli->error . "</p>";
        }
    }
    $stmt->close();
}
?>
