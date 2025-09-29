<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && $_POST['logout'] == '1') {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}


require_once '../handlers/AuthHandler.php';
require_once '../handlers/ProductHandler.php';



$authHandler = new AuthHandler();
$productHandler = new ProductHandler();


// Ensure user is logged in
if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in first.');
    exit();
}

$currentUser = $authHandler->getCurrentUser();

// Restrict only Cashier
if ($currentUser['role'] !== 'StoreKeeper') {
    header('Location: ../login.php?error=Access denied. Cashier privileges required.');
    exit();
}
// Fetch products with stock info
$products = $productHandler->getProductsStock(10); // low stock threshold = 10
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Level | StoreKeeper</title>
    <link rel="stylesheet" href="../assets/css/stock_level.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">

        <h1>Stock Levels</h1>
        <p class="subtitle">Monitor current stock and low-stock alerts</p>

        <?php
        // Stat card calculations
        $lowUnitCount = 0;
        $lowQuantityCount = 0;
        $inactiveCount = 0;
        foreach ($products as $product) {
            if (is_numeric($product['unit']) && $product['unit'] < 5) {
                $lowUnitCount++;
            }
            if (is_numeric($product['stock_quantity']) && $product['stock_quantity'] < 10) {
                $lowQuantityCount++;
            }
            if (isset($product['status']) && strtolower($product['status']) === 'inactive') {
                $inactiveCount++;
            }
        }
        ?>
        <div class="stats-container">
            <div class="stat-card low-unit stat-card-with-icon">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h2><?php echo $lowUnitCount; ?></h2>
                    <p>Products with Unit &lt; 5</p>
                </div>
            </div>

            <div class="stat-card low-quantity stat-card-with-icon">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-content">
                    <h2><?php echo $lowQuantityCount; ?></h2>
                    <p>Products with Quantity &lt; 10</p>
                </div>
            </div>

            <div class="stat-card inactive-stock stat-card-with-icon">
                <div class="stat-icon">
                    <i class="fas fa-pause-circle"></i>
                </div>
                <div class="stat-content">
                    <h2><?php echo $inactiveCount; ?></h2>
                    <p>Inactive Stock</p>
                </div>
            </div>
        </div>

        <!-- Filter/Search Form -->
        <form method="get" class="filter-form"
            style="margin-bottom:24px; display:flex; gap:16px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="name" placeholder="Product Name"
                value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>" />
            <input type="text" name="category" placeholder="Category"
                value="<?php echo isset($_GET['category']) ? htmlspecialchars($_GET['category']) : ''; ?>" />
            <select name="status">
                <option value="">All Status</option>
                <option value="active"
                    <?php if (isset($_GET['status']) && $_GET['status'] === 'active') echo 'selected'; ?>>Active
                </option>
                <option value="inactive"
                    <?php if (isset($_GET['status']) && $_GET['status'] === 'inactive') echo 'selected'; ?>>Inactive
                </option>
            </select>
            <select name="low_stock">
                <option value="">Low Stock?</option>
                <option value="yes"
                    <?php if (isset($_GET['low_stock']) && $_GET['low_stock'] === 'yes') echo 'selected'; ?>>Yes
                </option>
                <option value="no"
                    <?php if (isset($_GET['low_stock']) && $_GET['low_stock'] === 'no') echo 'selected'; ?>>
                    No</option>
            </select>
            <button type="submit" class="btn">Filter</button>
            <a href="stock_levels.php" class="btn" style="background:#eee; color:#333;">Reset</a>
        </form>

        <?php
        // Filter logic
        $filteredProducts = $products;
        if (!empty($_GET['name'])) {
            $filteredProducts = array_filter($filteredProducts, function ($p) {
                return stripos($p['name'], $_GET['name']) !== false;
            });
        }
        if (!empty($_GET['category'])) {
            $filteredProducts = array_filter($filteredProducts, function ($p) {
                return stripos($p['category'], $_GET['category']) !== false;
            });
        }
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $filteredProducts = array_filter($filteredProducts, function ($p) {
                return strtolower($p['status']) === strtolower($_GET['status']);
            });
        }
        if (isset($_GET['low_stock']) && $_GET['low_stock'] !== '') {
            $filteredProducts = array_filter($filteredProducts, function ($p) {
                return ($_GET['low_stock'] === 'yes') ? $p['low_stock'] : !$p['low_stock'];
            });
        }
        ?>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Stock Quantity</th>
                    <th>Unit</th>
                    <th>Low Stock</th>
                    <th>Expiry Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filteredProducts as $product): ?>
                    <tr class="<?php echo $product['low_stock'] ? 'low-stock' : ''; ?>">
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td><?php echo $product['stock_quantity']; ?></td>
                        <td><?php echo htmlspecialchars($product['unit']); ?></td>
                        <td><?php echo $product['low_stock'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $product['expiry_date'] ? date('Y-m-d', strtotime($product['expiry_date'])) : ''; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>

</html>