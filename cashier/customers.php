<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && $_POST['logout'] == '1') {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

require_once '../handlers/AuthHandler.php';
require_once '../handlers/CustomerHandler.php';

$authHandler = new AuthHandler();
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

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $result = $customerHandler->handleRequest();
    $message = $result['success'] ? $result['message'] : $result['error'];
}

// Get customers data
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';

if ($search) {
    $customers = $customerHandler->searchCustomers($search);
} elseif ($type_filter || $status_filter) {
    $customers = $customerHandler->filterCustomers($type_filter ?: null, $status_filter ?: null);
} else {
    $customers = $customerHandler->getAllCustomers();
}

// Get statistics
$stats = $customerHandler->getCustomerStats();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/customers.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Cashier Customers</title>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">
        <h1>Customers</h1>
        <p class="subtitle">Manage your customers here</p>

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'success') !== false ? 'alert-success' : 'alert-error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['total'] ?></h3>
                    <p>Total Customers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['active'] ?></h3>
                    <p>Active Customers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['individual'] ?></h3>
                    <p>Individual</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['business'] ?></h3>
                    <p>Business</p>
                </div>
            </div>
        </div>

        <!-- Actions and Filters -->
        <div class="actions">
            <button class="btn btn-primary" onclick="openModal('addCustomerModal')">
                <i class="fas fa-plus"></i> Add Customer
            </button>

            <div class="filters">
                <form method="GET" class="filter-form">
                    <div class="search-container">
                        <input type="text" name="search" id="searchInput" placeholder="Search customers..."
                            value="<?= htmlspecialchars($search) ?>" class="search-input">
                        <button type="button" id="liveSearchToggle" class="btn btn-toggle" onclick="toggleLiveSearch()">
                            <i class="fas fa-bolt"></i> Live Search: OFF
                        </button>
                    </div>

                    <select name="type" class="filter-select">
                        <option value="">All Types</option>
                        <option value="individual" <?= $type_filter === 'individual' ? 'selected' : '' ?>>Individual
                        </option>
                        <option value="business" <?= $type_filter === 'business' ? 'selected' : '' ?>>Business</option>
                    </select>

                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive
                        </option>
                    </select>

                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Filter
                    </button>

                    <a href="customers.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </form>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="table-container">
            <table class="customers-table">
                <thead>
                    <tr>
                        <th>Customer Code</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Town</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="9" class="no-data">No customers found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= htmlspecialchars($customer['customer_code'] ?? '') ?></td>
                                <td><?= htmlspecialchars($customer['name']) ?></td>
                                <td><?= htmlspecialchars($customer['email'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($customer['phone'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($customer['town'] ?? '-') ?></td>
                                <td>
                                    <span class="type-badge type-<?= $customer['type'] ?>">
                                        <?= ucfirst($customer['type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $customer['status'] ?>">
                                        <?= ucfirst($customer['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($customer['created_at'])) ?></td>
                                <td class="actions-cell">
                                    <button class="btn-action btn-view" onclick="viewCustomer(<?= $customer['id'] ?>)"
                                        title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editCustomer(<?= $customer['id'] ?>)"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteCustomer(<?= $customer['id'] ?>)"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Customer Modal -->
        <div id="addCustomerModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add New Customer</h3>
                    <span class="close" onclick="closeModal('addCustomerModal')">&times;</span>
                </div>
                <form method="POST" class="customer-form">
                    <input type="hidden" name="action" value="create">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="town">Town</label>
                            <input type="text" id="town" name="town">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select id="type" name="type">
                                <option value="individual">Individual</option>
                                <option value="business">Business</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-outline"
                            onclick="closeModal('addCustomerModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Customer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Customer Modal -->
        <div id="viewCustomerModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Customer Details</h3>
                    <span class="close" onclick="closeModal('viewCustomerModal')">&times;</span>
                </div>
                <div id="viewCustomerContent" class="customer-details">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Edit Customer Modal -->
        <div id="editCustomerModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Customer</h3>
                    <span class="close" onclick="closeModal('editCustomerModal')">&times;</span>
                </div>
                <form method="POST" class="customer-form">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_name">Name *</label>
                            <input type="text" id="edit_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" id="edit_email" name="email">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_phone">Phone</label>
                            <input type="tel" id="edit_phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="edit_town">Town</label>
                            <input type="text" id="edit_town" name="town">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_address">Address</label>
                        <textarea id="edit_address" name="address" rows="3"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_type">Type</label>
                            <select id="edit_type" name="type">
                                <option value="individual">Individual</option>
                                <option value="business">Business</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select id="edit_status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_notes">Notes</label>
                        <textarea id="edit_notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-outline"
                            onclick="closeModal('editCustomerModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteCustomerModal" class="modal">
            <div class="modal-content modal-small">
                <div class="modal-header">
                    <h3>Confirm Delete</h3>
                    <span class="close" onclick="closeModal('deleteCustomerModal')">&times;</span>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this customer? This action cannot be undone.</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="delete_id" name="id">
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline"
                            onclick="closeModal('deleteCustomerModal')">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Customer data for JavaScript
        const customers = <?= json_encode($customers) ?>;
        let liveSearchEnabled = false;
        let allCustomers = [...customers]; // Keep original data

        // Live search functionality
        function toggleLiveSearch() {
            liveSearchEnabled = !liveSearchEnabled;
            const toggle = document.getElementById('liveSearchToggle');
            const searchInput = document.getElementById('searchInput');

            if (liveSearchEnabled) {
                toggle.innerHTML = '<i class="fas fa-bolt"></i> Live Search: ON';
                toggle.classList.add('active');
                searchInput.addEventListener('input', performLiveSearch);
            } else {
                toggle.innerHTML = '<i class="fas fa-bolt"></i> Live Search: OFF';
                toggle.classList.remove('active');
                searchInput.removeEventListener('input', performLiveSearch);
                // Reset to show all customers
                displayCustomers(allCustomers);
            }
        }

        function performLiveSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const filtered = allCustomers.filter(customer => {
                return (
                    customer.name.toLowerCase().includes(searchTerm) ||
                    (customer.email && customer.email.toLowerCase().includes(searchTerm)) ||
                    (customer.phone && customer.phone.toLowerCase().includes(searchTerm)) ||
                    (customer.customer_code && customer.customer_code.toLowerCase().includes(searchTerm)) ||
                    (customer.town && customer.town.toLowerCase().includes(searchTerm))
                );
            });
            displayCustomers(filtered);
        }

        function displayCustomers(customerList) {
            const tbody = document.querySelector('.customers-table tbody');

            if (customerList.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="no-data">No customers found</td></tr>';
                return;
            }

            tbody.innerHTML = customerList.map(customer => `
                <tr>
                    <td>${customer.customer_code || ''}</td>
                    <td>${customer.name}</td>
                    <td>${customer.email || '-'}</td>
                    <td>${customer.phone || '-'}</td>
                    <td>${customer.town || '-'}</td>
                    <td>
                        <span class="type-badge type-${customer.type}">
                            ${customer.type.charAt(0).toUpperCase() + customer.type.slice(1)}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-${customer.status}">
                            ${customer.status.charAt(0).toUpperCase() + customer.status.slice(1)}
                        </span>
                    </td>
                    <td>${new Date(customer.created_at).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}</td>
                    <td class="actions-cell">
                        <button class="btn-action btn-view" onclick="viewCustomer(${customer.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-action btn-edit" onclick="editCustomer(${customer.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-action btn-delete" onclick="deleteCustomer(${customer.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // View customer
        function viewCustomer(id) {
            const customer = customers.find(c => c.id == id);
            if (customer) {
                const content = `
                    <div class="detail-row">
                        <strong>Customer Code:</strong> ${customer.customer_code || 'N/A'}
                    </div>
                    <div class="detail-row">
                        <strong>Name:</strong> ${customer.name}
                    </div>
                    <div class="detail-row">
                        <strong>Email:</strong> ${customer.email || 'N/A'}
                    </div>
                    <div class="detail-row">
                        <strong>Phone:</strong> ${customer.phone || 'N/A'}
                    </div>
                    <div class="detail-row">
                        <strong>Address:</strong> ${customer.address || 'N/A'}
                    </div>
                    <div class="detail-row">
                        <strong>Town:</strong> ${customer.town || 'N/A'}
                    </div>
                    <div class="detail-row">
                        <strong>Type:</strong> <span class="type-badge type-${customer.type}">${customer.type.charAt(0).toUpperCase() + customer.type.slice(1)}</span>
                    </div>
                    <div class="detail-row">
                        <strong>Status:</strong> <span class="status-badge status-${customer.status}">${customer.status.charAt(0).toUpperCase() + customer.status.slice(1)}</span>
                    </div>
                    <div class="detail-row">
                        <strong>Notes:</strong> ${customer.notes || 'N/A'}
                    </div>
                    <div class="detail-row">
                        <strong>Created:</strong> ${new Date(customer.created_at).toLocaleDateString()}
                    </div>
                `;
                document.getElementById('viewCustomerContent').innerHTML = content;
                openModal('viewCustomerModal');
            }
        }

        // Edit customer
        function editCustomer(id) {
            const customer = customers.find(c => c.id == id);
            if (customer) {
                document.getElementById('edit_id').value = customer.id;
                document.getElementById('edit_name').value = customer.name;
                document.getElementById('edit_email').value = customer.email || '';
                document.getElementById('edit_phone').value = customer.phone || '';
                document.getElementById('edit_town').value = customer.town || '';
                document.getElementById('edit_address').value = customer.address || '';
                document.getElementById('edit_type').value = customer.type;
                document.getElementById('edit_status').value = customer.status;
                document.getElementById('edit_notes').value = customer.notes || '';
                openModal('editCustomerModal');
            }
        }

        // Delete customer
        function deleteCustomer(id) {
            document.getElementById('delete_id').value = id;
            openModal('deleteCustomerModal');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>

</html>