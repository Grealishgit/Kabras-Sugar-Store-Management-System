<?php session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && $_POST['logout'] == '1') {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

require_once '../handlers/AuthHandler.php';


require_once '../handlers/ProductHandler.php';
require_once '../handlers/UserHandler.php';
require_once '../handlers/PaymentsHandler.php';
require_once '../handlers/SalesHandler.php';

$authHandler = new AuthHandler();

if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in first.');
    exit();
}

$currentUser = $authHandler->getCurrentUser();

if ($currentUser['role'] !== 'Manager') {
    header('Location: ../login.php?error=Access denied. Cashier privileges required.');
    exit();
}

// Fetch real data
$productHandler = new ProductHandler();
$userHandler = new UserHandler();
$paymentsHandler = new PaymentsHandler();
$salesHandler = new SalesHandler();

$products = $productHandler->getAllProducts();
$users = $userHandler->getAllUsers();
$payments = $paymentsHandler->getAllPayments();
$sales = $salesHandler->getAllSales();

$productsCount = count($products);
$staffCount = count($users);
$paymentsTotal = 0;

foreach ($payments as $p) {
    $paymentsTotal += isset($p['amount']) ? $p['amount'] : 0;
}

$salesTotal = 0;

foreach ($sales as $s) {
    $salesTotal += isset($s['total_amount']) ? $s['total_amount'] : 0;
}

$managersCount = count(array_filter(
    $users,
    function ($u) {
        return strtolower($u['role']) === 'manager';
    }

));

// Product category distribution
$categoryCounts = [];

foreach ($products as $p) {
    $cat = $p['category'] ?? 'Other';
    $categoryCounts[$cat] = ($categoryCounts[$cat] ?? 0) + 1;
}

$categoryLabels = array_keys($categoryCounts);
$categoryData = array_values($categoryCounts);

// Payment method distribution
$paymentMethodCounts = [];

foreach ($payments as $p) {
    $method = $p['method'] ?? 'Other';
    $paymentMethodCounts[$method] = ($paymentMethodCounts[$method] ?? 0) + 1;
}

$paymentMethodLabels = array_keys($paymentMethodCounts);
$paymentMethodData = array_values($paymentMethodCounts);

// Role distribution for progress bar
$roleCounts = array_count_values(array_map(
    function ($u) {
        return $u['role'];
    },
    $users
));
$roleTotal = array_sum($roleCounts);

// Sales trend for last 7 days
$salesByDay = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $salesByDay[$date] = 0;
}

foreach ($sales as $s) {
    $saleDate = isset($s['date']) ? date('Y-m-d', strtotime($s['date'])) : (isset($s['sale_date']) ? date('Y-m-d', strtotime($s['sale_date'])) : null);

    if ($saleDate && isset($salesByDay[$saleDate])) {
        $salesByDay[$saleDate] += isset($s['total_amount']) ? $s['total_amount'] : 0;
    }
}

$salesDayLabels = [];
$salesDayData = [];

