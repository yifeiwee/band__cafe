<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require 'config.php';

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');

// Query past sessions (by date) and upcoming approved sessions
$sql = "SELECT date, start_time, end_time, transport_needed, target_goal, status
        FROM practice_requests
        WHERE user_id = ?
        ORDER BY date DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
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
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-semibold text-gray-800">Band Cafe</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Logout</a>
            </div>
        </header>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-6">
            <h2 class="text-xl font-medium text-gray-700 mb-2">My Practice Records</h2>
            <p class="text-gray-500">View your past and upcoming practice sessions below.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Date</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Start</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">End</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Transport</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Goal</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while ($row = $result->fetch_assoc()) { 
                        $rowDate = $row['date'];
                        // You could split into past/upcoming based on date. For simplicity, we list all.
                        echo "<tr class='hover:bg-gray-50'>";
                        echo "<td class='p-3 text-gray-800'>{$row['date']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['start_time']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['end_time']}</td>";
                        echo "<td class='p-3 text-gray-800'>" . ($row['transport_needed'] ? 'Yes' : 'No') . "</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['target_goal']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['status']}</td>";
                        echo "</tr>";
                    } ?>
                </tbody>
            </table>
            <?php if ($result->num_rows === 0): ?>
                <p class="text-center text-gray-500 mt-4">No practice records found.</p>
            <?php endif; ?>
        </div>
        <nav class="mt-6 flex justify-center">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 font-medium">Back to Dashboard</a>
        </nav>
    </div>
</body>
</html>
