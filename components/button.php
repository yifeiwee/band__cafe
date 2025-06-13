<?php
// Button component for Band Cafe app
// Parameters:
// - $text: string, the text displayed on the button
// - $type: string, the type of button (default is 'submit')
// - $color: string, Tailwind color class (default is 'bg-blue-600')
$type = isset($type) ? $type : 'submit';
$color = isset($color) ? $color : 'bg-blue-600';

$hoverColor = str_replace('bg-', 'hover:bg-', str_replace('-600', '-700', $color));  // Corrected to properly change the shade, e.g., bg-blue-600 to hover:bg-blue-700
?>

<button type="<?php echo $type; ?>" class="w-full <?php echo $color; ?> text-white py-2 rounded-lg <?php echo $hoverColor; ?> focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all"><?php echo $text; ?></button>
