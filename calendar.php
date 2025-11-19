<?php
require 'config.php';
configureSecureSession();
session_start();
setSecurityHeaders();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Band Cafe - Calendar View</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <div class="container mx-auto p-6">
        <?php
        // Include components
        // include 'components/header.php'; // This line will be commented out
        include 'components/card.php';
        
        // Set parameters for header
        $showUserInfo = true;
        $username = htmlspecialchars($_SESSION['username']);
        include 'components/header.php';
        ?>
        
        <?php
        // Prepare content for the welcome card
        ob_start();
        ?>
        <h2 class="text-xl font-medium text-gray-700 mb-2">Practice Schedule Calendar</h2>
        <p class="text-gray-500">View your approved practice sessions in the calendar below.</p>
        <?php
        $content = ob_get_clean();
        include 'components/card.php';
        ?>
        
        <div id="calendar" class="bg-white p-6 rounded-xl shadow-lg border border-gray-100"></div>
        
        <nav class="mt-6 flex justify-center">
            <a href="dashboard.php" class="text-slate-600 hover:text-slate-800 font-medium">Back to Dashboard</a>
        </nav>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: 'fetchEvents.php',  // JSON feed endpoint for events
            eventBackgroundColor: '#2563eb',
            eventBorderColor: '#2563eb',
            eventTextColor: '#ffffff',
            height: 'auto',
            contentHeight: '400px',
            aspectRatio: 1.5
        });
        calendar.render();
    });
    </script>
</body>
</html>
