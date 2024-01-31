<?php

$uri = "censored";
$username = "censored";
$password = "censored";

try {
    $conn = new PDO($uri, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


?>
