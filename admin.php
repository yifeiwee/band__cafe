<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}
require 'config.php';

// Handle approve/reject actions via GET parameters
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] === 'approve') {
        $mysqli->query("UPDATE practice_requests SET status='approved' WHERE id=$id");
    }
    if ($_GET['action'] === 'reject') {
        $mysqli->query("UPDATE practice_requests SET status='rejected' WHERE id=$id");
    }
    header("Location: admin.php");
    exit();
}

// Handle attendance confirmation and points assignment
if (isset($_POST['confirm_attendance'])) {
    $request_id = intval($_POST['request_id']);
    $user_id = intval($_POST['user_id']);
    $date = $_POST['date'];
    $attended = isset($_POST['attended']) ? 1 : 0;
    $points = intval($_POST['points']);
    
    // Check if record already exists
    $check_sql = "SELECT id FROM practice_records WHERE request_id = $request_id";
    $check_result = $mysqli->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        // Update existing record
        $update_sql = "UPDATE practice_records SET attended = $attended, points = $points WHERE request_id = $request_id";
        $mysqli->query($update_sql);
    } else {
        // Insert new record
        $insert_sql = "INSERT INTO practice_records (request_id, user_id, date, attended, points) VALUES ($request_id, $user_id, '$date', $attended, $points)";
        $mysqli->query($insert_sql);
    }
    header("Location: admin.php");
    exit();
}

