<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/navbar.css" />
    <title>Kabras Sugar Store</title>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-wrapper">
            <!-- Left: Brand/Title -->
            <a href="/" class="navbar-brand">
                Kabras Sugar Store
            </a>

            <!-- Center: Navigation Links -->
            <div class="navbar-nav <?php echo $isLoggedIn ? '' : 'hidden'; ?>" id="mainNav">
                <?php if ($isLoggedIn): ?>
                    <?php if ($currentRole === 'admin'): ?>
                        <a href="/" class="nav-btn">
                            Home
                        </a>
                        <a class="nav-btn" href="admin.php">
                            Dashboard
                        </a>
                    <?php endif; ?>

                    <?php if ($currentRole === 'cashier'): ?>
                        <a href="../manager/dashboard.php" class="nav-btn">
                            Manager
                        </a>
                        <a class="nav-btn" href="../Account/dashboard.php">
                            Accountant
                        </a>
                    <?php endif; ?>

                    <?php if ($currentRole === 'manager'): ?>
                        <a href="/" class="nav-btn">
                            Home
                        </a>
                        <a href="../store/storekeeper.php" class="nav-btn">
                            Store
                        </a>
                        <a href="menu.php" class="nav-btn">
                            View Menu
                        </a>

                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Right: User Info -->
            <div class="navbar-user" id="topInfo">
                <?php if ($isLoggedIn): ?>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($currentUserName); ?></span>
                        <span class="user-role">(<?php echo htmlspecialchars($currentRole); ?>)</span>
                    </div>
                    <a href="?logout=1" class="logout-link">Logout</a>
                <?php else: ?>
                    <a href="login.php">Not logged in?</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</body>

</html>


<!-- Integrated Navbar Component -->


</html>