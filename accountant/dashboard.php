<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && $_POST['logout'] == '1') {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

require_once '../handlers/AuthHandler.php';
require_once '../handlers/SalesHandler.php';
require_once '../handlers/FinanceHandler.php';
require_once '../handlers/ComplianceHandler.php';

// Auth
$authHandler = new AuthHandler();
if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in first.');
    exit();
}
$currentUser = $authHandler->getCurrentUser();
if ($currentUser['role'] !== 'Accountant') {
    header('Location: ../login.php?error=Access denied. Cashier privileges required.');
    exit();
}

// Stats Handlers
$salesHandler = new SalesHandler();
$financeHandler = new FinanceHandler();
$complianceHandler = new ComplianceHandler();

// Financial stats
$totalRevenue = $salesHandler->getTotalSales();
$totalExpenses = 0;
try {
    $db = (new Database())->connect();
    $stmt = $db->query("SELECT SUM(amount) AS total_expenses FROM expenses");
    $row = $stmt->fetch();
    $totalExpenses = $row['total_expenses'] ?? 0;
} catch (Exception $e) {
}
$netProfit = $totalRevenue - $totalExpenses;

// Compliance stats
$auditStats = $complianceHandler->getAuditStats();
$violationStats = $complianceHandler->getViolationStats();
$totalAudits = $auditStats['total'] ?? 0;
$totalViolations = $violationStats['total'] ?? 0;

// Violation categories for pie chart
$violationCategories = [];
try {
    $stmt = $db->query("SELECT category, COUNT(*) as count FROM compliance_violations GROUP BY category");
    while ($row = $stmt->fetch()) {
        $violationCategories[$row['category']] = $row['count'];
    }
} catch (Exception $e) {
}

// Audit types for pie chart
$auditTypes = [];
try {
    $stmt = $db->query("SELECT audit_type, COUNT(*) as count FROM compliance_audits GROUP BY audit_type");
    while ($row = $stmt->fetch()) {
        $auditTypes[$row['audit_type']] = $row['count'];
    }
} catch (Exception $e) {
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accountant Dashboard</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/accountant-dashboard.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <h2>Accountant Dashboard</h2>
        <div class="dashboard-cards">
            <div class="card profit-card <?= $netProfit >= 0 ? 'profit' : 'loss' ?>">
                <div class="card-title">
                    Net <?= $netProfit >= 0 ? 'Profit' : 'Loss' ?>
                    <?php
                    $percent = ($totalRevenue > 0) ? ($netProfit / $totalRevenue * 100) : 0;
                    ?>
                    <span class="percent-badge" title="Percent of Revenue">
                        <?= ($totalRevenue > 0) ? number_format($percent, 1) . '%' : '0%' ?>
                    </span>
                </div>
                <div class="card-value"><?= number_format($netProfit, 2) ?></div>
            </div>
            <div class="card">
                <div class="card-title">Total Revenue</div>
                <div class="card-value"><?= number_format($totalRevenue, 2) ?></div>
            </div>
            <div class="card">
                <div class="card-title">Total Expenses</div>
                <div class="card-value"><?= number_format($totalExpenses, 2) ?></div>
            </div>
            <div class="card">
                <div class="card-title">Total Audits</div>
                <div class="card-value"><?= $totalAudits ?></div>
            </div>
            <div class="card">
                <div class="card-title">Total Violations</div>
                <div class="card-value"><?= $totalViolations ?></div>
            </div>
        </div>
        <div class="dashboard-charts">
            <div class="chart-container">
                <h3>Violation Categories</h3>
                <canvas id="violationPie"></canvas>
            </div>
            <div class="chart-container">
                <h3>Audit & Inspection Types</h3>
                <canvas id="auditPie"></canvas>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Violation Pie
        const violationData = {
            labels: <?= json_encode(array_keys($violationCategories)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($violationCategories)) ?>,
                backgroundColor: ['#f44336', '#ff9800', '#2196f3', '#4caf50', '#9c27b0', '#ffc107']
            }]
        };
        new Chart(document.getElementById('violationPie'), {
            type: 'pie',
            data: violationData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        // Audit Pie
        const auditData = {
            labels: <?= json_encode(array_keys($auditTypes)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($auditTypes)) ?>,
                backgroundColor: ['#2196f3', '#4caf50', '#ff9800', '#9c27b0']
            }]
        };
        new Chart(document.getElementById('auditPie'), {
            type: 'pie',
            data: auditData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html>