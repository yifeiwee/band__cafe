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

// Fetch all practice requests (join with user for info)
$sql = "SELECT pr.id, pr.date, pr.start_time, pr.end_time, pr.transport_to_venue, pr.transport_to_home, pr.pickup_time, pr.pickup_address, pr.dropoff_time, pr.dropoff_address, pr.target_goal, pr.status, u.username 
        FROM practice_requests pr
        JOIN users u ON pr.user_id = u.id
        ORDER BY pr.date DESC";
$result = $mysqli->query($sql);
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
                <a href="logout.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Logout</a>
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
                        echo "<a href='admin.php?action=reject&id={$row['id']}' class='text-red-600 hover:text-red-800 font-medium'>Reject</a>";
                        echo "</td>";
                        echo "</tr>";
                    } ?>
                </tbody>
            </table>
            <?php if ($result->num_rows === 0): ?>
                <p class="text-center text-gray-500 mt-4">No practice requests found.</p>
            <?php endif; ?>
        </div>
        <nav class="mt-6 flex justify-center">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 font-medium">Back to Dashboard</a>
        </nav>
    </div>
</body>
</html>
