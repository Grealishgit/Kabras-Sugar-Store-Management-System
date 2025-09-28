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

// Restrict only Cashier
if ($currentUser['role'] !== 'Cashier') {
    header('Location: ../login.php?error=Access denied. Cashier privileges required.');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/cashier-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($currentUser['name']); ?> 👋</h1>
        <p class="subtitle">Here’s your cashier dashboard</p>

        <!-- Quick Actions -->
        <div class="quick-links">
            <a href="pos.php" class="ql-btn"><i class="fas fa-cash-register"></i> New Sale</a>
            <a href="receipts.php" class="ql-btn"><i class="fas fa-receipt"></i> Receipts</a>
            <a href="transactions.php" class="ql-btn"><i class="fas fa-list"></i> Transactions</a>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Today’s Sales</h3>
                <p>Ksh 12,450</p>
            </div>
            <div class="stat-card">
                <h3>Total Transactions</h3>
                <p>56</p>
            </div>
            <div class="stat-card">
                <h3>Pending Receipts</h3>
                <p>8</p>
            </div>
            <div class="stat-card">
                <h3>Refunds Issued</h3>
                <p>2</p>
            </div>
            <div class="stat-card">
                <h3>Low Stock Alerts</h3>
                <p>5</p>
            </div>
            <div class="stat-card">
                <h3>Shift Duration</h3>
                <p>3h 20m</p>
            </div>
        </div>
    </main>
</body>

</html>