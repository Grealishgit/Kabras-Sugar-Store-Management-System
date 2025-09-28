<?php
session_start();
require_once '../handlers/AuthHandler.php';
require_once '../app/models/User.php';

$authHandler = new AuthHandler();
$userHandler = new User();

// Ensure user is logged in
if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in first.');
    exit();
}

$currentUser = $authHandler->getCurrentUser();

// Restrict only Admins
if ($currentUser['role'] !== 'Cashier') {
    header('Location: ../login.php?error=Access denied. Admin privileges required.');
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard | Receipts</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
</body>

</html>