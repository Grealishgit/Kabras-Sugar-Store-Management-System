<?php
require_once '../handlers/AuthHandler.php';
require_once '../handlers/PaymentsHandler.php';
require_once '../handlers/SalesHandler.php';
require_once '../handlers/FinanceHandler.php';
require_once '../config/database.php';

// Handle logout request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && $_POST['logout'] == '1') {
    session_start();
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// Format large amounts as 10K, 100K, etc.
function formatShortAmount($amount)
{
    $amount = floatval($amount);
    if ($amount >= 1000000) {
        return number_format($amount / 1000000, 2) . 'M';
    } elseif ($amount >= 100000) {
        return number_format($amount / 1000, 0) . 'K';
    } elseif ($amount >= 10000) {
        return number_format($amount / 1000, 1) . 'K';
    } else {
        return number_format($amount, 2);
    }
}


// Handle add expense form submission (from modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $expense_date = $_POST['expense_date'] ?? date('Y-m-d');
    $vendor = $_POST['vendor'] ?? '';
    $category = $_POST['category'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $recorded_by = isset($currentUser['id']) ? $currentUser['id'] : null;
    if ($recorded_by && is_numeric($recorded_by)) {
        $financeHandler = new FinanceHandler();
        if ($financeHandler->addExpense($expense_date, $vendor, $category, $amount, $recorded_by)) {
            header('Location: finance.php');
            exit();
        }
    } else {
        // Optionally log or show error: user ID missing or invalid
    }
}

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

// DB connection
$db = (new Database())->connect();
$salesHandler = new SalesHandler();
$paymentsHandler = new PaymentsHandler();

