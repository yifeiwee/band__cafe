<?php
// Card component for Band Cafe app
// Parameters:
// - $content: string, the content to be wrapped inside the card
// - $maxWidth: string, optional Tailwind class for max-width (e.g., 'max-w-md')
$maxWidth = isset($maxWidth) ? $maxWidth : 'max-w-full';
?>

<div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 w-full <?php echo $maxWidth; ?>">
    <?php echo isset($content) ? $content : ''; ?>
</div>
