<?php
// Navigation Card component for Band Cafe app
// Parameters:
// - $href: string, the URL to link to
// - $title: string, the title of the navigation item
// - $description: string, a short description of the navigation item
// - $icon: string, optional SVG icon HTML
$icon = '';
?>

<a href="<?php echo $href; ?>" class="group relative bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200/50 p-8 rounded-2xl hover:from-slate-100 hover:to-slate-200 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl">
    <!-- Background decoration -->
    <div class="absolute top-4 right-4 text-slate-300 group-hover:text-slate-400 transition-colors duration-300">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
        </svg>
    </div>
    
    <div class="flex items-start space-x-4">
        <div class="flex-shrink-0 p-3 bg-slate-600 text-white rounded-xl group-hover:bg-slate-700 transition-colors duration-300">
            <?php echo $icon; ?>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-xl font-bold text-slate-700 group-hover:text-slate-800 transition-colors duration-300 mb-2"><?php echo $title; ?></h3>
            <p class="text-gray-600 leading-relaxed"><?php echo $description; ?></p>
        </div>
    </div>
    
    <!-- Shine effect on hover -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
</a>
