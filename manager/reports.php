<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && $_POST['logout'] == '1') {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

require_once '../handlers/AuthHandler.php';


$authHandler = new AuthHandler();


// Ensure user is logged in
if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in first.');
    exit();
}

$currentUser = $authHandler->getCurrentUser();

// Restrict only Cashier
if ($currentUser['role'] !== 'Manager') {
    header('Location: ../login.php?error=Access denied. Cashier privileges required.');
    exit();
}

require_once '../handlers/SalesHandler.php';
require_once '../app/models/User.php';

$salesHandler = new SalesHandler();
$userModel = new User();

// Fetch sales data
$todaySales = $salesHandler->getTodaySalesAll();
$weeklySalesArr = $salesHandler->getWeeklySales(0); // 0 for all users
$monthlySalesArr = $salesHandler->getMonthlySales(0); // 0 for all users
$weeklySales = is_array($weeklySalesArr) && isset($weeklySalesArr['total_amount']) ? (float)$weeklySalesArr['total_amount'] : (float)$weeklySalesArr;
$monthlySales = is_array($monthlySalesArr) && isset($monthlySalesArr['total_amount']) ? (float)$monthlySalesArr['total_amount'] : (float)$monthlySalesArr;
$allSales = $salesHandler->getAllSales();

// Calculate average daily, weekly, monthly sales
$totalSalesCount = count($allSales);
$totalSalesAmount = 0;
foreach ($allSales as $s) {
    $totalSalesAmount += isset($s['total_amount']) ? $s['total_amount'] : 0;
}

// Calculate actual periods
$firstSaleDate = null;
$lastSaleDate = null;
foreach ($allSales as $s) {
    $saleDate = isset($s['date']) ? $s['date'] : (isset($s['sale_date']) ? $s['sale_date'] : null);
    if ($saleDate) {
        if (!$firstSaleDate || strtotime($saleDate) < strtotime($firstSaleDate)) {
            $firstSaleDate = $saleDate;
        }
        if (!$lastSaleDate || strtotime($saleDate) > strtotime($lastSaleDate)) {
            $lastSaleDate = $saleDate;
        }
    }
}
$days = $firstSaleDate && $lastSaleDate ? max(1, (strtotime($lastSaleDate) - strtotime($firstSaleDate)) / 86400 + 1) : 1;
$weeks = $days / 7;
$months = $days / 30.44;

$averageDailySales = $totalSalesCount > 0 ? $totalSalesAmount / $days : 0;
$averageWeeklySales = $totalSalesCount > 0 ? $totalSalesAmount / $weeks : 0;
$averageMonthlySales = $totalSalesCount > 0 ? $totalSalesAmount / $months : 0;

// CSV download logic
if (isset($_POST['download_csv']) && $_POST['download_csv'] === '1') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_report.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Sale ID', 'Product',  'Quantity', 'Sale Amount', 'Sale Date', 'Sold By'], ',', '"', '\\');
    foreach ($allSales as $sale) {
        $user = $userModel->findById($sale['user_id']);
        $soldBy = $user ? $user['name'] : $sale['user_id'];
        fputcsv($out, [
            $sale['id'],
            $sale['product_name'] ?? '',
            $sale['quantity'] ?? '',
            $sale['total_amount'] ?? '',
            $sale['date'] ?? $sale['sale_date'] ?? '',
            $soldBy
        ], ',', '"', '\\');
    }
    fclose($out);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | Reports</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/reports.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <h1 style="margin-bottom:18px;">Sales Reports</h1>
        <form method="post" style="margin-bottom:18px;">
            <input type="hidden" name="download_csv" value="1">
            <button type="submit" class="btn">Download Sales CSV</button>
        </form>

        <div class="dashboard-cards-row">
            <div class="dashboard-card">
                <h4>Today's Sales</h4>
                <div class="card-value">Ksh <?= number_format($todaySales, 2) ?></div>
            </div>
            <div class="dashboard-card">
                <h4>Weekly Sales</h4>
                <div class="card-value">Ksh
                    <?= is_array($weeklySales) && isset($weeklySales['total_amount']) ? number_format($weeklySales['total_amount'], 2) : number_format($weeklySales, 2) ?>
                </div>
            </div>
            <div class="dashboard-card">
                <h4>Monthly Sales</h4>
                <div class="card-value">Ksh
                    <?= is_array($monthlySales) && isset($monthlySales['total_amount']) ? number_format($monthlySales['total_amount'], 2) : number_format($monthlySales, 2) ?>
                </div>
            </div>
            <div class="dashboard-card">
                <h4>Average Daily Sales</h4>
                <div class="card-value">Ksh <?= number_format($averageDailySales, 2) ?></div>
            </div>
            <div class="dashboard-card">
                <h4>Average Weekly Sales</h4>
                <div class="card-value">Ksh <?= number_format($averageWeeklySales, 2) ?></div>
            </div>
            <div class="dashboard-card">
                <h4>Average Monthly Sales</h4>
                <div class="card-value">Ksh <?= number_format($averageMonthlySales, 2) ?></div>
            </div>
        </div>

        <div class="dashboard-table" style="margin-top:32px;">
            <h3>All Sales</h3>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Product</th>
                        <th>Customer ID</th>
                        <th>Quantity</th>
                        <th>Sale Amount</th>
                        <th>Sale Date</th>
                        <th>Sold By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allSales as $sale): ?>
                        <?php $user = $userModel->findById($sale['user_id']); ?>
                        <tr>
                            <td><?= $sale['id'] ?></td>
                            <td><?= isset($sale['product_name']) ? htmlspecialchars((string)$sale['product_name']) : '' ?>
                            </td>
                            <td><?= isset($sale['customer_id']) ? htmlspecialchars((string)$sale['customer_id']) : '' ?>
                            </td>
                            <td><?= isset($sale['quantity']) ? $sale['quantity'] : '' ?></td>
                            <td>Ksh
                                <?= isset($sale['total_amount']) && is_numeric($sale['total_amount']) ? number_format($sale['total_amount'], 2) : '0.00' ?>
                            </td>
                            <td><?= isset($sale['date']) && $sale['date'] !== null ? $sale['date'] : (isset($sale['sale_date']) && $sale['sale_date'] !== null ? $sale['sale_date'] : '') ?>
                            </td>
                            <td><?= $user && isset($user['name']) && $user['name'] !== null ? htmlspecialchars((string)$user['name']) : $sale['user_id'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($allSales)): ?><tr>
                            <td colspan="7">No sales found.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>