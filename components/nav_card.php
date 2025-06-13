<?php
// Navigation Card component for Band Cafe app
// Parameters:
// - $href: string, the URL to link to
// - $title: string, the title of the navigation item
// - $description: string, a short description of the navigation item
?>

<a href="<?php echo $href; ?>" class="bg-blue-50 border border-blue-100 p-6 rounded-xl hover:bg-blue-100 transition-all text-center">
    <h3 class="text-lg font-medium text-blue-700"><?php echo $title; ?></h3>
    <p class="text-gray-500 mt-1"><?php echo $description; ?></p>
</a>
