<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}
require 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_roster_entry'])) {
        $user_id = intval($_POST['user_id']);
        $date = $_POST['date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $role = $mysqli->real_escape_string($_POST['roster_role']);
        $notes = $mysqli->real_escape_string($_POST['notes']);
        
        // Insert new roster entry as a practice request
        $stmt = $mysqli->prepare("INSERT INTO practice_requests (user_id, date, start_time, end_time, target_goal, status) VALUES (?, ?, ?, ?, ?, 'approved')");
        $goal = "Roster Assignment: $role" . ($notes ? " - $notes" : "");
        $stmt->bind_param("issss", $user_id, $date, $start_time, $end_time, $goal);
        
        if ($stmt->execute()) {
            $success_message = "Roster entry added successfully!";
        } else {
            $error_message = "Error adding roster entry: " . $mysqli->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['update_roster_entry'])) {
        $request_id = intval($_POST['request_id']);
        $user_id = intval($_POST['user_id']);
        $date = $_POST['date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $role = $mysqli->real_escape_string($_POST['roster_role']);
        $notes = $mysqli->real_escape_string($_POST['notes']);
        
        $goal = "Roster Assignment: $role" . ($notes ? " - $notes" : "");
        $stmt = $mysqli->prepare("UPDATE practice_requests SET user_id=?, date=?, start_time=?, end_time=?, target_goal=? WHERE id=?");
        $stmt->bind_param("issssi", $user_id, $date, $start_time, $end_time, $goal, $request_id);
        
        if ($stmt->execute()) {
            $success_message = "Roster entry updated successfully!";
        } else {
            $error_message = "Error updating roster entry: " . $mysqli->error;
        }
        $stmt->close();
    }
}

// Handle deletions
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("DELETE FROM practice_requests WHERE id=? AND status='approved'");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success_message = "Roster entry deleted successfully!";
    } else {
        $error_message = "Error deleting roster entry: " . $mysqli->error;
    }
    $stmt->close();
}

