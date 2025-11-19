    header('Location: login.php');
    exit();
}
require 'config.php';

// Handle approve/reject actions via GET parameters
if (isset($_GET['action'], $_GET['id'], $_GET['csrf_token'])) {
    // CSRF token validation
    if (!validateCsrfToken($_GET['csrf_token'])) {
        die('Invalid security token.');
    }
    
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $stmt = $mysqli->prepare("UPDATE practice_requests SET status='approved' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        logSecurityEvent("Admin approved practice request ID: $id", "INFO");
    } elseif ($action === 'reject') {
        $stmt = $mysqli->prepare("UPDATE practice_requests SET status='rejected' WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        logSecurityEvent("Admin rejected practice request ID: $id", "INFO");
    }
    header("Location: admin.php");
    exit();
}

// Handle attendance confirmation and points assignment
if (isset($_POST['confirm_attendance'])) {
    // CSRF token validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }
    
    $request_id = intval($_POST['request_id']);
    $user_id = intval($_POST['user_id']);
    $date = $_POST['date'];
    $attended = isset($_POST['attended']) ? 1 : 0;
    $points = intval($_POST['points']);
    
    // Validate date
    $dateValidation = validateInput($date, 'date');
    if (!$dateValidation['valid']) {
        die('Invalid date format.');
    }
    
    // Check if record already exists using prepared statement
    $check_stmt = $mysqli->prepare("SELECT id FROM practice_records WHERE request_id = ?");
    $check_stmt->bind_param("i", $request_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        // Update existing record
        $update_stmt = $mysqli->prepare("UPDATE practice_records SET attended = ?, points = ? WHERE request_id = ?");
        $update_stmt->bind_param("iii", $attended, $points, $request_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Insert new record
        $insert_stmt = $mysqli->prepare("INSERT INTO practice_records (request_id, user_id, date, attended, points) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("iisii", $request_id, $user_id, $date, $attended, $points);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $check_stmt->close();
    logSecurityEvent("Admin updated attendance for request ID: $request_id", "INFO");
    header("Location: admin.php");
    exit();
}

// Handle user update
if (isset($_POST['update_user'])) {
    // CSRF token validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }
    
    $user_id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    
    // Validate inputs
    if (strlen($username) < 3 || strlen($username) > 50) {
        die('Invalid username length.');
    }
    if (!in_array($role, ['user', 'admin'])) {
        die('Invalid role.');
    }
    
    $update_stmt = $mysqli->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $username, $role, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
    logSecurityEvent("Admin updated user ID: $user_id to username: $username, role: $role", "INFO");
    header("Location: admin.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete_user'], $_GET['user_id'], $_GET['csrf_token'])) {
    // CSRF token validation
    if (!validateCsrfToken($_GET['csrf_token'])) {
        die('Invalid security token.');
    }
    
    $user_id = intval($_GET['user_id']);
    // Prevent deleting the current admin
    if ($user_id != $_SESSION['user_id']) {
        $mysqli->begin_transaction();
        try {
            // Delete related data first (foreign key constraints)
            $stmt1 = $mysqli->prepare("DELETE FROM practice_records WHERE user_id = ?");
            $stmt1->bind_param("i", $user_id);
            $stmt1->execute();
            $stmt1->close();
            
            $stmt2 = $mysqli->prepare("DELETE FROM practice_requests WHERE user_id = ?");
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $stmt2->close();
            
            $stmt3 = $mysqli->prepare("DELETE FROM users WHERE id = ?");
            $stmt3->bind_param("i", $user_id);
            $stmt3->execute();
            $stmt3->close();
            
            $mysqli->commit();
            logSecurityEvent("Admin deleted user ID: $user_id", "WARNING");
        } catch (Exception $e) {
            $mysqli->rollback();
            error_log("User deletion failed: " . $e->getMessage());
        }
    }
    header("Location: admin.php");
    exit();
}

// Handle practice request deletion
if (isset($_GET['delete_request'], $_GET['request_id'], $_GET['csrf_token'])) {
    // CSRF token validation
    if (!validateCsrfToken($_GET['csrf_token'])) {
        die('Invalid security token.');
    }
    
    $request_id = intval($_GET['request_id']);
    
    $mysqli->begin_transaction();
    try {
        // Delete related practice record if exists
        $stmt1 = $mysqli->prepare("DELETE FROM practice_records WHERE request_id = ?");
        $stmt1->bind_param("i", $request_id);
        $stmt1->execute();
        $stmt1->close();
        
        $stmt2 = $mysqli->prepare("DELETE FROM practice_requests WHERE id = ?");
        $stmt2->bind_param("i", $request_id);
        $stmt2->execute();
        $stmt2->close();
        
        $mysqli->commit();
        logSecurityEvent("Admin deleted practice request ID: $request_id", "WARNING");
    } catch (Exception $e) {
        $mysqli->rollback();
        error_log("Request deletion failed: " . $e->getMessage());
    }
    header("Location: admin.php");
    exit();
}

// Handle practice record deletion
if (isset($_GET['delete_record'], $_GET['record_id'], $_GET['csrf_token'])) {
    // CSRF token validation
    if (!validateCsrfToken($_GET['csrf_token'])) {
        die('Invalid security token.');
    }
    
    $record_id = intval($_GET['record_id']);
    $stmt = $mysqli->prepare("DELETE FROM practice_records WHERE id = ?");
    $stmt->bind_param("i", $record_id);
    $stmt->execute();
    $stmt->close();
    logSecurityEvent("Admin deleted practice record ID: $record_id", "WARNING");
    header("Location: admin.php");
    exit();
}

// Fetch future practice requests
$sql_future = "SELECT pr.id, pr.date, pr.start_time, pr.end_time, pr.transport_to_venue, pr.transport_to_home, pr.pickup_time, pr.pickup_address, pr.dropoff_time, pr.dropoff_address, pr.target_goal, pr.status, u.username, u.id as user_id
        FROM practice_requests pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.date > CURDATE()
        ORDER BY pr.date DESC";
$result_future = $mysqli->query($sql_future);

// Fetch past practice requests
$sql_past = "SELECT pr.id, pr.date, pr.start_time, pr.end_time, pr.transport_to_venue, pr.transport_to_home, pr.pickup_time, pr.pickup_address, pr.dropoff_time, pr.dropoff_address, pr.target_goal, pr.status, u.username, u.id as user_id
        FROM practice_requests pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.date <= CURDATE()
        ORDER BY pr.date DESC";
$result_past = $mysqli->query($sql_past);

// Fetch completed sessions for attendance confirmation and points (approved and past date)
$completed_sql = "SELECT pr.id, pr.date, pr.user_id, u.username, prc.attended, prc.points, prc.id as record_id
                  FROM practice_requests pr
                  JOIN users u ON pr.user_id = u.id
                  LEFT JOIN practice_records prc ON pr.id = prc.request_id
                  WHERE pr.status = 'approved' AND pr.date <= CURDATE()
                  ORDER BY pr.date DESC";
$completed_result = $mysqli->query($completed_sql);

// Fetch all registered users for user management
$users_sql = "SELECT id, username, role FROM users ORDER BY username ASC";
$users_result = $mysqli->query($users_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Band Cafe - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <div class="container mx-auto p-6">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-semibold text-gray-800">Band Cafe</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="text-sm text-slate-600 hover:text-slate-800 font-medium">Logout</a>
            </div>
        </header>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-6">
            <h2 class="text-xl font-medium text-gray-700 mb-2">Admin Dashboard: Manage Practice Requests</h2>
            <p class="text-gray-500">Review and manage all practice requests below.</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 overflow-x-auto">
            <h3 class="text-lg font-medium text-gray-700 mb-2">Future Practice Requests</h3>
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-3 text-left text-sm font-medium text-gray-700">User</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Date</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Start</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">End</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Transport to Venue</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Transport to Home</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Goal</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Status</th>
                        <th class="p-3 text-left text-sm font-medium text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php while ($row = $result_future->fetch_assoc()) {
                        echo "<tr class='hover:bg-gray-50'>";
                        echo "<td class='p-3 text-gray-800'>{$row['username']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['date']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['start_time']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['end_time']}</td>";
                        echo "<td class='p-3 text-gray-800'>" . ($row['transport_to_venue'] ? 'Yes' . ($row['pickup_time'] ? " (Pickup at {$row['pickup_time']}" . ($row['pickup_address'] ? " from {$row['pickup_address']}" : '') : '') : 'No') . "</td>";
                        echo "<td class='p-3 text-gray-800'>" . ($row['transport_to_home'] ? 'Yes' . ($row['dropoff_time'] ? " (Dropoff at {$row['dropoff_time']}" . ($row['dropoff_address'] ? " to {$row['dropoff_address']}" : '') : '') : 'No') . "</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['target_goal']}</td>";
                        echo "<td class='p-3 text-gray-800'>{$row['status']}</td>";
                        echo "<td class='p-3'>";
                        $csrf_token = generateCsrfToken();
                        echo "<a href='admin.php?action=approve&id={$row['id']}&csrf_token={$csrf_token}' class='text-green-600 hover:text-green-800 font-medium mr-3'>Approve</a>";
                        echo "<a href='admin.php?action=reject&id={$row['id']}&csrf_token={$csrf_token}' class='text-red-600 hover:text-red-800 font-medium mr-2'>Reject</a>";
                        echo "<a href='admin.php?delete_request=true&request_id={$row['id']}&csrf_token={$csrf_token}' class='text-sm bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700' onclick='return confirm(\"Are you sure you want to delete this request?\");'>Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    } ?>
                </tbody>
            </table>
            <?php if ($result_future->num_rows === 0): ?>
                <p class="text-center text-gray-500 mt-4">No future practice requests found.</p>
            <?php endif; ?>
            <div class="mt-4">
                <button id="togglePastSessions" class="bg-slate-600 text-white px-4 py-2 rounded hover:bg-slate-700">Show Past Sessions</button>
            </div>
            <div id="pastSessions" class="hidden mt-6">
                <h3 class="text-lg font-medium text-gray-700 mb-2">Past Practice Requests</h3>
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="p-3 text-left text-sm font-medium text-gray-700">User</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Date</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Start</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">End</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Transport to Venue</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Transport to Home</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Goal</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Status</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($row = $result_past->fetch_assoc()) {
                            echo "<tr class='hover:bg-gray-50'>";
                            echo "<td class='p-3 text-gray-800'>{$row['username']}</td>";
                            echo "<td class='p-3 text-gray-800'>{$row['date']}</td>";
                            echo "<td class='p-3 text-gray-800'>{$row['start_time']}</td>";
                            echo "<td class='p-3 text-gray-800'>{$row['end_time']}</td>";
                            echo "<td class='p-3 text-gray-800'>" . ($row['transport_to_venue'] ? 'Yes' . ($row['pickup_time'] ? " (Pickup at {$row['pickup_time']}" . ($row['pickup_address'] ? " from {$row['pickup_address']}" : '') : '') : 'No') . "</td>";
                            echo "<td class='p-3 text-gray-800'>" . ($row['transport_to_home'] ? 'Yes' . ($row['dropoff_time'] ? " (Dropoff at {$row['dropoff_time']}" . ($row['dropoff_address'] ? " to {$row['dropoff_address']}" : '') : '') : 'No') . "</td>";
                            echo "<td class='p-3 text-gray-800'>{$row['target_goal']}</td>";
                            echo "<td class='p-3 text-gray-800'>{$row['status']}</td>";
                            echo "<td class='p-3'>";
                            $csrf_token = generateCsrfToken();
                            echo "<a href='admin.php?action=approve&id={$row['id']}&csrf_token={$csrf_token}' class='text-green-600 hover:text-green-800 font-medium mr-3'>Approve</a>";
                            echo "<a href='admin.php?action=reject&id={$row['id']}&csrf_token={$csrf_token}' class='text-red-600 hover:text-red-800 font-medium mr-2'>Reject</a>";
                            echo "<a href='admin.php?delete_request=true&request_id={$row['id']}&csrf_token={$csrf_token}' class='text-sm bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700' onclick='return confirm(\"Are you sure you want to delete this request?\");'>Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        } ?>
                    </tbody>
                </table>
                <?php if ($result_past->num_rows === 0): ?>
                    <p class="text-center text-gray-500 mt-4">No past practice requests found.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-6">
            <h2 class="text-xl font-medium text-gray-700 mb-2">Confirm Attendance & Assign Points</h2>
            <p class="text-gray-500">Confirm attendance and assign points for completed practice sessions.</p>
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="p-3 text-left text-sm font-medium text-gray-700">User</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Date</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Attended</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Points</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($row = $completed_result->fetch_assoc()) {
                            echo "<tr class='hover:bg-gray-50'>";
                            echo "<td class='p-3 text-gray-800'>{$row['username']}</td>";
                            echo "<td class='p-3 text-gray-800'>{$row['date']}</td>";
                            echo "<td class='p-3 text-gray-800'>" . (isset($row['attended']) ? ($row['attended'] ? 'Yes' : 'No') : 'Not Set') . "</td>";
                            echo "<td class='p-3 text-gray-800'>" . (isset($row['points']) ? $row['points'] : '0') . "</td>";
                            echo "<td class='p-3'>";
                            echo "<form method='POST' action='admin.php'>";
                            echo csrfTokenField();
                            echo "<input type='hidden' name='request_id' value='{$row['id']}'>";
                            echo "<input type='hidden' name='user_id' value='{$row['user_id']}'>";
                            echo "<input type='hidden' name='date' value='{$row['date']}'>";
                            echo "<div class='flex items-center space-x-2'>";
                            echo "<label class='flex items-center space-x-1'>";
                            echo "<input type='checkbox' name='attended' value='1' " . (isset($row['attended']) && $row['attended'] ? 'checked' : '') . ">";
                            echo "<span class='text-sm text-gray-700'>Attended</span>";
                            echo "</label>";
                            echo "<input type='number' name='points' value='" . (isset($row['points']) ? $row['points'] : '0') . "' class='w-20 p-1 border border-gray-300 rounded'>";
                            echo "<button type='submit' name='confirm_attendance' class='text-sm bg-slate-600 text-white px-2 py-1 rounded hover:bg-slate-700'>Save</button>";
                            if (isset($row['record_id']) && $row['record_id']) {
                                $csrf_token = generateCsrfToken();
                                echo "<a href='admin.php?delete_record=true&record_id={$row['record_id']}&csrf_token={$csrf_token}' class='text-sm bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 ml-2' onclick='return confirm(\"Are you sure you want to delete this record?\");'>Delete</a>";
                            }
                            echo "</div>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        } ?>
                    </tbody>
                </table>
                <?php if ($completed_result->num_rows === 0): ?>
                    <p class="text-center text-gray-500 mt-4">No completed practice sessions found.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 mb-6">
            <h2 class="text-xl font-medium text-gray-700 mb-2">Manage Users</h2>
            <p class="text-gray-500">View, update, and delete registered users below.</p>
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Username</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Role</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($user = $users_result->fetch_assoc()) {
                            echo "<tr class='hover:bg-gray-50'>";
                            echo "<td class='p-3 text-gray-800'>{$user['username']}</td>";
                            echo "<td class='p-3 text-gray-800'>{$user['role']}</td>";
                            echo "<td class='p-3'>";
                            echo "<form method='POST' action='admin.php' class='inline-block'>";
                            echo csrfTokenField();
                            echo "<input type='hidden' name='user_id' value='{$user['id']}'>";
                            echo "<div class='flex items-center space-x-2'>";
                            echo "<input type='text' name='username' value='{$user['username']}' class='w-32 p-1 border border-gray-300 rounded'>";
                            echo "<select name='role' class='p-1 border border-gray-300 rounded'>";
                            echo "<option value='user'" . ($user['role'] == 'user' ? ' selected' : '') . ">User</option>";
                            echo "<option value='admin'" . ($user['role'] == 'admin' ? ' selected' : '') . ">Admin</option>";
                            echo "</select>";
                            echo "<button type='submit' name='update_user' class='text-sm bg-slate-600 text-white px-2 py-1 rounded hover:bg-slate-700'>Update</button>";
                            if ($user['id'] != $_SESSION['user_id']) {
                                $csrf_token = generateCsrfToken();
                                echo "<a href='admin.php?delete_user=true&user_id={$user['id']}&csrf_token={$csrf_token}' class='text-sm bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a>";
                            }
                            echo "</div>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        } ?>
                    </tbody>
                </table>
                <?php if ($users_result->num_rows === 0): ?>
                    <p class="text-center text-gray-500 mt-4">No registered users found.</p>
                <?php endif; ?>
            </div>
        </div>
        <nav class="mt-6 flex justify-center space-x-6">
            <a href="dashboard.php" class="text-slate-600 hover:text-slate-800 font-medium">Back to Dashboard</a>
            <span class="text-gray-300">|</span>
            <a href="roster.php" class="text-slate-600 hover:text-slate-800 font-medium">Roster Management</a>
        </nav>
    </div>
    <script>
        document.getElementById('togglePastSessions').addEventListener('click', function() {
            const pastSessions = document.getElementById('pastSessions');
            if (pastSessions.classList.contains('hidden')) {
                pastSessions.classList.remove('hidden');
                this.textContent = 'Hide Past Sessions';
            } else {
                pastSessions.classList.add('hidden');
                this.textContent = 'Show Past Sessions';
            }
        });
    </script>
</body>
</html>
