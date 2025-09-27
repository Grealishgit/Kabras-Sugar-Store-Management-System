<?php
session_start();
require_once 'handlers/AuthHandler.php';
$authHandler = new AuthHandler();

// Redirect if already logged in
if ($authHandler->isLoggedIn()) {
    $currentUser = $authHandler->getCurrentUser();
    if ($currentUser) {
        if ($currentUser['role'] === 'admin') {
            header('Location: admin/dashboard.php');
            exit();
        } elseif ($currentUser['role'] === 'manager') {
            header('Location: manager.php');
            exit();
        } else {
            header('Location: staff.php');
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
                <div class="logo-container">
                    <img src="./uploads/kabras-logo.png" alt="">
                </div>
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
                        <!-- login-role-select.html -->
                        <div class="role-select horizontal">
                            <p class="role-label">Select Login Role:</p>

                            <div class="role-options">
                                <label class="role-option">
                                    <input type="radio" name="role" value="staff" checked>
                                    <span class="custom-radio"></span>
                                    Staff
                                </label>

                                <label class="role-option">
                                    <input type="radio" name="role" value="manager">
                                    <span class="custom-radio"></span>
                                    Manager
                                </label>

                                <label class="role-option">
                                    <input type="radio" name="role" value="admin">
                                    <span class="custom-radio"></span>
                                    Admin
                                </label>
                            </div>


                        </div>

                        <div class="button-group">
                            <button type="submit" class="btn-primary">Sign In</button>
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <button type="button" class="btn-secondary" onclick="showRegister()">Register</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- REGISTER -->
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <div class="register-section hidden" id="registerSection">
                        <div class="login-header">
                            <h2>Create An Account</h2>
                            <p>Only admins can create accounts</p>
                        </div>

                        <form action="auth-handler.php" method="POST" id="registerForm">
                            <input type="hidden" name="action" value="register">

                            <div class="form-group">
                                <label for="reg_name" class="form-label">Full Name</label>
                                <input type="text" id="reg_name" placeholder="Enter your full name" name="name"
                                    class="form-input" required>
                            </div>

                            <div class="form-group">
                                <label for="reg_email" class="form-label">Email</label>
                                <input type="email" id="reg_email" placeholder="Enter your email" name="email"
                                    class="form-input" required>
                            </div>

                            <div class="form-group-row">
                                <div class="form-group">
                                    <label for="reg_phone" class="form-label">Phone</label>
                                    <input type="text" id="reg_phone" placeholder="Enter your phone number" name="phone"
                                        class="form-input">
                                </div>

                                <div class="form-group">
                                    <label for="reg_nid" class="form-label">National ID</label>
                                    <input type="text" id="reg_nid" placeholder="Enter your national ID" name="national_id"
                                        class="form-input">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="reg_password" class="form-label">Password</label>
                                <input type="password" id="reg_password" placeholder="Enter your password" name="password"
                                    class="form-input" required>
                            </div>

                            <div class="form-group">
                                <label for="reg_role" class="form-label">Role</label>
                                <select id="reg_role" name="role" class="form-select" required>
                                    <option value="cashier" selected>Cashier</option>
                                    <option value="manager">Manager</option>
                                    <option value="admin">Admin</option>
                                    <option value="accountant">Accountant</option>
                                    <option value="storekeeper">Storekeeper</option>
                                </select>
                            </div>

                            <div class="button-group">
                                <button type="submit" class="btn-primary">Create Account</button>
                                <button type="button" class="btn-secondary" onclick="showLogin()">Back to Login</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>



    </div>

    <script>
        function showRegister() {
            document.getElementById('loginSection').classList.add('hidden');
            document.getElementById('registerSection').classList.remove('hidden');
        }

        function showLogin() {
            document.getElementById('registerSection').classList.add('hidden');
            document.getElementById('loginSection').classList.remove('hidden');
        }
    </script>
</body>

</html>