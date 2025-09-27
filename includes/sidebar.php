<?php
// Assume session and access control are handled in the main file
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kabras Store</title>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>

            <?php if ($currentUser['role'] === 'admin'): ?>
                <li><a href="admin/users.php">User Management</a></li>
                <li><a href="admin/security.php">Security & Backup</a></li>
                <li><a href="admin/reports.php">Reports</a></li>
                <li><a href="admin/inventory.php">Inventory</a></li>
                <li><a href="admin/sales.php">Sales Overview</a></li>

            <?php elseif ($role === 'StoreKeeper'): ?>
                <li><a href="add_stock.php">Add/Update Stock</a></li>
                <li><a href="stock_levels.php">Stock Levels</a></li>
                <li><a href="alerts.php">Stock Alerts</a></li>

            <?php elseif ($role === 'Cashier'): ?>
                <li><a href="pos.php">Point of Sale</a></li>
                <li><a href="receipt.php">Issue Receipt</a></li>
                <li><a href="transactions.php">Daily Transactions</a></li>

            <?php elseif ($role === 'Manager'): ?>
                <li><a href="sales_reports.php">Sales Reports</a></li>
                <li><a href="discounts.php">Authorize Discounts/Refunds</a></li>
                <li><a href="performance.php">Performance Monitoring</a></li>

            <?php elseif ($role === 'Auditor'): ?>
                <li><a href="financial_reports.php">Financial Reports</a></li>
                <li><a href="transactions.php">Transaction History</a></li>
                <li><a href="audit_logs.php">Audit Logs</a></li>
            <?php endif; ?>
        </ul>
        <div>
            <div>
                <h2>Welcome, <?php echo htmlspecialchars($currentUser['name']); ?></h2>
                <p>Role: <strong><?php echo htmlspecialchars($currentUser['role'] ?? 'No Role'); ?></strong></p>
            </div>
            <a href="?logout=1" class="logout-link">Logout</a>
        </div>
    </div>
</body>

</html>