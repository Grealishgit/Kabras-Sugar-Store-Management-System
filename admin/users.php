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

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    $authHandler->handleLogout();
    header('Location: ../login.php?success=You have been logged out.');
    exit();
}

// Ensure user is logged in and is admin
if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in to access the admin dashboard.');
    exit();
}

$currentUser = $authHandler->getCurrentUser();
if ($currentUser['role'] !== 'Admin') {
    header('Location: ../login.php?error=Access denied. Admin privileges required.');
    exit();
}


// Get all users
$users = $authHandler->getAllUsers();
// 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Users Management</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <div class="header-left">
                <h1><i class="fas fa-users"></i> Users Management</h1>
                <p>Manage all system users and their permissions</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openAddUserModal()">
                    <i class="fas fa-plus"></i> Add New User
                </button>
                <button class="btn btn-secondary" onclick="exportUsers()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon admin">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count(array_filter($users, function ($user) {
                            return $user['role'] === 'Admin';
                        })); ?></h3>
                    <p>Administrators</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon manager">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count(array_filter($users, function ($user) {
                            return $user['role'] === 'Manager';
                        })); ?></h3>
                    <p>Managers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon staff">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count(array_filter($users, function ($user) {
                            return in_array($user['role'], ['Cashier', 'Accountant', 'StoreKeeper']);
                        })); ?></h3>
                    <p>Staff Members</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($users); ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="filters-section">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchUsers" placeholder="Search users by name, email, or role...">
            </div>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Users</button>
                <button class="filter-btn" data-filter="Admin">Admins</button>
                <button class="filter-btn" data-filter="Manager">Managers</button>
                <button class="filter-btn" data-filter="Cashier">Cashiers</button>
                <button class="filter-btn" data-filter="Accountant">Accountants</button>
                <button class="filter-btn" data-filter="StoreKeeper">Store Keepers</button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <table class="users-table" id="usersTable">
                <thead>
                    <tr>
                        <th>User Info</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="user-row" data-role="<?php echo htmlspecialchars($user['role']); ?>">
                            <td>
                                <div class="user-info">

                                    <div class="user-details">
                                        <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <div><i class="fas fa-phone"></i>
                                        <?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></div>
                                    <div><i class="fas fa-id-card"></i>
                                        <?php echo htmlspecialchars($user['national_id'] ?? 'N/A'); ?></div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge role-<?php echo strtolower($user['role']); ?>">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-active">
                                    <i class="fas fa-circle"></i> Active
                                </span>
                            </td>
                            <td>
                                <div class="date-info">
                                    <?php if (isset($user['last_login']) && $user['last_login']): ?>
                                        <div class="last-login"><?php echo date('M j, Y', strtotime($user['last_login'])); ?>
                                        </div>
                                        <div class="last-login-time">
                                            <?php echo date('g:i A', strtotime($user['last_login'])); ?></div>
                                    <?php else: ?>
                                        <div class="last-login">Never logged in</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" onclick="editUser(<?php echo $user['id']; ?>)"
                                        title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-view" onclick="viewUser(<?php echo $user['id']; ?>)"
                                        title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($user['id'] != $currentUser['id']): ?>
                                        <button class="btn-action btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)"
                                            title="Delete User">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <div class="pagination-info">
                Showing <span id="showingStart">1</span> to <span
                    id="showingEnd"><?php echo min(10, count($users)); ?></span> of <span
                    id="totalUsers"><?php echo count($users); ?></span> users
            </div>
            <div class="pagination">
                <button class="pagination-btn" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
                <div class="pagination-numbers" id="paginationNumbers"></div>
                <button class="pagination-btn" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New User</h2>
                <button class="close-btn" onclick="closeUserModal()">&times;</button>
            </div>
            <form id="userForm">
                <input type="hidden" id="userId">
                <div class="form-row">
                    <div class="form-group">
                        <label for="userName">Full Name *</label>
                        <input type="text" id="userName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="userEmail">Email *</label>
                        <input type="email" id="userEmail" name="email" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="userPhone">Phone</label>
                        <input type="text" id="userPhone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="userNationalId">National ID</label>
                        <input type="text" id="userNationalId" name="national_id">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="userRole">Role *</label>
                        <select id="userRole" name="role" required>
                            <option value="Admin">Administrator</option>
                            <option value="Manager">Manager</option>
                            <option value="Cashier">Cashier</option>
                            <option value="Accountant">Accountant</option>
                            <option value="StoreKeeper">Store Keeper</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="userPassword">Password</label>
                        <input type="password" id="userPassword" name="password"
                            placeholder="Leave blank to keep current password">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content small">
            <div class="modal-header">
                <h2>Confirm Delete</h2>
                <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete User</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/users.js"></script>
</body>

</html>