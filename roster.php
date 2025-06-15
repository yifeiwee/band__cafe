<?php
include 'components/header.php';  // Include the header component for consistency
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roster - Band Cafe</title>
    <link href="assets/css/style.css" rel="stylesheet">  <!-- Assuming global styles are here -->
</head>
<body>
    <h1>Roster Management</h1>
    <p>Below is the current roster for Band Cafe shifts. If you see an empty page, ensure the server is running and refresh.</p>
    
    <!-- Enhanced hardcoded roster table for demonstration -->
    <table class="table-auto w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-200">
                <th class="border p-2">Staff Name</th>
                <th class="border p-2">Shift Date</th>
                <th class="border p-2">Shift Time</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border p-2">John Doe</td>
                <td class="border p-2">2025-06-20</td>
                <td class="border p-2">09:00 AM - 05:00 PM</td>
            </tr>
            <tr>
                <td class="border p-2">Jane Smith</td>
                <td class="border p-2">2025-06-21</td>
                <td class="border p-2">10:00 AM - 06:00 PM</td>
            </tr>
            <!-- Added more sample rows -->
            <tr>
                <td class="border p-2">Alice Johnson</td>
                <td class="border p-2">2025-06-22</td>
                <td class="border p-2">11:00 AM - 07:00 PM</td>
            </tr>
        </tbody>
    </table>
    
    <div class="mt-4">
        <!-- Use the button component for adding a new entry -->
        <?php
        $text = 'Add New Entry';  // Button text
        $type = 'button';         // Button type
        $color = 'primary';       // Color variant
        $size = 'md';             // Size variant
        include 'components/button.php';  // Include the button component
        ?>
    </div>
</body>
</html>
