<?php
// sidebar.php

// Assume session and access control are handled in the main file
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Role -> menu mapping (label, href, fontawesome-class)
$menu = [
    'common' => [
        ['label' => 'Home', 'href' => 'home.php', 'icon' => 'fas fa-home'],
    ],
    'Admin' => [
        ['label' => 'User Management', 'href' => 'users.php', 'icon' => 'fas fa-users'],
        ['label' => 'Assign Roles', 'href' => 'roles.php', 'icon' => 'fas fa-user-cog'],
        ['label' => 'System Backup', 'href' => 'backup.php', 'icon' => 'fas fa-server'],
    ],
    'StoreKeeper' => [
        ['label' => 'Stock Entry', 'href' => 'stock_entry.php', 'icon' => 'fas fa-box-open'],
        ['label' => 'Stock Levels', 'href' => 'stock_levels.php', 'icon' => 'fas fa-boxes'],
        ['label' => 'Stock Alerts', 'href' => 'stock_alerts.php', 'icon' => 'fas fa-exclamation-triangle'],
    ],
    'Cashier' => [
        ['label' => 'Sales', 'href' => 'sales.php', 'icon' => 'fas fa-cash-register'],
        ['label' => 'Receipts', 'href' => 'receipts.php', 'icon' => 'fas fa-receipt'],
        ['label' => 'Payments', 'href' => 'payments.php', 'icon' => 'fas fa-credit-card'],
    ],
    'Manager' => [
        ['label' => 'Sales Reports', 'href' => 'reports.php', 'icon' => 'fas fa-chart-line'],
        ['label' => 'Discounts & Refunds', 'href' => 'discounts.php', 'icon' => 'fas fa-percentage'],
        ['label' => 'Store Performance', 'href' => 'performance.php', 'icon' => 'fas fa-tachometer-alt'],
    ],
    'Accountant' => [
        ['label' => 'Audit Reports', 'href' => 'audit_reports.php', 'icon' => 'fas fa-file-alt'],
        ['label' => 'Compliance', 'href' => 'compliance.php', 'icon' => 'fas fa-balance-scale'],
        ['label' => 'Financial Reports', 'href' => 'finance.php', 'icon' => 'fas fa-file-invoice-dollar'],
    ],
];

// helper get items for role
$items = $menu['common'];
if (isset($menu[$role])) {
    $items = array_merge($items, $menu[$role]);
}
?>

<!-- Font Awesome CDN (put in head of your page instead if preferred) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    integrity="sha512-p/w+..." crossorigin="anonymous" referrerpolicy="no-referrer" />

<aside class="sidebar" role="navigation" aria-label="Main Sidebar">
    <div class="sidebar-inner">
        <div class="sidebar-brand">
            <a href="home.php" class="brand-link">
                <i class="fas fa-store"></i>
                <span class="brand-text">KabrasPOS</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <?php foreach ($items as $it): ?>
                    <li>
                        <a href="<?= htmlspecialchars($it['href']) ?>"
                            class="<?= (basename($_SERVER['PHP_SELF']) === basename($it['href'])) ? 'active' : '' ?>">
                            <i class="<?= htmlspecialchars($it['icon']) ?>" aria-hidden="true"></i>
                            <span class="label"><?= htmlspecialchars($it['label']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- footer: pinned to bottom -->
        <div class="sidebar-footer" role="region" aria-label="User actions">
            <div class="usern-name">
                <h2>
                    <?php
                    if (isset($currentUser) && isset($currentUser['name']) && $currentUser['name']) {
                        echo htmlspecialchars($currentUser['name']);
                    } else {
                        echo 'Guest';
                    }
                    ?>
                </h2>
                <p>Role: <strong>
                        <?php
                        if (isset($currentUser) && isset($currentUser['role']) && $currentUser['role']) {
                            echo htmlspecialchars($currentUser['role']);
                        } else {
                            echo 'No Role';
                        }
                        ?>
                    </strong></p>
            </div>

            <div class="footer-actions">
                <form action="logout.php" method="POST" class="logout-form" style="display:inline;">
                    <button type="submit" class="btn-logout" aria-label="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span> <a href="?logout=1" class="logout-link">Logout</a></span>
                        <i class="fas fa-chevron-down chevron-down" aria-hidden="true"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>