<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}
require 'config.php';

// Fetch only approved events (and optionally filter by user or all)
$sql = "SELECT date, start_time, end_time, target_goal 
        FROM practice_requests WHERE status = 'approved'";
$result = $mysqli->query($sql);
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['target_goal'],
        'start' => $row['date'] . 'T' . $row['start_time'],
        'end'   => $row['date'] . 'T' . $row['end_time']
    ];
}
echo json_encode($events);
?>
