<?php
// db.php
$uri = "mysql:host=nwx.h.filess.io;port=3307;dbname=glosowanie_fullcaveby";
$username = "glosowanie_fullcaveby";
$password = "449ef9635c85fa699c3743b890cf12fb503db667";

try {
    $conn = new PDO($uri, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// start.php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                echo 'adminpage.php';
            } elseif ($user['role'] === 'staff') {
                echo 'manage_polls.php';
            } else {
                echo 'view_polls.php';
            }
        } else {
            echo "Invalid username or password";
        }
    }
}
// create_poll.php
require_once 'db.php';
$question = $_POST['question'];
$options = $_POST['options']; // This should be an array

$stmt = $conn->prepare("INSERT INTO polls (question) VALUES (?)");
$stmt->execute([$question]);
$pollId = $conn->lastInsertId();

foreach ($options as $option) {
    $stmt = $conn->prepare("INSERT INTO options (poll_id, option) VALUES (?, ?)");
    $stmt->execute([$pollId, $option]);
}

// vote.php
require_once 'db.php';
$pollId = $_POST['poll_id'];
$optionId = $_POST['option_id'];

$stmt = $conn->prepare("INSERT INTO votes (poll_id, option_id) VALUES (?, ?)");
$stmt->execute([$pollId, $optionId]);

// results.php
require_once 'db.php';
$pollId = $_GET['poll_id'];

$stmt = $conn->prepare("SELECT options.option, COUNT(votes.id) as votes FROM options LEFT JOIN votes ON options.id = votes.option_id WHERE options.poll_id = ? GROUP BY options.id");
$stmt->execute([$pollId]);
$results = $stmt->fetchAll();

echo json_encode($results);


?>