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

// Handle add product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $data = [
        'name' => $_POST['name'],
        'category' => $_POST['category'] ?? null,
        'description' => $_POST['description'] ?? null,
        'price' => $_POST['price'],
        'stock_quantity' => $_POST['stock_quantity'],
        'unit' => $_POST['unit'] ?? null,
        'batch_number' => $_POST['batch_number'] ?? null,
        'expiry_date' => $_POST['expiry_date'] ?? null,
        'production_date' => $_POST['production_date'] ?? null,
        'supplier' => $_POST['supplier'] ?? null,
        'status' => 'active',
        'created_by' => $currentUser['id']
    ];
    $productHandler->addProduct($data);
    header('Location: stock_entry.php?success=Product added');
    exit();
}

// Handle delete product
if (isset($_GET['delete_id'])) {
    $productHandler->deleteProduct($_GET['delete_id']);
    header('Location: stock_entry.php?success=Product deleted');
    exit();
}

// Get all products
$products = $productHandler->getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/stock_entry.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <title>StoreKeeper | Stock Entry</title>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($currentUser['name']); ?> 👋</h1>
        <p class="subtitle">Manage your store inventory here</p>

        <!-- Quick Actions -->
        <div class="quick-links">
            <button class="ql-btn" onclick="document.getElementById('add-product-form').scrollIntoView();">
                <i class="fas fa-plus-square"></i> Add Product
            </button>
            <button class="ql-btn" onclick="document.getElementById('stock-table').scrollIntoView();">
                <i class="fas fa-boxes"></i> View Stock
            </button>
        </div>

        <!-- Add Product Form -->
        <section id="add-product-form" class="card-section">
            <h2>Add New Product</h2>
            <form action="stock_entry.php" method="POST">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="stock_quantity" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category">
                </div>
                <button type="submit" name="add_product" class="btn">Add Product</button>
            </form>
        </section>

        <!-- Stock Table -->
        <section id="stock-table" class="card-section">
            <h2>Current Stock</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Batch No</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Created On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td><?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['stock_quantity']; ?></td>
                            <td><?php echo htmlspecialchars($product['unit']); ?></td>
                            <td><?php echo htmlspecialchars($product['batch_number']); ?></td>
                            <td><?php echo htmlspecialchars($product['supplier']); ?></td>
                            <td><?php echo htmlspecialchars($product['status']); ?></td>
                            <td><?php echo $product['created_at']; ?></td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-small">Edit</a>
                                <a href="stock_entry.php?delete_id=<?php echo $product['id']; ?>"
                                    class="btn-small btn-danger"
                                    onclick="return confirm('Delete this product?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>

</html>