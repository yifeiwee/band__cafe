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
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-semibold text-gray-800">Band Cafe</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Logout</a>
            </div>
        </header>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-6">
            <h2 class="text-xl font-medium text-gray-700 mb-2">Welcome to Band Cafe</h2>
            <p class="text-gray-500">Manage your band practice sessions with ease. Use the options below to get started.</p>
        </div>
        <nav class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="request.php" class="bg-blue-50 border border-blue-100 p-6 rounded-xl hover:bg-blue-100 transition-all text-center">
                <h3 class="text-lg font-medium text-blue-700">New Practice Request</h3>
                <p class="text-gray-500 mt-1">Schedule a new practice session.</p>
            </a>
            <a href="my_records.php" class="bg-blue-50 border border-blue-100 p-6 rounded-xl hover:bg-blue-100 transition-all text-center">
                <h3 class="text-lg font-medium text-blue-700">My Practice Records</h3>
                <p class="text-gray-500 mt-1">View your past and upcoming sessions.</p>
            </a>
            <a href="calendar.php" class="bg-blue-50 border border-blue-100 p-6 rounded-xl hover:bg-blue-100 transition-all text-center">
                <h3 class="text-lg font-medium text-blue-700">Calendar View</h3>
                <p class="text-gray-500 mt-1">See your schedule in a calendar format.</p>
            </a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="bg-blue-50 border border-blue-100 p-6 rounded-xl hover:bg-blue-100 transition-all text-center">
                    <h3 class="text-lg font-medium text-blue-700">Admin Dashboard</h3>
                    <p class="text-gray-500 mt-1">Manage all practice requests.</p>
                </a>
            <?php endif; ?>
        </nav>
    </div>
</body>
</html>
