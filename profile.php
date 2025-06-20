<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';  // Assuming this has database connection

// Fetch current user data first
$stmt = $mysqli->prepare("SELECT username, instrument, section FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($current_username, $current_instrument, $current_section);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $sets = [];
    $types = 'i';  // For the WHERE clause
    $params = [$user_id];
    
    if (isset($_POST['username']) && $_POST['username'] != $current_username && strlen($_POST['username']) <= 50) {
        $sets[] = 'username = ?';
        $types .= 's';
        $params[] = $_POST['username'];
    }
    
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        $sets[] = 'password = ?';
        $types .= 's';
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $params[] = $hashed_password;
    }
    
    if (isset($_POST['instrument']) && $_POST['instrument'] != $current_instrument) {
        $sets[] = 'instrument = ?';
        $types .= 's';
        $params[] = $_POST['instrument'];
    }
    
    if (isset($_POST['section']) && $_POST['section'] != $current_section) {
        $sets[] = 'section = ?';
        $types .= 's';
        $params[] = $_POST['section'];
    }
    
    if (!empty($sets)) {
        $setString = implode(', ', $sets);
        $sql = "UPDATE users SET $setString WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
    $bindParams = array_merge(array_slice($params, 1), [$params[0]]);
    $stmt->bind_param($types, ...array_merge(array_slice($params, 1), [$params[0]]));
        $stmt->execute();
        $stmt->close();
    }
    
    header('Location: dashboard.php');
    exit();
}
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
<body class="font-sans text-gray-900">
<div class="bg-gradient-to-br from-slate-500 to-slate-600 text-slate-900 p-6 rounded-2xl shadow-lg mx-auto max-w-md">
    <h2 class="text-2xl font-bold mb-4 text-white">Edit Profile</h2>
    <form method="POST" action="profile.php" class="space-y-4">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-white">Username</label>
<input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($current_username); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
<div class="mb-4">
                <label for="password" class="block text-sm font-medium text-white">New Password</label>
<input type="password" id="password" name="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">  
            </div>
            <div class="mb-4">
                <label for="instrument" class="block text-sm font-medium text-white">Instrument</label>
<select id="instrument" name="instrument" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    <option value="" class="text-gray-900">Select Instrument</option>
    <option value="Flute" <?php echo ($current_instrument == 'Flute') ? 'selected' : ''; ?> class="text-gray-900">Flute</option>
    <option value="Clarinet" <?php echo ($current_instrument == 'Clarinet') ? 'selected' : ''; ?> class="text-gray-900">Clarinet</option>
    <option value="Saxophone" <?php echo ($current_instrument == 'Saxophone') ? 'selected' : ''; ?> class="text-gray-900">Saxophone</option>
    <option value="Horn" <?php echo ($current_instrument == 'Horn') ? 'selected' : ''; ?> class="text-gray-900">Horn</option>
    <option value="Trumpet" <?php echo ($current_instrument == 'Trumpet') ? 'selected' : ''; ?> class="text-gray-900">Trumpet</option>
    <option value="Trombone" <?php echo ($current_instrument == 'Trombone') ? 'selected' : ''; ?> class="text-gray-900">Trombone</option>
    <option value="Euphonium" <?php echo ($current_instrument == 'Euphonium') ? 'selected' : ''; ?> class="text-gray-900">Euphonium</option>
    <option value="Tuba" <?php echo ($current_instrument == 'Tuba') ? 'selected' : ''; ?> class="text-gray-900">Tuba</option>
    <option value="Percussion" <?php echo ($current_instrument == 'Percussion') ? 'selected' : ''; ?> class="text-gray-900">Percussion</option>    
                </select>
            </div>
            <div class="mb-4">
                <label for="section" class="block text-sm font-medium text-white">Section</label>
<select id="section" name="section" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
    <option value="" class="text-gray-900">Select Section</option>
    <option value="Woodwind" <?php echo ($current_section == 'Woodwind') ? 'selected' : ''; ?> class="text-gray-900">Woodwind</option>
    <option value="Brass" <?php echo ($current_section == 'Brass') ? 'selected' : ''; ?> class="text-gray-900">Brass</option>
    <option value="Percussion" <?php echo ($current_section == 'Percussion') ? 'selected' : ''; ?> class="text-gray-900">Percussion</option>
                </select>
            </div>
<button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition duration-200">Save Changes</button>
<a href="dashboard.php" class="ml-4 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition duration-200">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
