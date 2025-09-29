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

// Restrict only StoreKeeper
if ($currentUser['role'] !== 'StoreKeeper') {
    header('Location: ../login.php?error=Access denied. StoreKeeper privileges required.');
    exit();
}

// Get product data
$products = $productHandler->getAllProducts();

$inactive = [];
$expired = [];
$lowStock = [];
$lowPrice = [];
$today = date('Y-m-d');
foreach ($products as $product) {
    if (isset($product['status']) && strtolower($product['status']) === 'inactive') {
        $inactive[] = $product;
    }
    if (!empty($product['expiry_date']) && strtotime($product['expiry_date']) < strtotime($today)) {
        $expired[] = $product;
    }
    if (is_numeric($product['stock_quantity']) && $product['stock_quantity'] < 10) {
        $lowStock[] = $product;
    }
    if (is_numeric($product['price']) && $product['price'] < 100) {
        $lowPrice[] = $product;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/stock_alerts.css">
    <title>StoreKeeper | Stock Alerts</title>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">

        <h1>Stock Alerts</h1>
        <p class="subtitle">Notifications for low-stock and expired products</p>


        <div class="alerts-container">
            <div class="alert-card inactive-stock">
                <h2 class="inactive-title"><i class="fas fa-ban"></i> Inactive Stock</h2>
                <p>You have <strong><?php echo count($inactive); ?></strong> inactive products.</p>
                <ul>
                    <?php foreach ($inactive as $p): ?>
                        <li><?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['category']); ?>) -
                            <?php echo $p['stock_quantity']; ?> units</li>
                    <?php endforeach; ?>
                    <?php if (empty($inactive)): ?><li>None</li><?php endif; ?>
                </ul>
            </div>
            <div class="alert-card expired-stock">
                <h2 class="expired-title"><i class="fas fa-exclamation-circle"></i> Expired Stock</h2>
                <p>You have <strong><?php echo count($expired); ?></strong> expired products.</p>
                <ul>
                    <?php foreach ($expired as $p): ?>
                        <li><?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['category']); ?>) -
                            Expired on <?php echo date('Y-m-d', strtotime($p['expiry_date'])); ?></li>
                    <?php endforeach; ?>
                    <?php if (empty($expired)): ?><li>None</li><?php endif; ?>
                </ul>
            </div>
            <div class="alert-card low-stock">
                <h2 class="low-title"><i class="fas fa-exclamation-triangle"></i> Low Stock</h2>
                <p>You have <strong><?php echo count($lowStock); ?></strong> products low in stock (&lt; 10 units).</p>
                <ul>
                    <?php foreach ($lowStock as $p): ?>
                        <li><?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['category']); ?>) -
                            <?php echo $p['stock_quantity']; ?> units left</li>
                    <?php endforeach; ?>
                    <?php if (empty($lowStock)): ?><li>None</li><?php endif; ?>
                </ul>
            </div>
            <div class="alert-card low-price">
                <h2 class="price-title"><i class="fas fa-money-bill-wave"></i> Low Price Stock</h2>
                <p>You have <strong><?php echo count($lowPrice); ?></strong> products with price &lt; 100.</p>
                <ul>
                    <?php foreach ($lowPrice as $p): ?>
                        <li><?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['category']); ?>) -
                            <?php echo number_format($p['price'], 2); ?> per unit</li>
                    <?php endforeach; ?>
                    <?php if (empty($lowPrice)): ?><li>None</li><?php endif; ?>
                </ul>
            </div>
        </div>

    </main>

</body>

</html>