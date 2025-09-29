<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_csv'])) {
    require_once '../handlers/ProductHandler.php';
    require_once '../app/models/User.php';
    $productHandler = new ProductHandler();
    $userModel = new User();
    $products = $productHandler->getAllProducts();
    $userCache = [];
    foreach ($products as &$p) {
        $uid = $p['created_by'];
        if ($uid && !isset($userCache[$uid])) {
            $u = $userModel->findById($uid);
            $userCache[$uid] = $u ? $u['name'] : $uid;
        }
        $p['created_by_name'] = $uid ? $userCache[$uid] : '';
    }
    unset($p);
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="products.csv"');
    $output = fopen('php://output', 'w');
    $header = array_keys($products[0] ?? []);
    $header = array_map(function ($h) {
        return $h === 'created_by' ? 'created_by_name' : $h;
    }, $header);
    fputcsv($output, $header, ',', '"');
    foreach ($products as $p) {
        $row = $p;
        if (isset($row['created_by'])) {
            $row['created_by'] = $row['created_by_name'];
        }
        fputcsv($output, $row, ',', '"');
    }
    fclose($output);
    exit();
}

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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | Products</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/products.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <?php
    require_once '../handlers/ProductHandler.php';
    require_once '../app/models/User.php';
    $productHandler = new ProductHandler();
    $userModel = new User();
    $products = $productHandler->getAllProducts();
    // Map user ids to names for created_by
    $userCache = [];
    foreach ($products as &$p) {
        $uid = $p['created_by'];
        if ($uid && !isset($userCache[$uid])) {
            $u = $userModel->findById($uid);
            $userCache[$uid] = $u ? $u['name'] : $uid;
        }
        $p['created_by_name'] = $uid ? $userCache[$uid] : '';
    }
    unset($p);
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
    <?php
    // CSV download logic
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_csv'])) {
        error_reporting(E_ERROR | E_PARSE);
        ini_set('display_errors', 0);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products.csv"');
        $output = fopen('php://output', 'w');
        // All fields in table
        $header = array_keys($products[0] ?? []);
        // Replace created_by with created_by_name in header
        $header = array_map(function ($h) {
            return $h === 'created_by' ? 'created_by_name' : $h;
        }, $header);
        fputcsv($output, $header, ',', '"');
        foreach ($products as $p) {
            $row = $p;
            if (isset($row['created_by'])) {
                $row['created_by'] = $row['created_by_name'];
            }
            fputcsv($output, $row, ',', '"');
        }
        fclose($output);
        exit();
    }
    ?>
    <main class="main-content">
        <h1>Product Stats</h1>
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
        <div class="dashboard-row" style="display:flex; gap:32px; flex-wrap:wrap; margin-bottom:32px;">
            <div class="dashboard-table left-table" style="flex:1; min-width:340px;">
                <h3 style="margin-bottom:12px;">Products & Supplier</h3>
                <table
                    style="width:100%; border-collapse:collapse; background:#fff; border-radius:8px; box-shadow:0 2px 8px #0001;">
                    <thead style="background:#1BB02C; color:#fff;">
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['name']) ?></td>
                                <td><?= htmlspecialchars($p['category']) ?></td>
                                <td><?= htmlspecialchars($p['supplier']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?><tr>
                                <td colspan="3">No products found.</td>
                            </tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="dashboard-table right-table" style="flex:1; min-width:340px;">
                <h3 style="margin-bottom:12px;">Stock Levels</h3>
                <table
                    style="width:100%; border-collapse:collapse; background:#fff; border-radius:8px; box-shadow:0 2px 8px #0001;">
                    <thead style="background:#1BB02C; color:#1976d2;">
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Stock Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['name']) ?></td>
                                <td><?= htmlspecialchars($p['category']) ?></td>
                                <td><?= $p['stock_quantity'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?><tr>
                                <td colspan="3">No products found.</td>
                            </tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <form method="post">
            <div class="products-table-actions"
                style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                <h2 class="section-title">Products Table</h2>
                <button type="submit" name="download_csv" class="btn">Download CSV</button>
            </div>
        </form>
        <table class="products-table"
            style="width:100%; border-collapse:collapse; background:#fff; border-radius:8px; box-shadow:0 2px 8px #0001;">
            <table class="products-table"
                style="width:100%; border-collapse:collapse; background:#fff; border-radius:8px; box-shadow:0 2px 8px #0001;">
                <thead style="background:#6aec79; color:#1BB02C;">
                    <tr>
                        <?php
                        $visibleCols = array_filter(array_keys($products[0] ?? []), function ($col) {
                            return !in_array($col, [
                                'description',
                                'batch_number',
                                'expiry_date',
                                'production_date',
                                'status',
                                'updated_at',
                                'created_by_name'
                            ]);
                        });
                        foreach ($visibleCols as $col):
                            if ($col === 'created_by'): ?>
                                <th>Created By</th>
                            <?php else: ?>
                                <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $col))) ?></th>
                        <?php endif;
                        endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <?php foreach ($visibleCols as $col): ?>
                                <?php if ($col === 'created_by'): ?>
                                    <td><?= htmlspecialchars($p['created_by_name']) ?></td>
                                <?php else: ?>
                                    <td><?= htmlspecialchars(is_numeric($p[$col]) && $col === 'price' ? number_format($p[$col], 2) : $p[$col]) ?>
                                    </td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?><tr>
                            <td colspan="<?= count($visibleCols) ?>">No products found.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </table>
    </main>
</body>

</html>