<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}
require 'config.php';

$user_id = $_SESSION['user_id'];

// Get user information
$user_sql = "SELECT role FROM users WHERE id = ?";
$user_stmt = $mysqli->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Build the query based on filters
$whereConditions = [];
$params = [];
$types = "";

// Status filter
$status_filter = $_GET['status'] ?? 'all';
if ($status_filter !== 'all') {
    $whereConditions[] = "pr.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// View filter (my sessions only)
$view_filter = $_GET['view'] ?? 'all';
if ($view_filter === 'my-sessions') {
    $whereConditions[] = "pr.user_id = ?";
    $params[] = $user_id;
    $types .= "i";
}

// Section filter (admin only)
$section_filter = $_GET['section'] ?? 'all';
if ($section_filter !== 'all' && $user_data['role'] === 'admin') {
    $whereConditions[] = "u.section = ?";
    $params[] = $section_filter;
    $types .= "s";
}

// Build the complete query
$sql = "SELECT pr.id, pr.date, pr.start_time, pr.end_time, pr.target_goal, pr.status, pr.user_id,
               u.username, u.instrument, u.section
        FROM practice_requests pr 
        JOIN users u ON pr.user_id = u.id";

if (!empty($whereConditions)) {
    $sql .= " WHERE " . implode(" AND ", $whereConditions);
}

$sql .= " ORDER BY pr.date ASC, pr.start_time ASC";

$stmt = $mysqli->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['id'],
        'title' => $row['target_goal'],
        'start' => $row['date'] . 'T' . $row['start_time'],
        'end' => $row['date'] . 'T' . $row['end_time'],
        'extendedProps' => [
            'status' => $row['status'],
            'user_id' => $row['user_id'],
            'username' => $row['username'],
            'instrument' => $row['instrument'],
            'section' => $row['section']
        ]
    ];
}

header('Content-Type: application/json');
echo json_encode($events);
?>
