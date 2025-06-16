<?php
// Input component for Band Cafe app
// Parameters:
// - $id: string, the ID and name attribute for the input
// - $label: string, the label text for the input
// - $type: string, the type of input (default is 'text')
// - $required: boolean, whether the input is required (default is true)
// - $placeholder: string, optional placeholder text
$type = isset($type) ? $type : 'text';
$required = isset($required) ? $required : true;
$placeholder = isset($placeholder) ? $placeholder : '';
?>

<div class="group">
    <label for="<?php echo $id; ?>" class="block text-sm font-semibold text-gray-700 mb-2 group-focus-within:text-slate-600 transition-colors duration-200">
        <?php echo $label; ?>
        <?php if ($required): ?>
            <span class="text-red-500 ml-1">*</span>
        <?php endif; ?>
    </label>
    <?php if ($type === 'textarea'): ?>
        <textarea 
            id="<?php echo $id; ?>" 
            name="<?php echo $id; ?>" 
            <?php echo $required ? 'required' : ''; ?> 
            placeholder="<?php echo $placeholder; ?>"
            rows="4"
            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 transition-all duration-200 resize-none bg-gray-50 focus:bg-white hover:border-gray-300"
        ></textarea>
    <?php else: ?>
        <input 
            id="<?php echo $id; ?>" 
            type="<?php echo $type; ?>" 
            name="<?php echo $id; ?>" 
            <?php echo $required ? 'required' : ''; ?> 
            placeholder="<?php echo $placeholder; ?>"
            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 transition-all duration-200 bg-gray-50 focus:bg-white hover:border-gray-300"
        >
    <?php endif; ?>
</div>
