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
if ($currentUser['role'] !== 'StoreKeeper') {
    header('Location: ../login.php?error=Access denied. Cashier privileges required.');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/storekeeper-dashboard.css">
    <title>Storekeeper Dashboard</title>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <?php
    require_once '../handlers/ProductHandler.php';
    $productHandler = new ProductHandler();
    $products = $productHandler->getAllProducts();
    $totalProducts = count($products);
    $categories = array_unique(array_map(function ($p) {
        return $p['category'];
    }, $products));
    $totalCategories = count($categories);
    $activeProducts = array_filter($products, function ($p) {
        return strtolower($p['status']) === 'active';
    });
    $totalActive = count($activeProducts);
    $suppliers = array_unique(array_map(function ($p) {
        return $p['supplier'];
    }, $products));
    $totalSuppliers = count($suppliers);
    $avgStock = $totalProducts ? round(array_sum(array_column($products, 'stock_quantity')) / $totalProducts, 2) : 0;
    $avgUnit = $totalProducts ? round(array_sum(array_map(function ($p) {
        return is_numeric($p['unit']) ? $p['unit'] : 0;
    }, $products)) / $totalProducts, 2) : 0;
    ?>
    <main class="main-content">
        <h1>Storekeeper Dashboard</h1>
        <div class="stats-container" style="display:flex; gap:24px; flex-wrap:wrap; margin-bottom:32px;">
            <div class="stat-card">
                <h2><?php echo $totalProducts; ?></h2>
                <p>Total Products</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $totalCategories; ?></h2>
                <p>Total Categories</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $totalActive; ?></h2>
                <p>Active Products</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $totalSuppliers; ?></h2>
                <p>Total Suppliers</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $avgStock; ?></h2>
                <p>Average Stock Quantity</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $avgUnit; ?></h2>
                <p>Average Unit</p>
            </div>
        </div>

        <!-- Recent Added Products Table -->
        <div class="dashboard-table recent-products" style="flex:1; margin-bottom: 20px; min-width:340px;">
            <h3>Recently Added Products</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Supplier</th>
                        <th>Stock Qty</th>
                        <th>Added On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($products, 0, 10) as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['category']) ?></td>
                            <td><?= htmlspecialchars($p['supplier']) ?></td>
                            <td><?= $p['stock_quantity'] ?></td>
                            <td><?= isset($p['created_at']) ? date('Y-m-d', strtotime($p['created_at'])) : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?><tr>
                            <td colspan="5">No products found.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Stock Level Table -->
        <div class="dashboard-table stock-levels" style="flex:1; min-width:340px;">
            <h3 style="margin-bottom:12px;">Stock Levels</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Stock Qty</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($products, 0, 10) as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['category']) ?></td>
                            <td><?= $p['stock_quantity'] ?></td>
                            <td><?= htmlspecialchars($p['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?><tr>
                            <td colspan="4">No products found.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</body>

</html>