<?php
require 'config.php';
require_once 'includes/security.php';
configureSecureSession();
session_start();
setSecurityHeaders();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

// Fetch only approved events (and optionally filter by user or all)
$sql = "SELECT date, start_time, end_time, target_goal 
        FROM practice_requests WHERE status = 'approved'";
$result = $mysqli->query($sql);
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => htmlspecialchars($row['target_goal'], ENT_QUOTES, 'UTF-8'),
        'start' => $row['date'] . 'T' . $row['start_time'],
        'end'   => $row['date'] . 'T' . $row['end_time']
    ];
}
header('Content-Type: application/json');
echo json_encode($events);
?>
