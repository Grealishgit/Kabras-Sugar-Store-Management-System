<?php

require_once __DIR__ . '/handlers/AuthHandler.php';
$authHandler = new AuthHandler();
if (isset($_GET['logout'])) {
    $authHandler->handleLogout();
    header('Location: login.php');
    exit;
}
if (!$authHandler->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
$currentUser = $authHandler->getCurrentUser();
if (!$currentUser) {
    $authHandler->handleLogout();
    header('Location: login.php');
    exit;
}
$isLoggedIn = true;
$currentRole = $currentUser['role'];
$currentUserName = $currentUser['name'];

function getGreeting()
{
    date_default_timezone_set('Africa/Nairobi');
    $hour = intval(date('G'));
    if ($hour < 12) return 'Good morning';
    if ($hour < 18) return 'Good afternoon';
    return 'Good evening';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['continue_dashboard'])) {

        if ($currentRole === 'Admin') {
            header('Location: admin/dashboard.php');
            exit;
        }
        if ($currentRole === 'Accountant') {
            header('Location: accountant/dashboard.php');
            exit;
        } elseif ($currentRole === 'Manager') {
            header('Location: manager/dashboard.php');
            exit;
        } elseif ($currentRole === 'Cashier') {
            header('Location: cashier/dashboard.php');
            exit;
        } else {
            header('Location: dashboard.php');
            exit;
        }
    }
    if (isset($_POST['logout'])) {
        $authHandler->handleLogout();
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./main.css">
    <title>Welcome</title>

</head>

<body>
    <div class="split-layout">
        <div class="split-section left-bg"></div>
        <div class="split-section right-bg"></div>
        <div class="center-modal">
            <img src="./uploads/kabras-logo.png" alt="kabras-logo">
            <div class="greeting"><?= getGreeting() ?>!</div>
            <div class="username">Welcome, <span><strong><?= htmlspecialchars($currentUserName) ?></strong></span>
            </div>
            <form method="post" style="margin-bottom: 0;">
                <button type="submit" name="continue_dashboard" class="btn">Dashboard</button>
                <button type="submit" name="logout" class="btn logout-btn">Logout</button>
            </form>
        </div>
    </div>

</body>

</html>