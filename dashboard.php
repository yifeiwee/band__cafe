<?php
session_start();
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
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <div class="container mx-auto p-6">
        <?php
        // Include components
        include 'components/header.php';
        // Commented out to prevent undefined variable warnings
        // include 'components/card.php';
        // include 'components/nav_card.php';
        
        // Set parameters for header
        $showUserInfo = true;
        $username = htmlspecialchars($_SESSION['username']);
        include 'components/header.php';
        ?>
        
        <?php
        // Prepare content for the welcome card
        ob_start();
        ?>
        <h2 class="text-xl font-medium text-gray-700 mb-2">Welcome to Band Cafe</h2>
        <p class="text-gray-500">Manage your band practice sessions with ease. Use the options below to get started.</p>
        <?php
        $content = ob_get_clean();
        include 'components/card.php';
        ?>
        
        <nav class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <?php
            $href = 'request.php';
            $title = 'New Practice Request';
            $description = 'Schedule a new practice session.';
            include 'components/nav_card.php';
            ?>
            <?php
            $href = 'my_records.php';
            $title = 'My Practice Records';
            $description = 'View your past and upcoming sessions.';
            include 'components/nav_card.php';
            ?>
            <?php
            $href = 'calendar.php';
            $title = 'Calendar View';
            $description = 'See your schedule in a calendar format.';
            include 'components/nav_card.php';
            ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <?php
                $href = 'admin.php';
                $title = 'Admin Dashboard';
                $description = 'Manage all practice requests.';
                include 'components/nav_card.php';
                ?>
            <?php endif; ?>
        </nav>
    </div>
</body>
</html>
