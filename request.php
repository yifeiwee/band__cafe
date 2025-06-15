<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require 'config.php';

// Handle quick add from calendar
if (isset($_POST['quick_add'])) {
    $userId = $_SESSION['user_id'];
    $date = $_POST['date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $goal = $mysqli->real_escape_string($_POST['target_goal']);
    
    // Insert quick request with minimal data
    $stmt = $mysqli->prepare("INSERT INTO practice_requests (user_id, date, start_time, end_time, transport_to_venue, transport_to_home, target_goal) VALUES (?, ?, ?, ?, 0, 0, ?)");
    $stmt->bind_param("issss", $userId, $date, $start, $end, $goal);
    
    header('Content-Type: application/json');
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Practice session added successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding session: ' . $mysqli->error]);
    }
    $stmt->close();
    exit();
}

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
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <div class="container mx-auto p-6">
        <?php
        // Include components
        include 'components/header.php';
        // Commented out to prevent undefined variable warnings
        // include 'components/card.php';
        // include 'components/input.php';
        // include 'components/button.php';
        
        // Set parameters for header
        $showUserInfo = true;
        $username = htmlspecialchars($_SESSION['username']);
        include 'components/header.php';
        ?>
        
        <?php
        // Prepare content for the welcome card
        ob_start();
        ?>
        <h2 class="text-xl font-medium text-gray-700 mb-2">New Practice Request</h2>
        <p class="text-gray-500">Fill out the form below to schedule a new practice session.</p>
        <?php
        $content = ob_get_clean();
        include 'components/card.php';
        ?>
        
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
            <form method="post" action="request.php" class="space-y-5">
                <?php
                $id = 'date';
                $label = 'Date';
                $type = 'date';
                include 'components/input.php';
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php
                    $id = 'start_time';
                    $label = 'Start Time';
                    $type = 'time';
                    include 'components/input.php';
                    ?>
                    <?php
                    $id = 'end_time';
                    $label = 'End Time';
                    $type = 'time';
                    include 'components/input.php';
                    ?>
                </div>
                <div class="space-y-4">
                    <div>
                        <label for="transport_to_venue" class="flex items-center cursor-pointer">
                            <input id="transport_to_venue" type="checkbox" name="transport_to_venue" class="mr-2 text-blue-600 focus:ring-blue-500" onclick="toggleTransportFields()"> Transport to Venue Needed
                        </label>
                    </div>
                    <div id="to_venue_fields" class="hidden space-y-3">
                        <?php
                        $id = 'pickup_time';
                        $label = 'Pickup Time';
                        $type = 'time';
                        include 'components/input.php';
                        ?>
                        <?php
                        $id = 'pickup_address';
                        $label = 'Pickup Address';
                        $type = 'text';
                        include 'components/input.php';
                        ?>
                    </div>
                    <div>
                        <label for="transport_to_home" class="flex items-center cursor-pointer">
                            <input id="transport_to_home" type="checkbox" name="transport_to_home" class="mr-2 text-blue-600 focus:ring-blue-500" onclick="toggleTransportFields()"> Transport to Home Needed
                        </label>
                    </div>
                    <div id="to_home_fields" class="hidden space-y-3">
                        <?php
                        $id = 'dropoff_time';
                        $label = 'Dropoff Time';
                        $type = 'time';
                        include 'components/input.php';
                        ?>
                        <?php
                        $id = 'dropoff_address';
                        $label = 'Dropoff Address';
                        $type = 'text';
                        include 'components/input.php';
                        ?>
                    </div>
                </div>
                <?php
                $id = 'target_goal';
                $label = 'Target Goal';
                $type = 'textarea';  // Assuming input.php can handle textarea, otherwise adjust
                include 'components/input.php';
                ?>
                <?php
                $text = 'Submit Request';
                $type = 'submit';
                include 'components/button.php';
                ?>
            </form>
        </div>
        <nav class="mt-6 flex justify-center">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 font-medium">Back to Dashboard</a>
        </nav>
    </div>
</body>
</html>