foreach ($salesByDay as $date => $amount) {
    $salesDayLabels[] = date('D', strtotime($date));
    $salesDayData[] = $amount;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/manager-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body><?php include '../includes/sidebar.php';
        ?><div class="main-content">
        <h1 style="margin-bottom:18px;">Manager Dashboard</h1>
        <div class="dashboard-cards-row">
            <div class="dashboard-card">
                <h4><i class="fa-solid fa-box"></i>Products</h4>
                <div class="card-value" id="productsCount">...</div>
            </div>
            <div class="dashboard-card">
                <h4><i class="fa-solid fa-users"></i>Staff</h4>
                <div class="card-value" id="staffCount">...</div>
            </div>
            <div class="dashboard-card">
                <h4><i class="fa-solid fa-money-bill-wave"></i>Payments</h4>
                <div class="card-value" id="paymentsTotal">...</div>
            </div>
            <div class="dashboard-card">
                <h4><i class="fa-solid fa-chart-line"></i>Sales</h4>
                <div class="card-value" id="salesTotal">...</div>
            </div>

        </div>
        <div class="dashboard-row-visuals">
            <div class="dashboard-visual-card">
                <h3><i class="fa-solid fa-chart-pie"></i> Product Category Distribution</h3><canvas
                    id="categoryPieChart" height="120"></canvas>
            </div>
            <div class="dashboard-visual-card">
                <h3><i class="fa-solid fa-chart-bar"></i> Sales Trend (Last 7 Days)</h3><canvas id="salesLineChart"
                    height="180"></canvas>
            </div>
            <div class="dashboard-visual-pair">
                <div class="dashboard-visual-half">
                    <h3><i class="fa-solid fa-doughnut"></i>Payment Methods</h3><canvas id="paymentDoughnutChart"
                        height="150"></canvas>
                </div>
                <div class="dashboard-visual-half">
                    <h3><i class="fa-solid fa-users"></i>Role Distribution</h3>

                    <div class="role-distribution-bars"><?php foreach ($roleCounts as $role => $count): ?><div
                                class="role-bar-row"><span class="role-bar-label"><?= htmlspecialchars($role) ?></span>
                                <div class="role-bar-bg">
                                    <div class="role-bar-fill"
                                        style="width:<?= $roleTotal > 0 ? round($count / $roleTotal * 100, 2) : 0 ?>%;">
                                    </div>
                                </div><span class="role-bar-count"><?= $count ?></span>
                            </div><?php endforeach; ?>
                    </div>
                    <div class="manager-main">
                        <div class="manager-card">
                            <h4><i class="fa-solid fa-user-tie"></i> Managers</h4>
                            <div class="card-values" id="managersCount">...</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Set real data for cards
        document.getElementById('productsCount').textContent = <?= json_encode($productsCount) ?>;
        document.getElementById('staffCount').textContent = <?= json_encode($staffCount) ?>;
        document.getElementById('paymentsTotal').textContent = 'Ksh ' + Number(<?= json_encode($paymentsTotal) ?>)
            .toLocaleString();
        document.getElementById('salesTotal').textContent = 'Ksh ' + Number(<?= json_encode($salesTotal) ?>)
            .toLocaleString();
        document.getElementById('managersCount').textContent = <?= json_encode($managersCount) ?>;

        // Product Category Pie Chart
        new Chart(document.getElementById('categoryPieChart'), {

                type: 'pie',
                data: {

                    labels: <?= json_encode($categoryLabels) ?>,
                    datasets: [{
                            data: <?= json_encode($categoryData) ?>,
                            backgroundColor: ['#1976d2', '#43a047', '#ffa726', '#e53935', '#8e24aa', '#00bcd4',
                                '#fbc02d', '#c62828'
                            ]
                        }

                    ]
                }

                ,
                options: {

                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            }

        );

        // Sales Trend Chart (Last 7 Days)
        new Chart(document.getElementById('salesLineChart'), {

                type: 'line',
                data: {

                    labels: <?= json_encode($salesDayLabels) ?>,
                    datasets: [{
                            label: 'Sales (Ksh)',
                            data: <?= json_encode($salesDayData) ?>,
                            borderColor: '#1976d2',
                            backgroundColor: 'rgba(25,118,210,0.1)',
                            fill: true,
                            tension: 0.3
                        }

                    ]
                }

                ,
                options: {

                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            }

        );

        // Payment Methods Doughnut Chart
        new Chart(document.getElementById('paymentDoughnutChart'), {

                type: 'doughnut',
                data: {

                    labels: <?= json_encode($paymentMethodLabels) ?>,
                    datasets: [{
                            data: <?= json_encode($paymentMethodData) ?>,
                            backgroundColor: ['#43a047', '#ffa726', '#1976d2', '#e53935', '#8e24aa', '#00bcd4',
                                '#fbc02d', '#c62828'
                            ]
                        }

                    ]
                }

                ,
                options: {

                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            }

        );
    </script>
</body>

</html>