<?php
// Card component for Band Cafe app
// Parameters:
// - $content: string, the content to be wrapped inside the card
// - $maxWidth: string, optional Tailwind class for max-width (e.g., 'max-w-md')
// - $variant: string, optional variant ('default', 'gradient', 'glass')
$maxWidth = isset($maxWidth) ? $maxWidth : 'max-w-full';
$variant = isset($variant) ? $variant : 'default';

$cardClasses = 'w-full ' . $maxWidth;

switch ($variant) {
    case 'gradient':
        $cardClasses .= ' bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-xl border border-gray-100/50 backdrop-blur-sm';
        break;
    case 'glass':
        $cardClasses .= ' bg-white/70 backdrop-blur-lg p-8 rounded-2xl shadow-xl border border-white/20';
        break;
    default:
        $cardClasses .= ' bg-white p-8 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300';
        break;
}
?>

<div class="<?php echo $cardClasses; ?>">
    <?php echo isset($content) ? $content : ''; ?>
</div>
