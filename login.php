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
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-gray-100">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-semibold text-gray-800">Band Cafe</h1>
                <p class="text-gray-500 mt-1">Sign in to your account</p>
            </div>
            <form method="post" action="login.php" class="space-y-5">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input id="username" type="text" name="username" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" type="password" name="password" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <button type="submit" name="login" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">Log In</button>
            </form>
            <p class="mt-5 text-center text-sm text-gray-500">Don't have an account? <a href="register.php" class="text-blue-600 hover:text-blue-800 font-medium">Register</a></p>
        </div>
    </div>
</body>
</html>
<?php
session_start(); // Resume session if exists

require 'config.php';
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
            echo "<script>alert('Login successful!'); window.location='dashboard.php';</script>";
        } else {
            echo "<p style='color:red;'>Invalid credentials.</p>";
        }
    } else {
        echo "<p style='color:red;'>User not found.</p>";
    }
    $stmt->close();
}
?>
