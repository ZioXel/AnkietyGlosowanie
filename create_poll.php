<?php
session_start();
require_once 'db.php';

if (!isset($_POST['name'], $_POST['description'], $_POST['options'], $_POST['quorum'])) {
    die('Missing required fields');
}

$name = $_POST['name'];
$description = $_POST['description'];
$options = explode(';', $_POST['options']); // Split the options string into an array
$quorum = $_POST['quorum'];
$created_at = date('Y-m-d H:i:s'); // Current date and time

$stmt = $conn->prepare("INSERT INTO polls (name, description, quorum, created_at) VALUES (?, ?, ?, ?)");
$stmt->execute([$name, $description, $quorum, $created_at]);
$pollId = $conn->lastInsertId();

if ($pollId === 0 || $pollId === false) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to generate poll ID']);
    exit;
}

if ($pollId === 0 || $pollId === false) {
    die('Failed to generate poll ID');
}

foreach ($options as $option) {
    $option = trim($option); // Remove any leading or trailing whitespace
    if (!empty($option)) { // Skip empty options
        // Insert a record in the votes table with a count of 0
        $stmt = $conn->prepare("INSERT INTO votes (poll_id, option_name, votes) VALUES (?, ?, 0)");
        $stmt->execute([$pollId, $option]);
    }
}

$_SESSION['message'] = "Poll added successfully";
echo json_encode(['status' => 'success', 'message' => 'Poll added successfully']);
header("ManagePools.html"); // Replace with your own success page
?>