<?php
// Input component for Band Cafe app
// Parameters:
// - $id: string, the ID and name attribute for the input
// - $label: string, the label text for the input
// - $type: string, the type of input (default is 'text')
// - $required: boolean, whether the input is required (default is true)
$type = isset($type) ? $type : 'text';
$required = isset($required) ? $required : true;
?>

<div>
    <label for="<?php echo $id; ?>" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $label; ?></label>
    <input id="<?php echo $id; ?>" type="<?php echo $type; ?>" name="<?php echo $id; ?>" <?php echo $required ? 'required' : ''; ?> class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
</div>