// Handle user update
if (isset($_POST['update_user'])) {
    $user_id = intval($_POST['user_id']);
    $username = $mysqli->real_escape_string($_POST['username']);
    $role = $mysqli->real_escape_string($_POST['role']);
    
    $update_user_sql = "UPDATE users SET username = '$username', role = '$role' WHERE id = $user_id";
    $mysqli->query($update_user_sql);
    header("Location: admin.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete_user']) && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    // Prevent deleting the current admin
    if ($user_id != $_SESSION['user_id']) {
        $delete_user_sql = "DELETE FROM users WHERE id = $user_id";
        $mysqli->query($delete_user_sql);
        // Also delete related data if necessary
        $mysqli->query("DELETE FROM practice_requests WHERE user_id = $user_id");
        $mysqli->query("DELETE FROM practice_records WHERE user_id = $user_id");
    }
    header("Location: admin.php");
    exit();
}

// Handle practice request deletion
if (isset($_GET['delete_request']) && isset($_GET['request_id'])) {
    $request_id = intval($_GET['request_id']);
    $delete_request_sql = "DELETE FROM practice_requests WHERE id = $request_id";
    $mysqli->query($delete_request_sql);
    // Also delete related practice record if exists
    $mysqli->query("DELETE FROM practice_records WHERE request_id = $request_id");
    header("Location: admin.php");
    exit();
}

// Handle practice record deletion
if (isset($_GET['delete_record']) && isset($_GET['record_id'])) {
    $record_id = intval($_GET['record_id']);
    $delete_record_sql = "DELETE FROM practice_records WHERE id = $record_id";
    $mysqli->query($delete_record_sql);
    header("Location: admin.php");
    exit();
}

// Fetch all practice requests (join with user for info)
$sql = "SELECT pr.id, pr.date, pr.start_time, pr.end_time, pr.transport_to_venue, pr.transport_to_home, pr.pickup_time, pr.pickup_address, pr.dropoff_time, pr.dropoff_address, pr.target_goal, pr.status, u.username, u.id as user_id
        FROM practice_requests pr
        JOIN users u ON pr.user_id = u.id
        ORDER BY pr.date DESC";
$result = $mysqli->query($sql);

// Fetch completed sessions for attendance confirmation and points (approved and past date)
$completed_sql = "SELECT pr.id, pr.date, pr.user_id, u.username, prc.attended, prc.points, prc.id as record_id
                  FROM practice_requests pr
                  JOIN users u ON pr.user_id = u.id
                  LEFT JOIN practice_records prc ON pr.id = prc.request_id
                  WHERE pr.status = 'approved' AND pr.date <= CURDATE()
                  ORDER BY pr.date DESC";
$completed_result = $mysqli->query($completed_sql);

// Fetch all registered users for user management
$users_sql = "SELECT id, username, role FROM users ORDER BY username ASC";
$users_result = $mysqli->query($users_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Band Cafe - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <div class="container mx-auto p-6">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-semibold text-gray-800">Band Cafe</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="text-sm text-slate-600 hover:text-slate-800 font-medium">Logout</a>
            </div>
        </header>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-6">
            <h2 class="text-xl font-medium text-gray-700 mb-2">Admin Dashboard: Manage Practice Requests</h2>
            <p class="text-gray-500">Review and manage all practice requests below.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-3 text-left text-sm font-medium text-gray-700">User</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Date</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Start</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">End</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Transport to Venue</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Transport to Home</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Goal</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Status</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while ($row = $result->fetch_assoc()) {
                        echo "<tr class='hover:bg-gray-50'>";
                        echo "<td class='p-3 text-gray-800'>{$row['username']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['date']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['start_time']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['end_time']}</td>";
                        echo "<td class='p-3 text-gray-800'>" . ($row['transport_to_venue'] ? 'Yes' . ($row['pickup_time'] ? " (Pickup at {$row['pickup_time']}" . ($row['pickup_address'] ? " from {$row['pickup_address']}" : '') : '') : 'No') . "</td>";
                        echo "<td class='p-3 text-gray-800'>" . ($row['transport_to_home'] ? 'Yes' . ($row['dropoff_time'] ? " (Dropoff at {$row['dropoff_time']}" . ($row['dropoff_address'] ? " to {$row['dropoff_address']}" : '') : '') : 'No') . "</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['target_goal']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['status']}</td>";
                        echo "<td class='p-3'>";
                        echo "<a href='admin.php?action=approve&id={$row['id']}' class='text-green-600 hover:text-green-800 font-medium mr-3'>Approve</a>";
                        echo "<a href='admin.php?action=reject&id={$row['id']}' class='text-red-600 hover:text-red-800 font-medium mr-2'>Reject</a>";
                        echo "<a href='admin.php?delete_request=true&request_id={$row['id']}' class='text-sm bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700' onclick='return confirm(\"Are you sure you want to delete this request?\");'>Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    } ?>
                </tbody>
            </table>
            <?php if ($result->num_rows === 0): ?>
                <p class="text-center text-gray-500 mt-4">No practice requests found.</p>
            <?php endif; ?>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-6">
            <h2 class="text-xl font-medium text-gray-700 mb-2">Confirm Attendance & Assign Points</h2>
            <p class="text-gray-500">Confirm attendance and assign points for completed practice sessions.</p>
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="p-3 text-left text-sm font-medium text-gray-700">User</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Date</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Attended</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Points</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($row = $completed_result->fetch_assoc()) {
                            echo "<tr class='hover:bg-gray-50'>";
                            echo "<td class='p-3 text-gray-800'>{$row['username']}</td>";
                            echo "<td class='p-3 text-gray-800'>{$row['date']}</td>";
                            echo "<td class='p-3 text-gray-800'>" . (isset($row['attended']) ? ($row['attended'] ? 'Yes' : 'No') : 'Not Set') . "</td>";
                            echo "<td class='p-3 text-gray-800'>" . (isset($row['points']) ? $row['points'] : '0') . "</td>";
                            echo "<td class='p-3'>";
                            echo "<form method='POST' action='admin.php'>";
                            echo "<input type='hidden' name='request_id' value='{$row['id']}'>";
                            echo "<input type='hidden' name='user_id' value='{$row['user_id']}'>";
                            echo "<input type='hidden' name='date' value='{$row['date']}'>";
                            echo "<div class='flex items-center space-x-2'>";
                            echo "<label class='flex items-center space-x-1'>";
                            echo "<input type='checkbox' name='attended' value='1' " . (isset($row['attended']) && $row['attended'] ? 'checked' : '') . ">";
                            echo "<span class='text-sm text-gray-700'>Attended</span>";
                            echo "</label>";
                            echo "<input type='number' name='points' value='" . (isset($row['points']) ? $row['points'] : '0') . "' class='w-20 p-1 border border-gray-300 rounded'>";
                            echo "<button type='submit' name='confirm_attendance' class='text-sm bg-slate-600 text-white px-2 py-1 rounded hover:bg-slate-700'>Save</button>";
                            if (isset($row['record_id']) && $row['record_id']) {
                                echo "<a href='admin.php?delete_record=true&record_id={$row['record_id']}' class='text-sm bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 ml-2' onclick='return confirm(\"Are you sure you want to delete this record?\");'>Delete</a>";
                            }
                            echo "</div>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        } ?>
                    </tbody>
                </table>
                <?php if ($completed_result->num_rows === 0): ?>
                    <p class="text-center text-gray-500 mt-4">No completed practice sessions found.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-6">
            <h2 class="text-xl font-medium text-gray-700 mb-2">Manage Users</h2>
            <p class="text-gray-500">View, update, and delete registered users below.</p>
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Username</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Role</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($user = $users_result->fetch_assoc()) {
                            echo "<tr class='hover:bg-gray-50'>";
                            echo "<td class='p-3 text-gray-800'>{$user['username']}</td>";
                            echo "<td class='p-3 text-gray-800'>{$user['role']}</td>";
                            echo "<td class='p-3'>";
                            echo "<form method='POST' action='admin.php' class='inline-block'>";
                            echo "<input type='hidden' name='user_id' value='{$user['id']}'>";
                            echo "<div class='flex items-center space-x-2'>";
                            echo "<input type='text' name='username' value='{$user['username']}' class='w-32 p-1 border border-gray-300 rounded'>";
                            echo "<select name='role' class='p-1 border border-gray-300 rounded'>";
                            echo "<option value='user'" . ($user['role'] == 'user' ? ' selected' : '') . ">User</option>";
                            echo "<option value='admin'" . ($user['role'] == 'admin' ? ' selected' : '') . ">Admin</option>";
                            echo "</select>";
                            echo "<button type='submit' name='update_user' class='text-sm bg-slate-600 text-white px-2 py-1 rounded hover:bg-slate-700'>Update</button>";
                            if ($user['id'] != $_SESSION['user_id']) {
                                echo "<a href='admin.php?delete_user=true&user_id={$user['id']}' class='text-sm bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a>";
                            }
                            echo "</div>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        } ?>
                    </tbody>
                </table>
                <?php if ($users_result->num_rows === 0): ?>
                    <p class="text-center text-gray-500 mt-4">No registered users found.</p>
                <?php endif; ?>
            </div>
        </div>
        <nav class="mt-6 flex justify-center space-x-6">
            <a href="dashboard.php" class="text-slate-600 hover:text-slate-800 font-medium">Back to Dashboard</a>
            <span class="text-gray-300">|</span>
            <a href="roster.php" class="text-slate-600 hover:text-slate-800 font-medium">Roster Management</a>
        </nav>
    </div>
</body>
</html>
