<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'admin') {
    echo 'Error: You do not have permission to add a user.';
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['password'], $_POST['permission'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $permission = $_POST['permission'];

        // Check if the username already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            echo "Username already exists";
            return;
        }

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $permission]);

        echo "User added successfully";
    }
}