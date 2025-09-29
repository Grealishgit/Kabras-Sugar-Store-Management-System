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

// Handle AJAX view product
if (isset($_GET['view_id'])) {
    $product = $productHandler->getProductById($_GET['view_id']);
    header('Content-Type: application/json');
    echo json_encode($product);
    exit();
}

// Handle AJAX edit product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id = $_POST['edit_id'];
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
        'status' => $_POST['status'] ?? 'active'
    ];
    $productHandler->updateProduct($id, $data);
    header('Location: stock_entry.php?success=Product updated');
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
            <button class="ql-btn" onclick="showAddProductModal()">
                <i class="fas fa-plus-square"></i> Add Product
            </button>
            <button class="ql-btn" onclick="document.getElementById('stock-table').scrollIntoView();">
                <i class="fas fa-boxes"></i> View Stock
            </button>
        </div>

        <!-- Add Product Modal -->
        <div id="addProductModal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" onclick="hideAddProductModal()">&times;</span>
                <h2>Add New Product</h2>
                <form action="stock_entry.php" method="POST">
                    <div class="form-group"><label>Product Name</label><input type="text" name="name" required></div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="">Select Category</option>
                            <option value="Sugar">Sugar</option>
                            <option value="Molasses">Molasses</option>
                            <option value="Bagasse">Bagasse</option>
                            <option value="Ethanol">Ethanol</option>
                            <option value="Pressmud">Pressmud</option>
                            <option value="Filter Cake">Filter Cake</option>
                            <option value="Cane Trash">Cane Trash</option>
                            <option value="Cane Juice">Cane Juice</option>
                            <option value="Cane Syrup">Cane Syrup</option>
                            <option value="Sugarcane">Sugarcane</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Description</label><textarea name="description"></textarea></div>
                    <div class="form-group"><label>Price</label><input type="number" name="price" step="0.01" required>
                    </div>
                    <div class="form-group"><label>Quantity</label><input type="number" name="stock_quantity" required>
                    </div>
                    <div class="form-group"><label>Unit</label><input type="text" name="unit"></div>
                    <div class="form-group"><label>Batch #</label><input type="text" name="batch_number"></div>
                    <div class="form-group"><label>Expiry Date</label><input type="date" name="expiry_date"></div>
                    <div class="form-group"><label>Production Date</label><input type="date" name="production_date">
                    </div>
                    <div class="form-group"><label>Supplier</label><input type="text" name="supplier"></div>
                    <input type="hidden" name="created_by" value="<?php echo $currentUser['id']; ?>">
                    <button type="submit" name="add_product" class="btn">Add Product</button>
                </form>
            </div>
        </div>

        <!-- View Product Modal -->
        <div id="viewProductModal" class="view-modal" style="display:none;">
            <div class="modal-content">
                <span class="close" onclick="hideViewProductModal()">&times;</span>
                <h2>Product Information</h2>
                <div id="viewProductInfo" class="flex-row"></div>
            </div>
        </div>

        <!-- Edit Product Modal -->
        <div id="editProductModal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" onclick="hideEditProductModal()">&times;</span>
                <h2>Edit Product</h2>
                <form id="editProductForm" action="stock_entry.php" method="POST">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="form-group"><label>Product Name</label><input type="text" name="name" id="edit_name"
                            required></div>
                    <div class="form-group"><label>Category</label><input type="text" name="category"
                            id="edit_category"></div>
                    <div class="form-group"><label>Description</label><textarea name="description"
                            id="edit_description"></textarea></div>
                    <div class="form-group"><label>Price</label><input type="number" name="price" id="edit_price"
                            step="0.01" required></div>
                    <div class="form-group"><label>Quantity</label><input type="number" name="stock_quantity"
                            id="edit_stock_quantity" required></div>
                    <div class="form-group"><label>Unit</label><input type="text" name="unit" id="edit_unit"></div>
                    <div class="form-group"><label>Batch #</label><input type="text" name="batch_number"
                            id="edit_batch_number"></div>
                    <div class="form-group"><label>Expiry Date</label><input type="date" name="expiry_date"
                            id="edit_expiry_date"></div>
                    <div class="form-group"><label>Production Date</label><input type="date" name="production_date"
                            id="edit_production_date"></div>
                    <div class="form-group"><label>Supplier</label><input type="text" name="supplier"
                            id="edit_supplier"></div>
                    <button type="submit" name="edit_product" class="btn">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteProductModal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" onclick="hideDeleteProductModal()">&times;</span>
                <h2>Confirm Delete</h2>
                <p>Are you sure you want to delete this product?</p>
                <form id="deleteProductForm" action="stock_entry.php" method="GET">
                    <input type="hidden" name="delete_id" id="delete_id">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn" onclick="hideDeleteProductModal()">Cancel</button>
                </form>
            </div>
        </div>

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
                        <th>Batch #</th>
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
                            <td style="white-space:nowrap;">
                                <button class="btn-small" onclick="viewProduct(<?php echo $product['id']; ?>)"><i
                                        class="fas fa-eye"></i></button>
                                <button class="btn-small" onclick="editProduct(<?php echo $product['id']; ?>)"><i
                                        class="fas fa-edit"></i></button>
                                <button class="btn-small btn-danger"
                                    onclick="confirmDeleteProduct(<?php echo $product['id']; ?>)"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <script src="../assets/js/stock_entry.js"></script>
    </main>
</body>

</html>