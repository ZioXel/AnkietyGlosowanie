<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['userRole']) || $_SESSION['userRole'] !== 'admin') {
    echo 'Error: You do not have permission to update a user.';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['permission'])) {
        $username = $_POST['username'];
        $permission = $_POST['permission'];

        // Check if the username exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() === 0) {
            echo "Username not found";
            return;
        }

        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE username = ?");
        $stmt->execute([$permission, $username]);

        if (isset($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->execute([$password, $username]);
        }

        echo "User updated successfully";
    }
}