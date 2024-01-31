<?php
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
            $_SESSION['userId'] = $user['id']; // Store the user ID in the session

            // Set $_SESSION['userRole'] based on the 'role' field in the database
            $_SESSION['userRole'] = $user['role'];

            if ($user['role'] === 'admin') {
                echo 'adminpage.html';
            } elseif ($user['role'] === 'staff') {
                echo 'managepolls.html';
            } else {
                echo 'viewpolls.html';
            }
        } else {
            echo "Invalid username or password";
        }
    }
}
?>
