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
    <div>
        <h1>Welcome, <?php echo htmlspecialchars($currentUserName); ?>!</h1>
        <p>Your role: <?php echo htmlspecialchars($currentRole); ?></p>
        <p>This is the main page. Use the navigation bar to explore different sections based on your role.</p>
    </div>

    <div>
        <a href="?logout=1" class="logout-link">Logout</a>
    </div>
</body>

</html>