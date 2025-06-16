<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require 'config.php';

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');

// Query all sessions with attendance and points
$sql = "SELECT pr.id, pr.date, pr.start_time, pr.end_time, pr.transport_to_venue, pr.transport_to_home, pr.pickup_time, pr.pickup_address, pr.dropoff_time, pr.dropoff_address, pr.target_goal, pr.status, prc.attended, prc.points
        FROM practice_requests pr
        LEFT JOIN practice_records prc ON pr.id = prc.request_id
        WHERE pr.user_id = ?
        ORDER BY pr.date DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Split results into past and future sessions
$past_sessions = [];
$future_sessions = [];
$today = date('Y-m-d');
while ($row = $result->fetch_assoc()) {
    if ($row['date'] < $today) {
        $past_sessions[] = $row;
    } else {
        $future_sessions[] = $row;
    }
}

// Calculate totals for attended sessions and points
$total_attended_sql = "SELECT COUNT(*) as total_attended, SUM(points) as total_points
                       FROM practice_records
                       WHERE user_id = ? AND attended = 1";
$total_stmt = $mysqli->prepare($total_attended_sql);
$total_stmt->bind_param("i", $userId);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$totals = $total_result->fetch_assoc();
$total_attended = $totals['total_attended'];
$total_points = $totals['total_points'] ?? 0;

// Handle deletion of pending practice requests
if (isset($_GET['delete_request']) && isset($_GET['request_id'])) {
    $request_id = intval($_GET['request_id']);
    // Ensure the request belongs to the user and is pending
    $check_sql = "SELECT id FROM practice_requests WHERE id = ? AND user_id = ? AND status = 'pending'";
    $check_stmt = $mysqli->prepare($check_sql);
    $check_stmt->bind_param("ii", $request_id, $userId);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $delete_sql = "DELETE FROM practice_requests WHERE id = ?";
        $delete_stmt = $mysqli->prepare($delete_sql);
        $delete_stmt->bind_param("i", $request_id);
        $delete_stmt->execute();
    }
    header("Location: my_records.php");
    exit();
}

// Handle edit submission for pending practice requests
if (isset($_POST['edit_request'])) {
    $request_id = intval($_POST['request_id']);
    // Ensure the request belongs to the user and is pending
    $check_sql = "SELECT id FROM practice_requests WHERE id = ? AND user_id = ? AND status = 'pending'";
    $check_stmt = $mysqli->prepare($check_sql);
    $check_stmt->bind_param("ii", $request_id, $userId);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $date = $mysqli->real_escape_string($_POST['date']);
        $start_time = $mysqli->real_escape_string($_POST['start_time']);
        $end_time = $mysqli->real_escape_string($_POST['end_time']);
        $transport_to_venue = isset($_POST['transport_to_venue']) ? 1 : 0;
        $pickup_time = $transport_to_venue ? $mysqli->real_escape_string($_POST['pickup_time']) : '';
        $pickup_address = $transport_to_venue ? $mysqli->real_escape_string($_POST['pickup_address']) : '';
        $transport_to_home = isset($_POST['transport_to_home']) ? 1 : 0;
        $dropoff_time = $transport_to_home ? $mysqli->real_escape_string($_POST['dropoff_time']) : '';
        $dropoff_address = $transport_to_home ? $mysqli->real_escape_string($_POST['dropoff_address']) : '';
        $target_goal = $mysqli->real_escape_string($_POST['target_goal']);
        
        $update_sql = "UPDATE practice_requests 
                       SET date = '$date', start_time = '$start_time', end_time = '$end_time', 
                           transport_to_venue = $transport_to_venue, pickup_time = '$pickup_time', pickup_address = '$pickup_address',
                           transport_to_home = $transport_to_home, dropoff_time = '$dropoff_time', dropoff_address = '$dropoff_address',
                           target_goal = '$target_goal'
                       WHERE id = $request_id";
        $mysqli->query($update_sql);
    }
    header("Location: my_records.php");
    exit();
}

