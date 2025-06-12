<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require 'config.php';

if (isset($_POST['submit_request'])) {
    $userId = $_SESSION['user_id'];
    $date = $_POST['date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $transport = isset($_POST['transport']) ? 1 : 0;
    $goal = $mysqli->real_escape_string($_POST['target_goal']);
    // Insert request into database using a prepared statement for security
    $stmt = $mysqli->prepare("INSERT INTO practice_requests (user_id,date,start_time,end_time,transport_needed,target_goal) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssis", $userId, $date, $start, $end, $transport, $goal);
    if ($stmt->execute()) {
        // Show confirmation and summary
        echo "<script>alert('Practice request submitted successfully!');</script>";
        echo "<h3>Request Summary:</h3>";
        echo "<ul>";
        echo "<li>Date: $date</li>";
        echo "<li>Time: $start - $end</li>";
        echo "<li>Transport Needed: " . ($transport ? "Yes" : "No") . "</li>";
        echo "<li>Target Goal: $goal</li>";
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>Error submitting request: " . $mysqli->error . "</p>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Band Cafe - New Practice Request</title>
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
            <h2 class="text-xl font-medium text-gray-700 mb-2">New Practice Request</h2>
            <p class="text-gray-500">Fill out the form below to schedule a new practice session.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
            <form method="post" action="request.php" class="space-y-5">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input id="date" type="date" name="date" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                        <input id="start_time" type="time" name="start_time" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                        <input id="end_time" type="time" name="end_time" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                </div>
                <div>
                    <label for="transport" class="flex items-center cursor-pointer">
                        <input id="transport" type="checkbox" name="transport" class="mr-2 text-blue-600 focus:ring-blue-500"> Transport Needed
                    </label>
                </div>
                <div>
                    <label for="target_goal" class="block text-sm font-medium text-gray-700 mb-1">Target Goal</label>
                    <textarea id="target_goal" name="target_goal" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></textarea>
                </div>
                <button type="submit" name="submit_request" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">Submit Request</button>
            </form>
        </div>
        <nav class="mt-6 flex justify-center">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 font-medium">Back to Dashboard</a>
        </nav>
    </div>
</body>
</html>
