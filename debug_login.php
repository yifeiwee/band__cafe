<?php
// Debug script to diagnose login/registration issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Band Cafe Debug Information</h2>";

// Test 1: Database connection
echo "<h3>1. Database Connection Test</h3>";
require 'config.php';

if ($mysqli->connect_error) {
    echo "❌ Database connection failed: " . $mysqli->connect_error . "<br>";
} else {
    echo "✅ Database connection successful<br>";
    echo "Database: " . $mysqli->get_server_info() . "<br>";
}

// Test 2: Check if users table exists
echo "<h3>2. Users Table Check</h3>";
$result = $mysqli->query("SHOW TABLES LIKE 'users'");
if ($result && $result->num_rows > 0) {
    echo "✅ Users table exists<br>";
    
    // Check table structure
    echo "<h4>Table Structure:</h4>";
    $result = $mysqli->query("DESCRIBE users");
    if ($result) {
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
        }
        echo "</table>";
    }
    
    // Check if any users exist
    $result = $mysqli->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<br>Total users in database: " . $row['count'] . "<br>";
    }
    
} else {
    echo "❌ Users table does not exist<br>";
    echo "<strong>Solution: You need to run the database_schema.sql to create the users table</strong><br>";
}

// Test 3: Test form data processing
echo "<h3>3. Form Data Test</h3>";
if ($_POST) {
    echo "✅ POST data received:<br>";
    foreach ($_POST as $key => $value) {
        echo "$key: " . htmlspecialchars($value) . "<br>";
    }
} else {
    echo "ℹ️ No POST data received (submit the test form below)<br>";
}

// Test 4: Test password hashing
echo "<h3>4. Password Hashing Test</h3>";
$test_password = "test123";
$hash = password_hash($test_password, PASSWORD_DEFAULT);
echo "Test password: $test_password<br>";
echo "Generated hash: $hash<br>";
echo "Verification test: " . (password_verify($test_password, $hash) ? "✅ PASS" : "❌ FAIL") . "<br>";

// Test 5: Test database INSERT operation
echo "<h3>5. Database INSERT Test</h3>";
if (isset($_POST['test_insert'])) {
    $test_username = "debug_test_" . time();
    $test_password_hash = password_hash("test123", PASSWORD_DEFAULT);
    
    echo "Attempting to insert test user: $test_username<br>";
    
    $stmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    if (!$stmt) {
        echo "❌ Prepare failed: " . $mysqli->error . "<br>";
    } else {
        $stmt->bind_param("ss", $test_username, $test_password_hash);
        if ($stmt->execute()) {
            echo "✅ Test user inserted successfully<br>";
            echo "Inserted ID: " . $mysqli->insert_id . "<br>";
            
            // Clean up - delete the test user
            $delete_stmt = $mysqli->prepare("DELETE FROM users WHERE username = ?");
            $delete_stmt->bind_param("s", $test_username);
            $delete_stmt->execute();
            echo "✅ Test user cleaned up<br>";
        } else {
            echo "❌ Insert failed: " . $stmt->error . "<br>";
        }
        $stmt->close();
    }
}
?>

<hr>
<h3>Test Forms</h3>

<!-- Test form data submission -->
<form method="POST" style="margin: 10px 0; padding: 10px; border: 1px solid #ccc;">
    <h4>Test Form Data Submission</h4>
    <input type="text" name="test_username" placeholder="Test Username" required><br><br>
    <input type="password" name="test_password" placeholder="Test Password" required><br><br>
    <button type="submit" name="test_form">Test Form Submission</button>
</form>

<!-- Test database INSERT -->
<form method="POST" style="margin: 10px 0; padding: 10px; border: 1px solid #ccc;">
    <h4>Test Database INSERT</h4>
    <button type="submit" name="test_insert">Test Database Insert Operation</button>
</form>

<hr>
<p><a href="register.php">← Back to Register</a> | <a href="login.php">← Back to Login</a></p>