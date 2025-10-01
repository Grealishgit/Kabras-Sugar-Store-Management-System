<?php
session_start();
require_once 'handlers/AuthHandler.php';
$authHandler = new AuthHandler();

// Redirect if already logged in
if ($authHandler->isLoggedIn()) {
    $currentUser = $authHandler->getCurrentUser();
    if ($currentUser) {
        $role = strtolower($currentUser['role']);
        if ($role === 'admin') {
            header('Location: admin/dashboard.php');
            exit();
        } elseif ($role === 'manager') {
            header('Location: manager/dashboard.php');
            exit();
        } elseif ($role === 'cashier') {
            header('Location: cashier/dashboard.php');
            exit();
        } elseif ($role === 'accountant') {
            header('Location: accountant/dashboard.php');
            exit();
        } elseif ($role === 'storekeeper') {
            header('Location: storekeeper/dashboard.php');
            exit();
        } else {
            header('Location: index.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kabras Sugar Store - Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
    <div class="login-page">
        <!-- Left side (branding/info) -->
        <div class="login-image-section">
            <div class="image-overlay">

                <div class="welcome-content">
                    <h1>Welcome to Kabras Sugar Store</h1>
                    <p>Internal Management System for Inventory & Sales</p>
                </div>
            </div>
        </div>

        <!-- Right side (Login & Register) -->
        <div class="login-form-section">
            <div class="login-container">
                <!-- LOGIN -->
                <div class="login-section" id="loginSection">
                    <div class="login-header">
                        <div class="logo-container">
                            <img src="./uploads/kabras-logo.png" alt="">
                        </div>
                        <h2>Welcome Back</h2>
                        <p>Sign in to your account</p>
                    </div>

                    <?php if (isset($_GET['error'])): ?>
                    <div class="alert error"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_GET['success'])): ?>
                    <div class="alert success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                    <?php endif; ?>

                    <form action="auth-handler.php" method="POST" id="loginForm">
                        <input type="hidden" name="action" value="login">

                        <div class="form-group">
                            <label for="username" class="form-label">Email</label>
                            <input type="email" placeholder="Enter your email" id="username" name="username"
                                class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" placeholder="Enter your password" id="password" name="password"
                                class="form-input" required>
                        </div>
                        <!-- Role select removed -->

                        <div class="button-group">
                            <button type="submit" class="btn-primary">Login to Continue</button>
                        </div>
                    </form>
                </div>

                <!-- Register section removed -->
            </div>
        </div>



    </div>

    <!-- Register JS removed -->
</body>

</html>