// Handle add expense form submission (from modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $expense_date = $_POST['expense_date'] ?? date('Y-m-d');
    $vendor = $_POST['vendor'] ?? '';
    $category = $_POST['category'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $recorded_by = isset($currentUser['id']) ? $currentUser['id'] : 1;
    if ($amount > 0 && $category) {
        $stmt = $db->prepare("INSERT INTO expenses (date, vendor, category, amount, recorded_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$expense_date, $vendor, $category, $amount, $recorded_by]);
        header('Location: finance.php');
        exit();
    }
}
// $userHandler = new UserHandler();

// Revenue calculations from payments
$totalPaymentsRevenue = 0;
$todayPaymentsRevenue = 0;
$weeklyPaymentsRevenue = 0;
$monthlyPaymentsRevenue = 0;
$payments = $paymentsHandler->getAllPayments();
foreach ($payments as $p) {
    $amt = isset($p['amount']) ? $p['amount'] : 0;
    $totalPaymentsRevenue += $amt;
    $payDate = isset($p['date']) ? $p['date'] : (isset($p['payment_date']) ? $p['payment_date'] : null);
    if ($payDate) {
        if (date('Y-m-d', strtotime($payDate)) == date('Y-m-d')) {
            $todayPaymentsRevenue += $amt;
        }
        if (strtotime($payDate) >= strtotime('-6 days')) {
            $weeklyPaymentsRevenue += $amt;
        }
        if (date('Y-m', strtotime($payDate)) == date('Y-m')) {
            $monthlyPaymentsRevenue += $amt;
        }
    }
}

// Revenue calculations from sales
$totalSalesRevenue = 0;
$todaySalesRevenue = 0;
$weeklySalesRevenue = 0;
$monthlySalesRevenue = 0;
$sales = $salesHandler->getAllSales();
foreach ($sales as $s) {
    $amt = isset($s['total_amount']) ? $s['total_amount'] : 0;
    $totalSalesRevenue += $amt;
    $saleDate = isset($s['date']) ? $s['date'] : (isset($s['sale_date']) ? $s['sale_date'] : null);
    if ($saleDate) {
        if (date('Y-m-d', strtotime($saleDate)) == date('Y-m-d')) {
            $todaySalesRevenue += $amt;
        }
        if (strtotime($saleDate) >= strtotime('-6 days')) {
            $weeklySalesRevenue += $amt;
        }
        if (date('Y-m', strtotime($saleDate)) == date('Y-m')) {
            $monthlySalesRevenue += $amt;
        }
    }
}

// Expenses calculations
$totalExpenses = 0;
$monthlyExpenses = 0;
$weeklyExpenses = 0;
$todayExpenses = 0;
$expenses = [];
$expenseQuery = $db->query("SELECT e.*, u.name as recorded_by_name FROM expenses e JOIN users u ON e.recorded_by = u.id
ORDER BY e.date DESC, e.id DESC LIMIT 10");

while ($row = $expenseQuery->fetch(PDO::FETCH_ASSOC)) {
    $expenses[] = $row;
    $amt = $row['amount'];
    $totalExpenses += $amt;

    if (date('Y-m-d', strtotime($row['date'])) == date('Y-m-d')) {
        $todayExpenses += $amt;
    }

    if (strtotime($row['date']) >= strtotime('-6 days')) {
        $weeklyExpenses += $amt;
    }

    if (date('Y-m', strtotime($row['date'])) == date('Y-m')) {
        $monthlyExpenses += $amt;
    }
}

$totalRevenue = $totalPaymentsRevenue + $totalSalesRevenue;
$netProfit = $totalRevenue - $totalExpenses;

// Recent payments
$recentPayments = [];
$payments = $paymentsHandler->getAllPayments();

usort(
    $payments,
    function ($a, $b) {
        return strtotime($b['date'] ?? $b['payment_date']) - strtotime($a['date'] ?? $a['payment_date']);
    }

);
$payments = array_slice($payments, 0, 10);

foreach ($payments as $p) {
    // Remove userHandler lookup; just use user_id or cashier_name if available
    $p['recorded_by_name'] = isset($p['cashier_name']) ? $p['cashier_name'] : $p['user_id'];
    $recentPayments[] = $p;
}


// CSV export for recent payments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_payments_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="recent_payments.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Customer', 'Amount', 'Payment Method', 'Recorded By'], ',', '"');
    foreach ($recentPayments as $p) {
        fputcsv($output, [
            $p['date'] ?? $p['payment_date'],
            $p['customer'] ?? 'N/A',
            $p['amount'] ?? 0,
            $p['method'] ?? 'N/A',
            $p['recorded_by_name'] ?? ''
        ], ',', '"');
    }
    fclose($output);
    exit();
}

// CSV export for recent expenses
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_expenses_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="recent_expenses.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date', 'Vendor/Supplier', 'Amount', 'Category', 'Recorded By'], ',', '"');
    foreach ($expenses as $e) {
        fputcsv($output, [
            $e['date'],
            $e['vendor'],
            $e['amount'],
            $e['category'],
            $e['recorded_by_name']
        ], ',', '"');
    }
    fclose($output);
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accountant Dashboard | Finance</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/finance.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <h1 class="finance-title">Finance Overview</h1>
        <div class="finance-cards-row">
            <div class="finance-card">
                <h4>Total Payments Revenue</h4>
                <div class="card-value">Ksh <?= formatShortAmount($totalPaymentsRevenue) ?></div>
            </div>
            <div class="finance-card">
                <h4>Today's Payments Revenue</h4>
                <div class="card-value">Ksh <?= formatShortAmount($todayPaymentsRevenue) ?></div>
            </div>
            <div class="finance-card">
                <h4>Weekly Payments Revenue</h4>
                <div class="card-value">Ksh <?= formatShortAmount($weeklyPaymentsRevenue) ?></div>
            </div>
            <div class="finance-card">
                <h4>Monthly Payments Revenue</h4>
                <div class="card-value">Ksh <?= formatShortAmount($monthlyPaymentsRevenue) ?></div>
            </div>
            <div class="finance-card">
                <h4>Total Sales Revenue</h4>
                <div class="card-value">Ksh <?= formatShortAmount($totalSalesRevenue) ?></div>
            </div>
            <div class="finance-card">
                <h4>Today's Sales Revenue</h4>
                <div class="card-value">Ksh <?= formatShortAmount($todaySalesRevenue) ?></div>
            </div>
            <div class="finance-card">
                <h4>Weekly Sales Revenue</h4>
                <div class="card-value">Ksh <?= formatShortAmount($weeklySalesRevenue) ?></div>
            </div>
            <div class="finance-card">
                <h4>Monthly Sales Revenue</h4>
                <div class="card-value">Ksh <?= formatShortAmount($monthlySalesRevenue) ?></div>
            </div>
            <div class="finance-card">
                <h4>Total Expenses</h4>
                <div class="card-value">Ksh <?= formatShortAmount($totalExpenses) ?></div>
            </div>
            <div class="finance-card">
                <h4 class="net-profit-title">Net Profit/Loss</h4>
                <div class="card-value net-profit <?= $netProfit >= 0 ? 'profit' : 'loss' ?>">
                    Ksh <?= formatShortAmount($netProfit) ?>
                </div>
                <div class="card-value net-profit-percentage"
                    style="font-size:1.1rem; font-weight:600; color:<?= $netProfit >= 0 ? '#43a047' : '#e53935' ?>;">
                    <?= $totalRevenue > 0 ? ($netProfit >= 0 ? '+' : '') . round(($netProfit / $totalRevenue) * 100, 2) . '%' : '0%' ?>
                </div>



            </div>
        </div>
        <!-- Add Expense Button and Modal -->
        <div class="add-expense-container">
            <button id="openExpenseModalBtn" class="btn">Add Expense</button>



        </div>
        <div id="addExpenseModal" class="modal">
            <div class="modal-content">
                <span id="closeExpenseModalBtn">&times;</span>
                <h3 class="finance-table-title">Add Expense</h3>
                <form method="post" class="add-expense-form">
                    <input type="hidden" name="add_expense" value="1">
                    <div class="form-row">
                        <label for="expense_date">Date:</label>
                        <input type="date" name="expense_date" id="expense_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-row">
                        <label for="vendor">Vendor/Supplier:</label>
                        <input type="text" name="vendor" id="vendor" required>
                    </div>
                    <div class="form-row">
                        <label for="category">Category:</label>
                        <input type="text" name="category" id="category" required>
                    </div>
                    <div class="form-row">
                        <label for="amount">Amount:</label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0" required>
                    </div>
                    <div class="form-row">
                        <button type="submit" class="btn btn-primary">Add Expense</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="finance-tables-row">
            <div class="finance-table-card">
                <form method="post" style="display:inline; float:right; margin-left:8px;">
                    <input type="hidden" name="export_payments_csv" value="1">
                    <button type="submit" class="btn btn-export-payments">Export Payments CSV</button>
                </form>
                <h3 class="finance-table-title">Recent Payments</h3>
                <table class="finance-table payments-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPayments as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['date'] ?? $p['payment_date']) ?></td>
                            <td><?= htmlspecialchars($p['customer'] ?? 'N/A') ?></td>
                            <td>Ksh <?= number_format($p['amount'] ?? 0, 2) ?></td>
                            <td><?= htmlspecialchars($p['method'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($p['recorded_by_name']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentPayments)): ?><tr>
                            <td colspan="5">No payments found.</td>
                        </tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="finance-table-card">
                <form method="post" style="display:inline; float:right; margin-left:8px;">
                    <input type="hidden" name="export_expenses_csv" value="1">
                    <button type="submit" class="btn btn-export-expenses">Export Expenses CSV</button>
                </form>
                <h3 class="finance-table-title">Recent Expenses / Purchases</h3>

                <table class="finance-table expenses-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vendor/Supplier</th>
                            <th>Amount</th>
                            <th>Category</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['date']) ?></td>
                            <td><?= htmlspecialchars($e['vendor']) ?></td>
                            <td>Ksh <?= number_format($e['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($e['category']) ?></td>
                            <td><?= htmlspecialchars($e['recorded_by_name']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($expenses)): ?><tr>
                            <td colspan="5">No expenses found.</td>
                        </tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
    // Modal open/close logic
    document.getElementById('openExpenseModalBtn').onclick = function() {
        document.getElementById('addExpenseModal').style.display = 'flex';
    };
    document.getElementById('closeExpenseModalBtn').onclick = function() {
        document.getElementById('addExpenseModal').style.display = 'none';
    };
    window.onclick = function(event) {
        var modal = document.getElementById('addExpenseModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };
    </script>
</body>

</html>