<?php session_start();
require_once '../handlers/AuthHandler.php';
require_once '../app/models/User.php';

$authHandler = new AuthHandler();
$userHandler = new User();

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

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $userId = intval($_POST['user_id']);
    $newRole = $_POST['role'];
    $userHandler->updateRole($userId, $newRole);
    header("Location: roles.php?success=Role updated successfully.");
    exit();
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = intval($_POST['user_id']);
    $userHandler->deleteUser($userId);
    header("Location: roles.php?success=User deleted successfully.");
    exit();
}

// Get all users
$users = $userHandler->getAllUsers();


// Count users by role
$roleCounts = [
    'Admin' => 0,
    'Manager' => 0,
    'Cashier' => 0,
    'Accountant' => 0,
    'StoreKeeper' => 0,
];

foreach ($users as $user) {
    if (isset($roleCounts[$user['role']])) {
        $roleCounts[$user['role']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Roles</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/roles.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <h1>Manage User Roles</h1><?php if (isset($_GET['success'])): ?>
                <p class="success-msg"><?= htmlspecialchars($_GET['success']); ?>
                </p><?php endif; ?>

            <div class="stats-cards">
                <div class="stat-card admin">
                    <h3>Admins</h3>
                    <p><?= $roleCounts['Admin']; ?></p>
                </div>
                <div class="stat-card manager">
                    <h3>Managers</h3>
                    <p><?= $roleCounts['Manager']; ?></p>
                </div>
                <div class="stat-card cashier">
                    <h3>Cashiers</h3>
                    <p><?= $roleCounts['Cashier']; ?></p>
                </div>
                <div class="stat-card accountant">
                    <h3>Accountants</h3>
                    <p><?= $roleCounts['Accountant']; ?></p>
                </div>
                <div class="stat-card storekeeper">
                    <h3>Store Keepers</h3>
                    <p><?= $roleCounts['StoreKeeper']; ?></p>
                </div>
            </div>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Created On</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?><tr>
                            <td><?= htmlspecialchars($user['id']); ?></td>
                            <td><?= htmlspecialchars($user['name']); ?></td>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))); ?></td>
                            <td>
                                <form method="POST" action="roles.php" class="role-form">
                                    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                    <select name="role">
                                        <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin
                                        </option>
                                        <option value="Manager" <?= $user['role'] === 'Manager' ? 'selected' : ''; ?>>
                                            Manager</option>
                                        <option value="Cashier" <?= $user['role'] === 'Cashier' ? 'selected' : ''; ?>>
                                            Cashier</option>
                                        <option value="Accountant" <?= $user['role'] === 'Accountant' ? 'selected' : ''; ?>>
                                            Accountant</option>
                                        <option value="StoreKeeper"
                                            <?= $user['role'] === 'StoreKeeper' ? 'selected' : ''; ?>>
                                            Store Keeper</option>
                                    </select>
                                    <button type="submit" name="update_role">Update</button>
                                </form>
                            </td>
                            <td><?php if ($user['id'] !== $currentUser['id']): ?>
                                    <form method="POST" action="roles.php"
                                        onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn-danger">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <em>Main Admin</em>
                                <?php endif; ?>
                            </td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>

</html>