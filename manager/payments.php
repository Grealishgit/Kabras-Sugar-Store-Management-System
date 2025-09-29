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


// CSV download logic - must be BEFORE any HTML output
if (isset($_POST['download_csv']) && $_POST['download_csv'] === '1') {
    require_once '../handlers/PaymentsHandler.php';
    require_once '../handlers/SalesHandler.php';
    require_once '../app/models/User.php';
    $paymentHandler = new PaymentsHandler();
    $saleHandler = new SalesHandler();
    $userModel = new User();
    $payments = $paymentHandler->getAllPayments();
    $sales = $saleHandler->getAllSales();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_payments.csv"');
    $out = fopen('php://output', 'w');

    // CSV header
    fputcsv($out, [
        'Sale ID',
        'Product',
        'Quantity',
        'Sale Amount',
        'Sale Date',
        'Sold By',
    ], ',', '"', '\\');

    foreach ($sales as $sale) {
        $user = $userModel->findById($sale['user_id']);
        $soldBy = $user ? $user['name'] : $sale['user_id'];

        // Find payment for this sale
        $payment = null;
        foreach ($payments as $p) {
            if ($p['sale_id'] == $sale['id']) {
                $payment = $p;
                break;
            }
        }

        fputcsv($out, [
            $sale['id'],
            $sale['product_name'] ?? '',
            $sale['quantity'] ?? '',
            $sale['total_amount'] ?? '',
            $sale['date'] ?? $sale['sale_date'] ?? '',
            $soldBy,
        ], ',', '"', '\\');
    }

    fclose($out);
    exit(); // important to stop the script
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | Payments</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/payments.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <h1 style="margin-bottom:18px;">Payments & Sales Overview</h1>

        <?php
        require_once '../handlers/PaymentsHandler.php';
        require_once '../handlers/SalesHandler.php';
        require_once '../app/models/User.php';

        $paymentHandler = new PaymentsHandler();
        $saleHandler = new SalesHandler();
        $userModel = new User();

        // Fetch payments and sales
        $payments = $paymentHandler->getAllPayments();
        $sales = $saleHandler->getAllSales();

        // Payment stats
        $totalPayments = 0;
        $totalSales = 0;
        $totalPaid = 0;
        $totalSalesAmount = 0;
        $paymentMethods = [];
        $totalPaymentAmount = 0;
        foreach ($payments as $p) {
            $totalPayments++;
            $totalPaid += $p['amount'];
            $totalPaymentAmount += $p['amount'];
            if (isset($p['method']) && $p['method']) {
                $paymentMethods[] = $p['method'];
            }
        }
        foreach ($sales as $s) {
            $totalSales++;
            if (isset($s['total_amount']) && is_numeric($s['total_amount'])) {
                $totalSalesAmount += $s['total_amount'];
            }
        }
        $averageSales = $totalSales > 0 ? $totalSalesAmount / $totalSales : 0;
        $averagePayments = $totalPayments > 0 ? $totalPaymentAmount / $totalPayments : 0;
        $commonPaymentMethod = '';
        if (!empty($paymentMethods)) {
            $counts = array_count_values($paymentMethods);
            arsort($counts);
            $commonPaymentMethod = array_key_first($counts);
        }



        ?>

        <div class="dashboard-cards-row">
            <div class="dashboard-card">
                <h4>Total Payments</h4>
                <div class="card-value"><?= $totalPayments ?></div>
            </div>
            <div class="dashboard-card">
                <h4>Total Paid</h4>
                <div class="card-value">Ksh <?= number_format($totalPaid, 2) ?></div>
            </div>
            <div class="dashboard-card">
                <h4>Total Sales</h4>
                <div class="card-value"><?= $totalSales ?></div>
            </div>
            <div class="dashboard-card">
                <h4>Average Sales</h4>
                <div class="card-value">Ksh <?= number_format($averageSales, 2) ?></div>
            </div>
            <div class="dashboard-card">
                <h4>Average Payment</h4>
                <div class="card-value">Ksh <?= number_format($averagePayments, 2) ?></div>
            </div>
            <div class="dashboard-card">
                <h4>Common Payment Method</h4>
                <div class="card-value"><?= $commonPaymentMethod ? htmlspecialchars($commonPaymentMethod) : 'N/A' ?>
                </div>
            </div>
        </div>

        <form method="post" style="margin-bottom:18px;">
            <input type="hidden" name="download_csv" value="1">
            <button type="submit" class="btn">Download Sales & Payments CSV</button>
        </form>

        <div class="dashboard-row" style="display:flex; gap:32px; flex-wrap:wrap;">
            <div class="dashboard-table left-table" style="flex:1; min-width:340px;">
                <h3>Payments Table</h3>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td>Ksh <?= number_format($p['amount'], 2) ?></td>
                                <td><?= isset($p['date']) && $p['date'] !== null ? $p['date'] : (isset($p['payment_date']) && $p['payment_date'] !== null ? $p['payment_date'] : '') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($payments)): ?><tr>
                                <td colspan="4">No payments found.</td>
                            </tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="dashboard-table right-table" style="flex:1; min-width:340px;">
                <h3>Sales Table</h3>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Sold By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $s): ?>
                            <?php $user = $userModel->findById($s['user_id']); ?>
                            <tr>
                                <td><?= $s['id'] ?></td>
                                <td><?= isset($s['product_name']) && $s['product_name'] !== null ? htmlspecialchars((string)$s['product_name']) : '' ?>
                                </td>
                                <td><?= isset($s['quantity']) ? $s['quantity'] : '' ?></td>
                                <td>Ksh
                                    <?= isset($s['total_amount']) && is_numeric($s['total_amount']) ? number_format($s['total_amount'], 2) : '0.00' ?>
                                </td>
                                <td><?= isset($s['date']) && $s['date'] !== null ? $s['date'] : (isset($s['sale_date']) && $s['sale_date'] !== null ? $s['sale_date'] : '') ?>
                                </td>
                                <td><?= $user && isset($user['name']) && $user['name'] !== null ? htmlspecialchars((string)$user['name']) : $s['user_id'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($sales)): ?><tr>
                                <td colspan="7">No sales found.</td>
                            </tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>