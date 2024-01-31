<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_POST['pollId'])) {
    echo json_encode(['status' => 'error', 'message' => 'No pollId provided']);
    exit;
}

$pollId = $_POST['pollId'];


$stmt = $conn->prepare("DELETE FROM polls WHERE id = ?");
$stmt->execute([$pollId]);

$stmt2 = $conn->prepare("DELETE FROM votes WHERE poll_id = ?");
$stmt2->execute([$pollId]);

echo json_encode(['status' => 'error', 'message' => 'An error occurred']);

?>