<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_csv'])) {
    // Suppress deprecated warnings and output only CSV
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    require_once '../handlers/UserHandler.php';
    $userHandler = new UserHandler();
    $users = $userHandler->getAllUsers();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="staff.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'Email', 'Role', 'Phone', 'National ID', 'Joined'], ',', '"');
    foreach ($users as $u) {
        fputcsv($output, [
            $u['name'],
            $u['email'],
            $u['role'],
            $u['phone'] ?? 'N/A',
            $u['national_id'] ?? 'N/A',
            date('Y-m-d', strtotime($u['created_at']))
        ], ',', '"');
    }
    fclose($output);
    exit();
}

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

require_once '../handlers/UserHandler.php';
$userHandler = new UserHandler();
$users = $userHandler->getAllUsers();
$totalUsers = count($users);
$roles = array_column($users, 'role');
$uniqueRoles = array_unique($roles);
$totalRoles = count($uniqueRoles);
usort($users, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$recentUsers = array_slice($users, 0, 5);
$roleCounts = array_count_values($roles);
$topRole = $roleCounts ? array_search(max($roleCounts), $roleCounts) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | Staff</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/staff.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <h1>Staff Overview</h1>
        <div class="stats-container">
            <div class="stat-card">
                <h2><?php echo $totalUsers; ?></h2>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $totalRoles; ?></h2>
                <p>Total Roles</p>
            </div>
            <div class="stat-card">
                <h2><?php echo count($recentUsers); ?></h2>
                <p>Recently Joined</p>
            </div>
            <div class="stat-card">
                <h2><?php echo htmlspecialchars($topRole); ?></h2>
                <p>Role with Most Users</p>
            </div>
        </div>

        <h2 class="section-title">Staff Cards</h2>
        <div class="user-cards">
            <?php foreach ($users as $u): ?>
                <div class="user-card">
                    <h3><?php echo htmlspecialchars($u['name']); ?></h3>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars($u['role']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($u['email']); ?></p>
                    <p><strong>Joined:</strong> <?php echo date('Y-m-d', strtotime($u['created_at'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>


        <form method="post">
            <div class="staff-table-actions">
                <h2 class="section-title">Staff Table</h2>
                <button type="submit" name="download_csv" class="btn">Download CSV</button>
            </div>

        </form>
        <table class="staff-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>ID No</th>
                    <th>Role</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($u['national_id'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($u['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?><tr>
                        <td colspan="4">No users found.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </main>

    <!-- CSV logic moved to top of file -->
</body>

</html>