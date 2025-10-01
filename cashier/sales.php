<?php
session_start();
require_once '../handlers/AuthHandler.php';
require_once '../handlers/SalesHandler.php';
require_once '../handlers/CustomerHandler.php';

$authHandler = new AuthHandler();
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

// Get sales statistics
$todaySales = $salesHandler->getTodaySales($currentUser['id']);
$weeklySales = $salesHandler->getWeeklySales($currentUser['id']);
$monthlySales = $salesHandler->getMonthlySales($currentUser['id']);
$recentSales = $salesHandler->getRecentSales($currentUser['id'], 10);

// Handle sale submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_sale'])) {
    $items = $_POST['items'] ?? [];
    $customerId = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : 1;

    // Only keep items with quantity > 0
    $filteredItems = [];
    foreach ($items as $productId => $qty) {
        $qty = intval($qty);
        if ($qty > 0) {
            $filteredItems[$productId] = $qty;
        }
    }

    if (!empty($filteredItems)) {
        $saleResult = $salesHandler->processSale($customerId, $currentUser['id'], $filteredItems);
        if ($saleResult) {
            header("Location: sales.php?success=Sale processed successfully.");
        } else {
            header("Location: sales.php?error=Failed to process sale. Please try again.");
        }
    } else {
        header("Location: sales.php?error=No items selected for sale.");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Sales | Kabras Sugar Store</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/cashier-sale.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <!-- Header Section -->
        <div class="page-header">
            <div class="header-left">
                <h1><i class="fas fa-cash-register"></i> Point of Sale</h1>
                <p>Process sales transactions and view sales statistics</p>
            </div>
            <div class="header-right">
                <div class="current-user">
                    <i class="fas fa-user-circle"></i>
                    <span><?= htmlspecialchars($currentUser['name']); ?></span>
                </div>
                <div class="current-time" id="currentTime"></div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_GET['success']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($_GET['error']); ?>
        </div>
        <?php endif; ?>

        <!-- Sales Statistics Dashboard -->
        <div class="stats-dashboard">
            <h2><i class="fas fa-chart-line"></i> Sales Statistics</h2>

            <!-- Time Period Tabs -->
            <div class="stats-tabs">
                <button class="tab-btn active" data-period="today">Today</button>
                <button class="tab-btn" data-period="week">This Week</button>
                <button class="tab-btn" data-period="month">This Month</button>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <!-- Today's Stats -->
                <div class="stats-period" id="today-stats">
                    <div class="stat-card total-sales">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $todaySales['total_transactions'] ?? 0; ?></h3>
                            <p>Total Sales</p>
                            <span class="stat-label">Today</span>
                        </div>
                    </div>

                    <div class="stat-card total-quantity">
                        <div class="stat-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $todaySales['total_quantity'] ?? 0; ?></h3>
                            <p>Items Sold</p>
                            <span class="stat-label">Today</span>
                        </div>
                    </div>

                    <div class="stat-card avg-price">
                        <div class="stat-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Ksh <?= number_format($todaySales['avg_unit_price'] ?? 0, 2); ?></h3>
                            <p>Avg Unit Price</p>
                            <span class="stat-label">Today</span>
                        </div>
                    </div>

                    <div class="stat-card total-amount">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Ksh <?= number_format($todaySales['total_amount'] ?? 0, 2); ?></h3>
                            <p>Total Revenue</p>
                            <span class="stat-label">Today</span>
                        </div>
                    </div>
                </div>

                <!-- Weekly Stats -->
                <div class="stats-period hidden" id="week-stats">
                    <div class="stat-card total-sales">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $weeklySales['total_transactions'] ?? 0; ?></h3>
                            <p>Total Sales</p>
                            <span class="stat-label">This Week</span>
                        </div>
                    </div>

                    <div class="stat-card total-quantity">
                        <div class="stat-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $weeklySales['total_quantity'] ?? 0; ?></h3>
                            <p>Items Sold</p>
                            <span class="stat-label">This Week</span>
                        </div>
                    </div>

                    <div class="stat-card avg-price">
                        <div class="stat-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Ksh <?= number_format($weeklySales['avg_unit_price'] ?? 0, 2); ?></h3>
                            <p>Avg Unit Price</p>
                            <span class="stat-label">This Week</span>
                        </div>
                    </div>

                    <div class="stat-card total-amount">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Ksh <?= number_format($weeklySales['total_amount'] ?? 0, 2); ?></h3>
                            <p>Total Revenue</p>
                            <span class="stat-label">This Week</span>
                        </div>
                    </div>
                </div>

                <!-- Monthly Stats -->
                <div class="stats-period hidden" id="month-stats">
                    <div class="stat-card total-sales">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $monthlySales['total_transactions'] ?? 0; ?></h3>
                            <p>Total Sales</p>
                            <span class="stat-label">This Month</span>
                        </div>
                    </div>

                    <div class="stat-card total-quantity">
                        <div class="stat-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $monthlySales['total_quantity'] ?? 0; ?></h3>
                            <p>Items Sold</p>
                            <span class="stat-label">This Month</span>
                        </div>
                    </div>

                    <div class="stat-card avg-price">
                        <div class="stat-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Ksh <?= number_format($monthlySales['avg_unit_price'] ?? 0, 2); ?></h3>
                            <p>Avg Unit Price</p>
                            <span class="stat-label">This Month</span>
                        </div>
                    </div>

                    <div class="stat-card total-amount">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Ksh <?= number_format($monthlySales['total_amount'] ?? 0, 2); ?></h3>
                            <p>Total Revenue</p>
                            <span class="stat-label">This Month</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main sales Section -->
        <div class="sales-container">
            <div class="sales-left">
                <div class="section-header">
                    <h2><i class="fas fa-list"></i> Product Selection</h2>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="productSearch" placeholder="Search products...">
                    </div>
                </div>

                <form method="POST" action="sales.php" id="salesForm">
                    <!-- Customer Selection (Optional) -->
                    <div class="customer-section">
                        <label for="customer_id">
                            <i class="fas fa-user"></i> Customer (Optional)
                        </label>
                        <select name="customer_id" id="customer_id">
                            <option value="">Walk-in Customer</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>">
                                <?= htmlspecialchars($customer['name']) ?>
                                <?php if ($customer['phone']): ?>
                                - <?= htmlspecialchars($customer['phone']) ?>
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Products Grid -->
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                        <div class="product-card" data-product-name="<?= strtolower($product['name']); ?>">
                            <div class="product-info">
                                <h3><?= htmlspecialchars($product['name']); ?></h3>
                                <p class="product-price">Ksh <?= number_format($product['price'], 2); ?></p>
                            </div>
                            <div class="quantity-controls">
                                <button type="button" class="qty-btn minus"
                                    data-product="<?= $product['id']; ?>">-</button>
                                <input type="number" name="items[<?= $product['id']; ?>]" value="0" min="0"
                                    class="qty-input" data-price="<?= $product['price']; ?>"
                                    data-product="<?= $product['id']; ?>">
                                <button type="button" class="qty-btn plus"
                                    data-product="<?= $product['id']; ?>">+</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-actions">
                        <button type="button" id="clearAll" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear All
                        </button>
                        <button type="submit" name="process_sale" class="btn btn-primary" id="processSale">
                            <i class="fas fa-cash-register"></i> Process Sale
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sale Summary Panel -->
            <div class="sales-right">
                <div class="sale-summary">
                    <h3><i class="fas fa-receipt"></i> Sale Summary</h3>
                    <div class="summary-items" id="summaryItems">
                        <p class="empty-cart">No items selected</p>
                    </div>
                    <div class="summary-total">
                        <div class="total-line">
                            <span>Subtotal:</span>
                            <span id="subtotal">Ksh 0.00</span>
                        </div>
                        <div class="total-line final">
                            <span>Total:</span>
                            <span id="total">Ksh 0.00</span>
                        </div>
                    </div>
                </div>


            </div>
            <!-- All Sales Table with Pagination -->
            <div class="all-sales">
                <h3><i class="fas fa-table"></i> All Sales</h3>
                <div class="sales-table-wrapper">
                    <table id="salesTable" class="sales-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Sale ID</th>
                                <th>Customer ID</th>
                                <th>Cashier ID</th>
                                <th>Amount (Ksh)</th>
                                <th>Items</th>
                                <th>Unit Price</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $allSales = $salesHandler->getSalesByCashier($currentUser['id']);
                            if (!empty($allSales)):
                                $rowNum = 1;
                                foreach ($allSales as $sale): ?>
                            <tr>
                                <td><?= $rowNum++; ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($sale['sale_date'])); ?></td>
                                <td><?= $sale['id']; ?></td>
                                <td><?= $sale['customer_id'] ?? '-'; ?></td>
                                <td><?= $sale['user_id']; ?></td>
                                <td><?= number_format($sale['total_amount'], 2); ?></td>
                                <td><?= $sale['quantity']; ?></td>
                                <td><?= number_format($sale['unit_price'], 2); ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm">View</button>
                                </td>
                            </tr>
                            <?php endforeach;
                            else: ?>
                            <tr>
                                <td colspan="8" class="no-sales">No sales found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="sales-pagination">
                        <button id="salesPrev" class="btn btn-secondary">Prev</button>
                        <span id="salesPageNum">Page 1</span>
                        <button id="salesNext" class="btn btn-secondary">Next</button>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="../assets/js/sales.js"></script>
</body>

</html>