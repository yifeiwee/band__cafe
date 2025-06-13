<?php
// Button component for Band Cafe app
// Parameters:
// - $text: string, the text displayed on the button
// - $type: string, the type of button (default is 'submit')
// - $name: string, the name attribute for the button (optional)
// - $color: string, color variant ('primary', 'secondary', 'success', 'danger')
// - $size: string, size variant ('sm', 'md', 'lg')
// - $icon: string, optional SVG icon HTML
$type = isset($type) ? $type : 'submit';
$color = isset($color) ? $color : 'primary';
$size = isset($size) ? $size : 'md';
$nameAttr = isset($name) ? 'name="' . $name . '"' : '';
$icon = isset($icon) ? $icon : '';

// Color variants
$colorClasses = [
    'primary' => 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-lg hover:shadow-xl',
    'secondary' => 'bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white shadow-lg hover:shadow-xl',
    'success' => 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white shadow-lg hover:shadow-xl',
    'danger' => 'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white shadow-lg hover:shadow-xl'
];

// Size variants
$sizeClasses = [
    'sm' => 'px-4 py-2 text-sm',
    'md' => 'px-6 py-3 text-base',
    'lg' => 'px-8 py-4 text-lg'
];

$buttonClasses = 'w-full font-semibold rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center space-x-2 ' . $colorClasses[$color] . ' ' . $sizeClasses[$size];
?>

<button type="<?php echo $type; ?>" <?php echo $nameAttr; ?> class="<?php echo $buttonClasses; ?>">
    <?php if ($icon): ?>
        <span class="flex-shrink-0"><?php echo $icon; ?></span>
    <?php endif; ?>
    <span><?php echo $text; ?></span>
</button>
