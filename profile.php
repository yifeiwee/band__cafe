<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';  // Assuming this has database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  // Hash the password
    $instrument = $_POST['instrument'];
    $section = $_POST['section'];
    
    // Update query
$stmt = $mysqli->prepare("UPDATE users SET username = ?, password = ?, instrument = ?, section = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $username, $password, $instrument, $section, $user_id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: dashboard.php');
    exit();
}

// Fetch current user data
$stmt = $mysqli->prepare("SELECT username, instrument, section FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($current_username, $current_instrument, $current_section);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="font-sans text-gray-800">
<div class="bg-gradient-to-br from-slate-500 to-slate-600 text-white p-6 rounded-2xl shadow-lg">
    <h2 class="text-2xl font-bold mb-4">Edit Profile</h2>
    <form method="POST" action="profile.php" class="space-y-4">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium">Username</label>
<input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($current_username); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium">New Password</label>
<input type="password" id="password" name="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">  
            </div>
            <div class="mb-4">
                <label for="instrument" class="block text-sm font-medium">Instrument</label>
<select id="instrument" name="instrument" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    <option value="">Select Instrument</option>
    <option value="Flute" <?php echo ($current_instrument == 'Flute') ? 'selected' : ''; ?>>Flute</option>
    <option value="Clarinet" <?php echo ($current_instrument == 'Clarinet') ? 'selected' : ''; ?>>Clarinet</option>
    <option value="Saxophone" <?php echo ($current_instrument == 'Saxophone') ? 'selected' : ''; ?>>Saxophone</option>
    <option value="Horn" <?php echo ($current_instrument == 'Horn') ? 'selected' : ''; ?>>Horn</option>
    <option value="Trumpet" <?php echo ($current_instrument == 'Trumpet') ? 'selected' : ''; ?>>Trumpet</option>
    <option value="Trombone" <?php echo ($current_instrument == 'Trombone') ? 'selected' : ''; ?>>Trombone</option>
    <option value="Euphonium" <?php echo ($current_instrument == 'Euphonium') ? 'selected' : ''; ?>>Euphonium</option>
    <option value="Tuba" <?php echo ($current_instrument == 'Tuba') ? 'selected' : ''; ?>>Tuba</option>
    <option value="Percussion" <?php echo ($current_instrument == 'Percussion') ? 'selected' : ''; ?>>Percussion</option>
                    <option value="">Select Instrument</option>
                    <option value="Flute">Flute</option>
                    <option value="Clarinet">Clarinet</option>
                    <option value="Saxophone">Saxophone</option>
                    <option value="Horn">Horn</option>
                    <option value="Trumpet">Trumpet</option>
                    <option value="Trombone">Trombone</option>
                    <option value="Euphonium">Euphonium</option>
                    <option value="Tuba">Tuba</option>
                    <option value="Percussion">Percussion</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="section" class="block text-sm font-medium">Section</label>
<select id="section" name="section" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    <option value="">Select Section</option>
    <option value="Woodwind" <?php echo ($current_section == 'Woodwind') ? 'selected' : ''; ?>>Woodwind</option>
    <option value="Brass" <?php echo ($current_section == 'Brass') ? 'selected' : ''; ?>>Brass</option>
    <option value="Percussion" <?php echo ($current_section == 'Percussion') ? 'selected' : ''; ?>>Percussion</option>
                    <option value="">Select Section</option>
                    <option value="Woodwind">Woodwind</option>
                    <option value="Brass">Brass</option>
                    <option value="Percussion">Percussion</option>
                </select>
            </div>
<button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition duration-200">Save Changes</button>
<a href="dashboard.php" class="ml-4 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition duration-200">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
