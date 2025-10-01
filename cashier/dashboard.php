<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && $_POST['logout'] == '1') {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

require_once '../handlers/AuthHandler.php';
require_once '../handlers/PaymentsHandler.php';
require_once '../handlers/SalesHandler.php';
require_once '../handlers/CustomerHandler.php';

$authHandler = new AuthHandler();
$paymentsHandler = new PaymentsHandler();
$salesHandler = new SalesHandler();
$customerHandler = new CustomerHandler();

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

// Get all products
$products = $salesHandler->getProducts();

// Get all customers for dropdown
$customers = $customerHandler->getAllCustomers();

// Get statistics for dashboard
$customerStats = $customerHandler->getCustomerStats();
$totalCustomers = $customerStats['total'];

// Get total sales count (we'll use existing methods)
$allSalesData = $salesHandler->getSalesByCashier($currentUser['id']);
$totalSalesCount = count($allSalesData);

// Get total payments count
$allPayments = $paymentsHandler->getAllPayments();
$totalPaymentsCount = count($allPayments);

// Get new customers today
$today = date('Y-m-d');
$newCustomersToday = 0;
foreach ($customers as $customer) {
    if (date('Y-m-d', strtotime($customer['created_at'])) === $today) {
        $newCustomersToday++;
    }
}

// Get stats

$totalSales = $salesHandler->getTotalSales();
$todaySales = $salesHandler->getTodaySalesAll();

$totalPayments = $paymentsHandler->getTotalPayments();
$todayPayments = $paymentsHandler->getTodayPayments();

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
        <h1>Welcome, <?= htmlspecialchars($currentUser['name']); ?> 👋</h1>
        <p class="subtitle">Here’s your cashier dashboard</p>

        <!-- Quick Actions -->
        <div class="quick-links">
            <a href="sales.php" class="ql-btn"><i class="fas fa-cash-register"></i> New Sale</a>
            <a href="receipts.php" class="ql-btn"><i class="fas fa-receipt"></i> Receipts</a>
            <a href="payments.php" class="ql-btn"><i class="fas fa-money-bill-wave"></i> Payments</a>
            <a href="customers.php" class="ql-btn"><i class="fas fa-user-friends"></i> Customers</a>
        </div>
        <h2><i class="fas fa-chart-bar"></i> Dashboard Overview</h2>
        <!-- Stats Grid -->
        <div class="stats-grid">

            <div class="stat-card">
                <h3>Today’s Sales</h3>
                <p>Ksh <?= number_format($todaySales, 2); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Sales</h3>
                <p>Ksh <?= number_format($totalSales, 2); ?></p>
            </div>
            <div class="stat-card">
                <h3>Today’s Payments</h3>
                <p>Ksh <?= number_format($todayPayments, 2); ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Payments</h3>
                <p>Ksh <?= number_format($totalPayments, 2); ?></p>
            </div>
        </div>
        <!-- Dashboard Statistics Cards -->
        <div class="dashboard-stats">
            <div class="overview-grid">
                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="overview-content">
                        <h3><?= number_format($totalSalesCount) ?></h3>
                        <p>Total Sales</p>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="overview-content">
                        <h3><?= number_format($totalCustomers) ?></h3>
                        <p>Total Customers</p>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="overview-content">
                        <h3><?= number_format($totalPaymentsCount) ?></h3>
                        <p>Total Payments</p>
                    </div>
                </div>
                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="overview-content">
                        <h3><?= number_format($newCustomersToday) ?></h3>
                        <p>New Customers Today</p>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Fetch recent payments and sales for current cashier
        $recentPayments = $paymentsHandler->getPaymentsByUser($currentUser['id']);
        $recentPayments = array_slice($recentPayments, 0, 10);
        $recentSales = $salesHandler->getRecentSales($currentUser['id'], 10);
        ?>

        <div class="tables-row">
            <div class="table-container">
                <h2>Recent Sales</h2>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSales as $sale): ?>
                        <tr>
                            <td><?= date('d M Y H:i', strtotime($sale['sale_date'])); ?></td>
                            <td><?= htmlspecialchars($sale['product_name']); ?></td>
                            <td><?= $sale['quantity']; ?></td>
                            <td>Ksh <?= number_format($sale['unit_price'], 2); ?></td>
                            <td>Ksh <?= number_format($sale['total_amount'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-container">
                <h2>Recent Payments</h2>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPayments as $payment): ?>
                        <tr>
                            <td><?= date('d M Y H:i', strtotime($payment['payment_date'])); ?></td>
                            <td><?= htmlspecialchars($payment['customer_name'] ?? ''); ?></td>
                            <td>Ksh <?= number_format($payment['amount'], 2); ?></td>
                            <td><?= htmlspecialchars($payment['method']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</body>

</html>