// Get edit entry data
$edit_entry = null;
if (isset($_GET['edit']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT pr.*, u.username FROM practice_requests pr JOIN users u ON pr.user_id = u.id WHERE pr.id=? AND pr.status='approved'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_entry = $result->fetch_assoc();
    $stmt->close();
}

// Get filter parameters
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$filter_user = isset($_GET['filter_user']) ? intval($_GET['filter_user']) : '';

// Build query for roster entries
$query = "SELECT pr.*, u.username, u.instrument, u.section 
          FROM practice_requests pr 
          JOIN users u ON pr.user_id = u.id 
          WHERE pr.status = 'approved'";
$params = [];
$types = '';

if ($filter_date) {
    $query .= " AND pr.date = ?";
    $params[] = $filter_date;
    $types .= 's';
}

if ($filter_user) {
    $query .= " AND pr.user_id = ?";
    $params[] = $filter_user;
    $types .= 'i';
}

$query .= " ORDER BY pr.date ASC, pr.start_time ASC";

$stmt = $mysqli->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$roster_result = $stmt->get_result();

// Get all users for dropdowns
$users_query = "SELECT id, username, instrument, section FROM users ORDER BY username";
$users_result = $mysqli->query($users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Band Cafe - Roster Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <div class="container mx-auto p-6">
        <?php
        // Set parameters for header
        $showUserInfo = true;
        $username = htmlspecialchars($_SESSION['username']);
        include 'components/header.php';
        ?>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Page Title -->
        <div class="mb-8">
            <?php
            ob_start();
            ?>
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Roster Management 📅</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Manage band member schedules, assignments, and roster entries. Add new shifts, update existing ones, and track member availability.
                </p>
            </div>
            <?php
            $content = ob_get_clean();
            include 'components/card.php';
            ?>
        </div>

        <!-- Filters Section -->
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Filter Roster</h3>
            <form method="GET" action="roster.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">                <div>
                    <label for="filter_date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="filter_date" name="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm">
                </div>
                <div>
                    <label for="filter_user" class="block text-sm font-medium text-gray-700">Band Member</label>
                    <select id="filter_user" name="filter_user" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm">
                        <option value="">All Members</option>
                        <?php 
                        $users_result->data_seek(0);
                        while ($user = $users_result->fetch_assoc()): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo $filter_user == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?> 
                                <?php echo $user['instrument'] ? '(' . htmlspecialchars($user['instrument']) . ')' : ''; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <?php
                    $text = 'Apply Filters';
                    $type = 'submit';
                    $color = 'secondary';
                    $size = 'md';
                    include 'components/button.php';
                    ?>
                    <a href="roster.php" class="ml-2 px-4 py-2 text-gray-600 hover:text-gray-800">Clear</a>
                </div>
            </form>
        </div>

        <!-- Add/Edit Roster Entry Form -->
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">
                <?php echo $edit_entry ? 'Edit Roster Entry' : 'Add New Roster Entry'; ?>
            </h3>
            <form method="POST" action="roster.php" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if ($edit_entry): ?>
                    <input type="hidden" name="request_id" value="<?php echo $edit_entry['id']; ?>">
                <?php endif; ?>
                
                <div>                    <label for="user_id" class="block text-sm font-medium text-gray-700">Band Member *</label>
                    <select id="user_id" name="user_id" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm">
                        <option value="">Select Member</option>
                        <?php 
                        $users_result->data_seek(0);
                        while ($user = $users_result->fetch_assoc()): ?>
                            <option value="<?php echo $user['id']; ?>" 
                                    <?php echo ($edit_entry && $edit_entry['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?> 
                                <?php echo $user['instrument'] ? '(' . htmlspecialchars($user['instrument']) . ')' : ''; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                  <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">Date *</label>
                    <input type="date" id="date" name="date" required 
                           value="<?php echo $edit_entry ? $edit_entry['date'] : ''; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time *</label>
                    <input type="time" id="start_time" name="start_time" required 
                           value="<?php echo $edit_entry ? $edit_entry['start_time'] : ''; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700">End Time *</label>                    <input type="time" id="end_time" name="end_time" required 
                           value="<?php echo $edit_entry ? $edit_entry['end_time'] : ''; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="roster_role" class="block text-sm font-medium text-gray-700">Role/Assignment</label>
                    <select id="roster_role" name="roster_role" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm">
                        <option value="Practice Session" <?php echo ($edit_entry && strpos($edit_entry['target_goal'], 'Practice Session') !== false) ? 'selected' : ''; ?>>Practice Session</option>
                        <option value="Performance" <?php echo ($edit_entry && strpos($edit_entry['target_goal'], 'Performance') !== false) ? 'selected' : ''; ?>>Performance</option>
                        <option value="Rehearsal" <?php echo ($edit_entry && strpos($edit_entry['target_goal'], 'Rehearsal') !== false) ? 'selected' : ''; ?>>Rehearsal</option>
                        <option value="Recording Session" <?php echo ($edit_entry && strpos($edit_entry['target_goal'], 'Recording Session') !== false) ? 'selected' : ''; ?>>Recording Session</option>
                        <option value="Teaching" <?php echo ($edit_entry && strpos($edit_entry['target_goal'], 'Teaching') !== false) ? 'selected' : ''; ?>>Teaching</option>
                        <option value="Setup/Breakdown" <?php echo ($edit_entry && strpos($edit_entry['target_goal'], 'Setup/Breakdown') !== false) ? 'selected' : ''; ?>>Setup/Breakdown</option>
                        <option value="Other" <?php echo ($edit_entry && strpos($edit_entry['target_goal'], 'Other') !== false) ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="md:col-span-2 lg:col-span-1">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>                    <input type="text" id="notes" name="notes" placeholder="Additional notes or details"
                           value="<?php echo $edit_entry ? htmlspecialchars(str_replace('Roster Assignment: ' . (strpos($edit_entry['target_goal'], ' - ') !== false ? substr($edit_entry['target_goal'], 0, strpos($edit_entry['target_goal'], ' - ')) : $edit_entry['target_goal']) . ' - ', '', $edit_entry['target_goal'])) : ''; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm">
                </div>
                
                <div class="md:col-span-2 lg:col-span-3 flex justify-end space-x-2">
                    <?php if ($edit_entry): ?>
                        <a href="roster.php" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                        <?php
                        $text = 'Update Entry';
                        $type = 'submit';
                        $name = 'update_roster_entry';
                        $color = 'primary';
                        $size = 'md';
                        include 'components/button.php';
                        ?>
                    <?php else: ?>
                        <?php
                        $text = 'Add New Entry';
                        $type = 'submit';
                        $name = 'add_roster_entry';
                        $color = 'primary';
                        $size = 'md';
                        $icon = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>';
                        include 'components/button.php';
                        ?>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Roster Table -->
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-700">Current Roster Schedule</h3>
                <div class="text-sm text-gray-500">
                    Total entries: <?php echo $roster_result->num_rows; ?>
                </div>
            </div>
            
            <?php if ($roster_result->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instrument</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($entry = $roster_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('M j, Y', strtotime($entry['date'])); ?>
                                        <div class="text-xs text-gray-500"><?php echo date('l', strtotime($entry['date'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('g:i A', strtotime($entry['start_time'])); ?> - 
                                        <?php echo date('g:i A', strtotime($entry['end_time'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($entry['username']); ?></div>
                                        <?php if ($entry['section']): ?>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($entry['section']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $entry['instrument'] ? htmlspecialchars($entry['instrument']) : 'N/A'; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="max-w-xs truncate" title="<?php echo htmlspecialchars($entry['target_goal']); ?>">
                                            <?php echo htmlspecialchars($entry['target_goal']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="roster.php?edit=1&id=<?php echo $entry['id']; ?>" 
                                           class="text-slate-600 hover:text-slate-900 mr-3">Edit</a>
                                        <a href="roster.php?delete=1&id=<?php echo $entry['id']; ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('Are you sure you want to delete this roster entry?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="text-gray-500 text-lg mb-2">No roster entries found</div>
                    <div class="text-gray-400">Add your first roster entry using the form above.</div>
                </div>
            <?php endif; ?>
        </div>        <!-- Navigation -->
        <div class="mt-8 text-center">            <div class="flex justify-center space-x-6">                <a href="dashboard.php" class="inline-flex items-center text-slate-600 hover:text-slate-800 font-medium text-lg">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                    Back to Dashboard
                </a>
                <span class="text-gray-300">|</span>
                <a href="admin.php" class="inline-flex items-center text-slate-600 hover:text-slate-800 font-medium text-lg">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    Admin Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        // Auto-fill end time when start time changes (default 2-hour session)
        document.getElementById('start_time').addEventListener('change', function() {
            const startTime = this.value;
            const endTimeInput = document.getElementById('end_time');
            
            if (startTime && !endTimeInput.value) {
                const [hours, minutes] = startTime.split(':');
                const startDate = new Date();
                startDate.setHours(parseInt(hours), parseInt(minutes));
                startDate.setHours(startDate.getHours() + 2); // Add 2 hours
                
                const endHours = startDate.getHours().toString().padStart(2, '0');
                const endMinutes = startDate.getMinutes().toString().padStart(2, '0');
                endTimeInput.value = `${endHours}:${endMinutes}`;
            }
        });

        // Set default date to today if adding new entry
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('date');
            if (!dateInput.value && !<?php echo $edit_entry ? 'true' : 'false'; ?>) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.value = today;
            }
        });
    </script>
</body>
</html>
