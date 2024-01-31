<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pollId = $_POST['pollId'];

    $stmt = $conn->prepare("SELECT option_name FROM votes WHERE poll_id = ?");
    $stmt->execute([$pollId]);
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($options as $option) {
        $votes = rand(0, 100); // Generate a random number of votes between 0 and 100

        $stmt = $conn->prepare("UPDATE votes SET votes = ? WHERE poll_id = ? AND option_name = ?");
        $stmt->execute([$votes, $pollId, $option['option_name']]);
    }

    header('Location: editPoll.php?pollId=' . $pollId);
}
?>