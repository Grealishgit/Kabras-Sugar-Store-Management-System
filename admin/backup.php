<?php
session_start();
require_once '../handlers/AuthHandler.php';
$authHandler = new AuthHandler();

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    $authHandler->handleLogout();
    header('Location: ../login.php?success=You have been logged out.');
    exit();
}

// Ensure user is logged in and is admin
if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in to access the admin dashboard.');
    exit();
}

$currentUser = $authHandler->getCurrentUser();
if ($currentUser['role'] !== 'Admin') {
    header('Location: ../login.php?error=Access denied. Admin privileges required.');
    exit();
}


// Get all users
$users = $authHandler->getAllUsers();
// 
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin-Backup</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">

</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
</body>

</html>