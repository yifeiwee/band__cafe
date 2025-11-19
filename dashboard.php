<?php
require 'config.php';
require_once 'includes/security.php';
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
    <title>Band Cafe - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="font-sans text-gray-800">
    <div class="container mx-auto p-6 max-w-7xl">
        <?php
        // Set parameters for header
        $showUserInfo = true;
        $username = htmlspecialchars($_SESSION['username']);
        include 'components/header.php';
        ?>
        
        <!-- Welcome section -->
        <div class="mb-8">
            <?php
            ob_start();
            ?>
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Welcome Back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">Ready to make some music? Manage your band practice sessions with ease using our studio management tools.</p>
                
                <!-- Quick stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <div class="bg-gradient-to-br from-slate-500 to-slate-600 text-white p-6 rounded-2xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-100 text-sm font-medium">This Month</p>
                                <p class="text-3xl font-bold">12</p>
                                <p class="text-slate-100 text-sm">Practice Sessions</p>
                            </div>
                            <svg class="w-12 h-12 text-slate-200" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-slate-500 to-slate-600 text-white p-6 rounded-2xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-100 text-sm font-medium">Total Hours</p>
                                <p class="text-3xl font-bold">48</p>
                                <p class="text-slate-100 text-sm">Practice Time</p>
                            </div>
                            <svg class="w-12 h-12 text-slate-200" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/>
                                <path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-slate-500 to-slate-600 text-white p-6 rounded-2xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-slate-100 text-sm font-medium">Next Session</p>
                                <p class="text-3xl font-bold">Today</p>
                                <p class="text-slate-100 text-sm">6:00 PM</p>
                            </div>
                            <!-- Music note icon removed -->
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            $variant = 'gradient';
            include 'components/card.php';
            ?>
        </div>
        
        <!-- Navigation grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            $href = 'request.php';
            $title = 'New Practice Request';
            $description = 'Schedule a new practice session with transport options and goal setting.';
            $icon = '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>';
            include 'components/nav_card.php';
            ?>
            
            <?php
            $href = 'my_records.php';
            $title = 'My Practice Records';
            $description = 'View your past sessions, upcoming bookings, and practice history.';
            $icon = '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>';
            include 'components/nav_card.php';
            ?>
            
            <?php
            $href = 'calendar.php';
            $title = 'Calendar View';
            $description = 'See your schedule in an interactive calendar format with booking details.';
            $icon = '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/></svg>';
            include 'components/nav_card.php';
            ?>
            
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <?php
                $href = 'admin.php';
                $title = 'Admin Dashboard';
                $description = 'Manage all practice requests, user accounts, and studio operations.';
                $icon = '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>';
                include 'components/nav_card.php';
                ?>
                
                <?php
                $href = 'roster.php';
                $title = 'Roster Management';
                $description = 'Manage band member schedules, assignments, and track member availability.';
                $icon = '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>';
                include 'components/nav_card.php';
                ?>
            <?php endif; ?>        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>
