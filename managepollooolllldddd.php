<?php
session_start();
require_once 'db.php';

// Fetch all polls
$stmt = $conn->prepare("SELECT * FROM polls");
$stmt->execute();
$polls = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pollName = filter_input(INPUT_POST, 'pollName', FILTER_SANITIZE_STRING);
    $pollDescription = filter_input(INPUT_POST, 'pollDescription', FILTER_SANITIZE_STRING);
    $pollQuorum = filter_input(INPUT_POST, 'pollQuorum', FILTER_SANITIZE_NUMBER_INT);

    // Sanitize the options string
    $pollOptions = filter_input(INPUT_POST, 'pollOptions', FILTER_SANITIZE_STRING);

    $stmt = $conn->prepare("INSERT INTO polls (name, description, options, quorum) VALUES (?, ?, ?, ?)");
    $stmt->execute([$pollName, $pollDescription, $pollOptions, $pollQuorum]);

    $_SESSION['message'] = "Poll added successfully";
    header("Location: ManagePools.html");
}

?>