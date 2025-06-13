<?php
// Header component for Band Cafe app
// Parameters:
// - $showUserInfo: boolean to show/hide user info and logout link
// - $username: string for displaying the logged-in user's name
$showUserInfo = isset($showUserInfo) ? $showUserInfo : false;
$username = isset($username) ? htmlspecialchars($username) : '';
?>

<header class="flex justify-between items-center mb-8">
    <h1 class="text-2xl font-semibold text-gray-800">Band Cafe</h1>
    <?php if ($showUserInfo): ?>
        <div class="flex items-center space-x-4">
            <span class="text-gray-600"><?php echo $username; ?></span>
            <a href="logout.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Logout</a>
        </div>
    <?php endif; ?>
</header>
