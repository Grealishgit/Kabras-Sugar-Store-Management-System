<?php
session_start();

require_once __DIR__ . '/handlers/AuthHandler.php';

// Initialize authentication handler
$authHandler = new AuthHandler();

// Handle logout if requested
if (isset($_GET['logout'])) {
    $authHandler->handleLogout();
    header('Location: login.php');
    exit;
}

// Check if user is logged in
if (!$authHandler->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get current logged-in user
$currentUser = $authHandler->getCurrentUser();

// If somehow session exists but user not found in DB
if (!$currentUser) {
    $authHandler->handleLogout();
    header('Location: login.php');
    exit;
}

// Set helper variables for use in pages
$isLoggedIn = true;
$currentRole = $currentUser['role'];
$currentUserName = $currentUser['name'];
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    Hello User
</body>

</html>