// Fetch request details for edit form if requested
$edit_request = null;
if (isset($_GET['edit_request']) && isset($_GET['request_id'])) {
    $request_id = intval($_GET['request_id']);
    $edit_sql = "SELECT * FROM practice_requests WHERE id = ? AND user_id = ? AND status = 'pending'";
    $edit_stmt = $mysqli->prepare($edit_sql);
    $edit_stmt->bind_param("ii", $request_id, $userId);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    if ($edit_result->num_rows > 0) {
        $edit_request = $edit_result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Band Cafe - My Practice Records</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <div class="container mx-auto p-6">
<?php
// Set parameters for header
$showUserInfo = true;
$username = htmlspecialchars($_SESSION['username']);
include 'components/header.php';
?>
<?php
// Prepare content for the welcome card
ob_start();
?>
<h2 class="text-xl font-medium text-gray-700 mb-2">My Practice Records</h2>
<p class="text-gray-500">View your past and upcoming practice sessions below.</p>
<div class="mt-4 p-4 bg-gray-50 rounded-lg">
    <h3 class="text-lg font-medium text-gray-700 mb-1">Summary</h3>
    <p class="text-gray-600">Total Attended Sessions: <span class="font-semibold"><?php echo $total_attended; ?></span></p>
    <p class="text-gray-600">Total Points: <span class="font-semibold"><?php echo $total_points; ?></span></p>
</div>
<?php
$content = ob_get_clean();
include 'components/card.php';
?>
        <?php if ($edit_request): ?>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-6">
            <h2 class="text-xl font-medium text-gray-700 mb-2">Edit Practice Request</h2>
            <form method="POST" action="my_records.php" class="space-y-4">
                <input type="hidden" name="request_id" value="<?php echo $edit_request['id']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" id="date" name="date" value="<?php echo $edit_request['date']; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                        <input type="time" id="start_time" name="start_time" value="<?php echo $edit_request['start_time']; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                        <input type="time" id="end_time" name="end_time" value="<?php echo $edit_request['end_time']; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="target_goal" class="block text-sm font-medium text-gray-700">Target Goal</label>
                        <input type="text" id="target_goal" name="target_goal" value="<?php echo htmlspecialchars($edit_request['target_goal']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" required>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="transport_to_venue" value="1" <?php echo $edit_request['transport_to_venue'] ? 'checked' : ''; ?> class="rounded text-slate-600 focus:ring-slate-500">
                            <span class="ml-2 text-sm text-gray-700">Transport to Venue</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <div>
                                <label for="pickup_time" class="block text-sm font-medium text-gray-700">Pickup Time</label>
                                <input type="time" id="pickup_time" name="pickup_time" value="<?php echo $edit_request['pickup_time']; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="pickup_address" class="block text-sm font-medium text-gray-700">Pickup Address</label>
                                <input type="text" id="pickup_address" name="pickup_address" value="<?php echo htmlspecialchars($edit_request['pickup_address']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="transport_to_home" value="1" <?php echo $edit_request['transport_to_home'] ? 'checked' : ''; ?> class="rounded text-slate-600 focus:ring-slate-500">
                            <span class="ml-2 text-sm text-gray-700">Transport to Home</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <div>
                                <label for="dropoff_time" class="block text-sm font-medium text-gray-700">Dropoff Time</label>
                                <input type="time" id="dropoff_time" name="dropoff_time" value="<?php echo $edit_request['dropoff_time']; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="dropoff_address" class="block text-sm font-medium text-gray-700">Dropoff Address</label>
                                <input type="text" id="dropoff_address" name="dropoff_address" value="<?php echo htmlspecialchars($edit_request['dropoff_address']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-2">
                    <a href="my_records.php" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">Cancel</a>
                    <button type="submit" name="edit_request" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">Save Changes</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 overflow-x-auto">
            <h3 class="text-lg font-medium text-gray-700 mb-2">Upcoming Practice Sessions</h3>
            <table class="min-w-full mb-6">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Date</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Start</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">End</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Transport to Venue</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Transport to Home</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Goal</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Status</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Attended</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Points</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($future_sessions as $row) { 
                        echo "<tr class='hover:bg-gray-50'>";
                        echo "<td class='p-3 text-gray-800'>{$row['date']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['start_time']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['end_time']}</td>";
                        echo "<td class='p-3 text-gray-800'>" . ($row['transport_to_venue'] ? 'Yes' . ($row['pickup_time'] ? " (Pickup at {$row['pickup_time']}" . ($row['pickup_address'] ? " from {$row['pickup_address']}" : '') : '') : 'No') . "</td>";
                        echo "<td class='p-3 text-gray-800'>" . ($row['transport_to_home'] ? 'Yes' . ($row['dropoff_time'] ? " (Dropoff at {$row['dropoff_time']}" . ($row['dropoff_address'] ? " to {$row['dropoff_address']}" : '') : '') : 'No') . "</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['target_goal']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['status']}</td>";
                        echo "<td class='p-3 text-gray-800'>" . (isset($row['attended']) ? ($row['attended'] ? 'Yes' : 'No') : 'Not Set') . "</td>";
                        echo "<td class='p-3 text-gray-800'>" . (isset($row['points']) ? $row['points'] : '0') . "</td>";
                        echo "<td class='p-3'>";
                        if ($row['status'] == 'pending') {
                            echo "<a href='my_records.php?edit_request=true&request_id={$row['id']}' class='text-sm bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 mr-2'>Edit</a>";
                            echo "<a href='my_records.php?delete_request=true&request_id={$row['id']}' class='text-sm bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700' onclick='return confirm(\"Are you sure you want to delete this request?\");'>Delete</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    } ?>
                </tbody>
            </table>
            <?php if (empty($future_sessions)): ?>
                <p class="text-center text-gray-500 mt-4 mb-6">No upcoming practice sessions found.</p>
            <?php endif; ?>
            
            <div class="mb-4">
                <button id="togglePastSessions" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">Show Past Practice Sessions</button>
            </div>
            
            <div id="pastSessions" class="hidden">
                <h3 class="text-lg font-medium text-gray-700 mb-2">Past Practice Sessions</h3>
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Date</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Start</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">End</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Transport to Venue</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Transport to Home</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Goal</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Status</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Attended</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Points</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (isset($past_sessions) && !empty($past_sessions)) { ?>
                            <?php foreach ($past_sessions as $row) { 
                                echo "<tr class='hover:bg-gray-50'>";
                                echo "<td class='p-3 text-gray-800'>{$row['date']}</td>";
                                echo "<td class='p-3 text-gray-800'>{$row['start_time']}</td>";
                                echo "<td class='p-3 text-gray-800'>{$row['end_time']}</td>";
                                echo "<td class='p-3 text-gray-800'>" . ($row['transport_to_venue'] ? 'Yes' . ($row['pickup_time'] ? " (Pickup at {$row['pickup_time']}" . ($row['pickup_address'] ? " from {$row['pickup_address']}" : '') : '') : 'No') . "</td>";
                                echo "<td class='p-3 text-gray-800'>" . ($row['transport_to_home'] ? 'Yes' . ($row['dropoff_time'] ? " (Dropoff at {$row['dropoff_time']}" . ($row['dropoff_address'] ? " to {$row['dropoff_address']}" : '') : '') : 'No') . "</td>";
                                echo "<td class='p-3 text-gray-800'>{$row['target_goal']}</td>";
                                echo "<td class='p-3 text-gray-800'>{$row['status']}</td>";
                                echo "<td class='p-3 text-gray-800'>" . (isset($row['attended']) ? ($row['attended'] ? 'Yes' : 'No') : 'Not Set') . "</td>";
                                echo "<td class='p-3 text-gray-800'>" . (isset($row['points']) ? $row['points'] : '0') . "</td>";
                                echo "<td class='p-3'>";
                                if ($row['status'] == 'pending') {
                                    echo "<a href='my_records.php?edit_request=true&request_id={$row['id']}' class='text-sm bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 mr-2'>Edit</a>";
                                    echo "<a href='my_records.php?delete_request=true&request_id={$row['id']}' class='text-sm bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700' onclick='return confirm(\"Are you sure you want to delete this request?\");'>Delete</a>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            } ?>
                        <?php } ?>
                    </tbody>
                </table>
                <?php if (!isset($past_sessions) || empty($past_sessions)): ?>
                    <p class="text-center text-gray-500 mt-4">No past practice sessions found.</p>
                <?php endif; ?>
            </div>
        </div>
        <nav class="mt-6 flex justify-center">
            <a href="dashboard.php" class="text-slate-600 hover:text-slate-800 font-medium">Back to Dashboard</a>
        </nav>
    </div>
    <script>
        document.getElementById('togglePastSessions').addEventListener('click', function() {
            var pastSessions = document.getElementById('pastSessions');
            if (pastSessions.classList.contains('hidden')) {
                pastSessions.classList.remove('hidden');
                this.textContent = 'Hide Past Practice Sessions';
            } else {
                pastSessions.classList.add('hidden');
                this.textContent = 'Show Past Practice Sessions';
            }
        });
    </script>
</body>
</html>
