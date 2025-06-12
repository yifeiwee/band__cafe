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
    $transportToVenue = isset($_POST['transport_to_venue']) ? 1 : 0;
    $transportToHome = isset($_POST['transport_to_home']) ? 1 : 0;
    $pickupTime = $transportToVenue ? $_POST['pickup_time'] : null;
    $pickupAddress = $transportToVenue ? $mysqli->real_escape_string($_POST['pickup_address']) : null;
    $dropoffTime = $transportToHome ? $_POST['dropoff_time'] : null;
    $dropoffAddress = $transportToHome ? $mysqli->real_escape_string($_POST['dropoff_address']) : null;
    $goal = $mysqli->real_escape_string($_POST['target_goal']);
    // Insert request into database using a prepared statement for security
    $stmt = $mysqli->prepare("INSERT INTO practice_requests (user_id, date, start_time, end_time, transport_to_venue, transport_to_home, pickup_time, pickup_address, dropoff_time, dropoff_address, target_goal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssiisssss", $userId, $date, $start, $end, $transportToVenue, $transportToHome, $pickupTime, $pickupAddress, $dropoffTime, $dropoffAddress, $goal);
    if ($stmt->execute()) {
        // Show confirmation and summary
        echo "<div class='bg-green-50 border border-green-100 p-4 rounded-lg mt-4'>";
        echo "<h3 class='text-green-700 font-medium'>Request Submitted Successfully!</h3>";
        echo "<p class='text-gray-600 mt-2'>Request Summary:</p>";
        echo "<ul class='list-disc list-inside text-gray-600'>";
        echo "<li>Date: $date</li>";
        echo "<li>Time: $start - $end</li>";
        echo "<li>Transport to Venue: " . ($transportToVenue ? "Yes (Pickup at $pickupTime from $pickupAddress)" : "No") . "</li>";
        echo "<li>Transport to Home: " . ($transportToHome ? "Yes (Dropoff at $dropoffTime to $dropoffAddress)" : "No") . "</li>";
        echo "<li>Target Goal: $goal</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<p class='text-red-600 mt-4'>Error submitting request: " . $mysqli->error . "</p>";
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
    <script>
        function toggleTransportFields() {
            const toVenueCheckbox = document.getElementById('transport_to_venue');
            const toHomeCheckbox = document.getElementById('transport_to_home');
            const toVenueFields = document.getElementById('to_venue_fields');
            const toHomeFields = document.getElementById('to_home_fields');

            toVenueFields.classList.toggle('hidden', !toVenueCheckbox.checked);
            toHomeFields.classList.toggle('hidden', !toHomeCheckbox.checked);
        }
    </script>
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
                <div class="space-y-4">
                    <div>
                        <label for="transport_to_venue" class="flex items-center cursor-pointer">
                            <input id="transport_to_venue" type="checkbox" name="transport_to_venue" class="mr-2 text-blue-600 focus:ring-blue-500" onclick="toggleTransportFields()"> Transport to Venue Needed
                        </label>
                    </div>
                    <div id="to_venue_fields" class="hidden space-y-3">
                        <div>
                            <label for="pickup_time" class="block text-sm font-medium text-gray-700 mb-1">Pickup Time</label>
                            <input id="pickup_time" type="time" name="pickup_time" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                        <div>
                            <label for="pickup_address" class="block text-sm font-medium text-gray-700 mb-1">Pickup Address</label>
                            <input id="pickup_address" type="text" name="pickup_address" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                    </div>
                    <div>
                        <label for="transport_to_home" class="flex items-center cursor-pointer">
                            <input id="transport_to_home" type="checkbox" name="transport_to_home" class="mr-2 text-blue-600 focus:ring-blue-500" onclick="toggleTransportFields()"> Transport to Home Needed
                        </label>
                    </div>
                    <div id="to_home_fields" class="hidden space-y-3">
                        <div>
                            <label for="dropoff_time" class="block text-sm font-medium text-gray-700 mb-1">Dropoff Time</label>
                            <input id="dropoff_time" type="time" name="dropoff_time" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                        <div>
                            <label for="dropoff_address" class="block text-sm font-medium text-gray-700 mb-1">Dropoff Address</label>
                            <input id="dropoff_address" type="text" name="dropoff_address" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                    </div>
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
