<?php
// Header component for Band Cafe app
// Parameters:
// - $showUserInfo: boolean to show/hide user info and logout link
// - $username: string for displaying the logged-in user's name
$showUserInfo = isset($showUserInfo) ? $showUserInfo : false;
$username = isset($username) ? htmlspecialchars($username) : '';
?>

<header class="relative bg-gradient-to-r from-purple-600 via-blue-600 to-indigo-700 p-6 rounded-2xl shadow-lg mb-8 overflow-hidden">
    <!-- Background pattern -->
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <circle cx="25" cy="25" r="3" fill="white"/>
            <circle cx="75" cy="25" r="3" fill="white"/>
            <circle cx="25" cy="75" r="3" fill="white"/>
            <circle cx="75" cy="75" r="3" fill="white"/>
            <circle cx="50" cy="50" r="3" fill="white"/>
        </svg>
    </div>
    
    <div class="relative flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <!-- Music note icon -->
            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Band Cafe</h1>
                <p class="text-purple-100 text-sm font-medium">Music Practice Studio</p>
            </div>
        </div>
        <?php if ($showUserInfo): ?>
            <div class="flex items-center space-x-4">
                <div class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl flex items-center space-x-3">
                    <div class="w-8 h-8 bg-white/30 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <span class="text-white font-medium"><?php echo $username; ?></span>
                </div>
                <a href="logout.php" class="bg-red-500/80 hover:bg-red-500 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2 backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.59L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</header>
