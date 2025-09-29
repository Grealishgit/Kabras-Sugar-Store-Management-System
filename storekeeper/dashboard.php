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
        <!-- Add more dashboard content here -->
    </main>
</body>

</html>