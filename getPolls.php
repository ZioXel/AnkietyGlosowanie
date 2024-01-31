<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'db.php';
session_start();

if (!isset($_SESSION['userId'])) {
    error_log("User ID not set in session\n", 3, "error_log.txt");
    echo json_encode([]);
    exit();
}

// If the user is a staff member, fetch all polls
if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'staff') {
    $stmt = $conn->prepare("SELECT * FROM polls");
    $stmt->execute();
} else {
    // If the user is not a staff member, fetch only the polls they are associated with
    $stmt = $conn->prepare("SELECT * FROM polls INNER JOIN user_polls ON polls.id = user_polls.poll_id WHERE user_polls.user_id = ?");
    $stmt->execute([$_SESSION['userId']]);
}


if ($stmt->errorCode() != 0) {
    error_log("SQL Error: " . implode(", ", $stmt->errorInfo()) . "\n", 3, "error_log.txt");
    echo json_encode([]);
    exit();
}

$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($polls as &$poll) {
    $stmt2 = $conn->prepare("SELECT `option_name`, SUM(votes) as votes FROM votes WHERE poll_id = ? GROUP BY `option_name`");
    $stmt2->execute([$poll['id']]);
    $votes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $options = [];
    foreach ($votes as $vote) {
        $options[] = $vote['option_name'] . ' (' . $vote['votes'] . ' votes)';
    }

    $poll['options'] = implode('; ', $options);
}

echo json_encode($polls);
?>