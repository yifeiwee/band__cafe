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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .checkbox-custom {
            appearance: none;
            background-color: #fff;
            border: 2px solid #d1d5db;
            border-radius: 0.5rem;
            width: 1.25rem;
            height: 1.25rem;
            position: relative;
            cursor: pointer;
            transition: all 0.2s;
        }
        .checkbox-custom:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        .checkbox-custom:checked::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            background-color: white;
            border-radius: 2px;
        }
    </style>
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
<body class="font-sans text-gray-800">
    <div class="container mx-auto p-6 max-w-4xl">
        <?php
        // Set parameters for header
        $showUserInfo = true;
        $username = htmlspecialchars($_SESSION['username']);
        include 'components/header.php';
        ?>
        
        <!-- Page title section -->
        <div class="mb-8">
            <?php
            ob_start();
            ?>
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">New Practice Request 🎸</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">Fill out the form below to schedule your next practice session. Include transport details and set your practice goals.</p>
            </div>
            <?php
            $content = ob_get_clean();
            $variant = 'gradient';
            include 'components/card.php';
            ?>
        </div>
        
        <!-- Main form -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Form header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
                <h3 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-8 h-8 mr-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                    Practice Session Details
                </h3>
                <p class="text-blue-100 mt-2">Please provide all the necessary information for your session</p>
            </div>
            
            <div class="p-8">
                <form method="post" action="request.php" class="space-y-8">
                    <!-- Date and Time Section -->
                    <div class="bg-gray-50 p-6 rounded-xl">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
                            </svg>
                            Date & Time
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <?php
                            $id = 'date';
                            $label = 'Practice Date';
                            $type = 'date';
                            $placeholder = '';
                            include 'components/input.php';
                            ?>
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
                    </div>

                    <!-- Transport Section -->
                    <div class="bg-amber-50 p-6 rounded-xl border border-amber-100">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-amber-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.22.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                            </svg>
                            Transport Options
                        </h4>
                        
                        <div class="space-y-6">
                            <!-- Transport to venue -->
                            <div class="bg-white p-4 rounded-lg border border-amber-200">
                                <label for="transport_to_venue" class="flex items-center cursor-pointer group">
                                    <input id="transport_to_venue" type="checkbox" name="transport_to_venue" class="checkbox-custom mr-4" onclick="toggleTransportFields()">
                                    <div class="flex-1">
                                        <span class="text-lg font-medium text-gray-900 group-hover:text-blue-600 transition-colors">Transport to Venue Needed</span>
                                        <p class="text-gray-600 text-sm mt-1">We'll arrange pickup transportation to the practice venue</p>
                                    </div>
                                </label>
                            </div>
                            
                            <div id="to_venue_fields" class="hidden bg-blue-50 p-4 rounded-lg border border-blue-200 space-y-4">
                                <h5 class="font-medium text-blue-900">Pickup Details</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php
                                    $id = 'pickup_time';
                                    $label = 'Pickup Time';
                                    $type = 'time';
                                    $placeholder = '';
                                    $required = false;
                                    include 'components/input.php';
                                    ?>
                                    <?php
                                    $id = 'pickup_address';
                                    $label = 'Pickup Address';
                                    $type = 'text';
                                    $placeholder = 'Enter your pickup location';
                                    $required = false;
                                    include 'components/input.php';
                                    ?>
                                </div>
                            </div>

                            <!-- Transport to home -->
                            <div class="bg-white p-4 rounded-lg border border-amber-200">
                                <label for="transport_to_home" class="flex items-center cursor-pointer group">
                                    <input id="transport_to_home" type="checkbox" name="transport_to_home" class="checkbox-custom mr-4" onclick="toggleTransportFields()">
                                    <div class="flex-1">
                                        <span class="text-lg font-medium text-gray-900 group-hover:text-blue-600 transition-colors">Transport to Home Needed</span>
                                        <p class="text-gray-600 text-sm mt-1">We'll arrange drop-off transportation after practice</p>
                                    </div>
                                </label>
                            </div>
                            
                            <div id="to_home_fields" class="hidden bg-green-50 p-4 rounded-lg border border-green-200 space-y-4">
                                <h5 class="font-medium text-green-900">Drop-off Details</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php
                                    $id = 'dropoff_time';
                                    $label = 'Drop-off Time';
                                    $type = 'time';
                                    $placeholder = '';
                                    $required = false;
                                    include 'components/input.php';
                                    ?>
                                    <?php
                                    $id = 'dropoff_address';
                                    $label = 'Drop-off Address';
                                    $type = 'text';
                                    $placeholder = 'Enter your drop-off location';
                                    $required = false;
                                    include 'components/input.php';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Goals Section -->
                    <div class="bg-purple-50 p-6 rounded-xl border border-purple-100">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 11l3-3 3 3-3 3-3-3zm3-8l-3 3h6l-3-3zm0 16l3-3H6l3 3z"/>
                            </svg>
                            Practice Goals
                        </h4>
                        <?php
                        $id = 'target_goal';
                        $label = 'What do you want to achieve in this session?';
                        $type = 'textarea';
                        $placeholder = 'e.g., Work on new song arrangements, practice harmonies, recording session, equipment setup...';
                        include 'components/input.php';
                        ?>
                    </div>

                    <!-- Submit Section -->
                    <div class="pt-6 border-t border-gray-200">
                        <?php
                        $text = 'Submit Practice Request';
                        $type = 'submit';
                        $name = 'submit_request';
                        $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>';
                        include 'components/button.php';
                        ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="mt-8 text-center">
            <a href="dashboard.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium text-lg transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                </svg>
                Back to Dashboard
            </a>        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>
