<?php
session_start();
require_once '../handlers/AuthHandler.php';
require_once '../app/models/User.php';

$authHandler = new AuthHandler();
$userHandler = new User();

// Ensure user is logged in
if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in first.');
    exit();
}

$currentUser = $authHandler->getCurrentUser();

// Restrict only Admins
if ($currentUser['role'] !== 'Admin') {
    header('Location: ../login.php?error=Access denied. Admin privileges required.');
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
    'StoreKeeper' => 0
];

foreach ($users as $user) {
    if (isset($roleCounts[$user['role']])) {
        $roleCounts[$user['role']]++;
    }
}

$totalUsers = count($users);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Home</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <h1>Welcome, <?= htmlspecialchars($currentUser['name']); ?></h1>
            <p class="subtitle">System Overview</p>

            <!-- Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p><?= $totalUsers; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Admins</h3>
                    <p><?= $roleCounts['Admin']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Managers</h3>
                    <p><?= $roleCounts['Manager']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Cashiers</h3>
                    <p><?= $roleCounts['Cashier']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Accountants</h3>
                    <p><?= $roleCounts['Accountant']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Store Keepers</h3>
                    <p><?= $roleCounts['StoreKeeper']; ?></p>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="quick-links">
                <a href="users.php" class="ql-btn"><i class="fas fa-user-plus"></i> Add User</a>
                <a href="backup.php" class="ql-btn"><i class="fas fa-database"></i> Backup DB</a>
                <a href="../modules/products/list.php" class="ql-btn"><i class="fas fa-box"></i> Manage Products</a>
                <a href="#" class="ql-btn"><i class="fas fa-chart-line"></i> View Reports</a>
            </div>

            <!-- Activity + Role Chart -->
            <div class="activity-role-grid">
                <div class="recent-activity">
                    <h2>Recent Activity</h2>
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent = array_slice(array_filter($users, fn($u) => !empty($u['last_login'])), -5);
                            foreach (array_reverse($recent) as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['name']); ?></td>
                                    <td><?= htmlspecialchars($u['email']); ?></td>
                                    <td><?= htmlspecialchars($u['role']); ?></td>
                                    <td><?= date('M j, Y g:i A', strtotime($u['last_login'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="role-chart">
                    <h2>User Role Distribution</h2>
                    <canvas id="roleChart" width="300" height="300"></canvas>
                </div>
            </div>

            <!-- Pending + Backup -->
            <div class="pending-backup-grid">
                <div class="pending-requests">
                    <h2>Pending Requests</h2>
                    <ul>
                        <?php
                        $pending = array_filter($users, fn($u) => isset($u['status']) && $u['status'] === 'pending');
                        if ($pending) {
                            foreach ($pending as $u) {
                                echo '<li>' . htmlspecialchars($u['name']) . ' (' . htmlspecialchars($u['email']) . ')</li>';
                            }
                        } else {
                            echo '<li>No pending requests.</li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="backup-status">
                    <h2>Backup Status</h2>
                    <?php
                    $backupDir = realpath(__DIR__ . '/../backups');
                    $lastBackup = '';
                    if ($backupDir && is_dir($backupDir)) {
                        $files = glob($backupDir . '/db-backup-*.sql');
                        if ($files) {
                            usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
                            $lastBackup = basename($files[0]) . ' (' . date('M j, Y g:i A', filemtime($files[0])) . ')';
                        }
                    }
                    ?>
                    <p>Last Backup: <?= $lastBackup ? $lastBackup : 'No backups found.'; ?></p>
                    <form method="post" action="backup.php" style="display:inline;">
                        <button type="submit" name="backup_db" class="ql-btn">
                            <i class="fas fa-database"></i> Backup Now
                        </button>
                    </form>
                </div>
            </div>

            <!-- Calendar -->
            <div class="calendar-section">
                <h2>Calendar</h2>
                <div id="calendar"></div>
            </div>
        </div>
    </main>

    <!-- Chart -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('roleChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Admin', 'Manager', 'Cashier', 'Accountant', 'StoreKeeper'],
                    datasets: [{
                        data: [<?= $roleCounts['Admin']; ?>, <?= $roleCounts['Manager']; ?>,
                            <?= $roleCounts['Cashier']; ?>, <?= $roleCounts['Accountant']; ?>,
                            <?= $roleCounts['StoreKeeper']; ?>
                        ],
                        backgroundColor: ['#1BB02C', '#3498db', '#f39c12', '#e67e22', '#8e44ad'],
                    }]
                },
                options: {
                    responsive: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Simple Calendar
            const calendar = document.getElementById("calendar");
            const date = new Date();
            const month = date.toLocaleString('default', {
                month: 'long'
            });
            const year = date.getFullYear();
            const days = new Date(year, date.getMonth() + 1, 0).getDate();

            let html = `<div class="calendar-header">${month} ${year}</div><div class="calendar-grid">`;
            for (let i = 1; i <= days; i++) {
                let today = (i === date.getDate()) ? "today" : "";
                html += `<div class="calendar-day ${today}">${i}</div>`;
            }
            html += "</div>";
            calendar.innerHTML = html;
        });
    </script>
</body>

</html>