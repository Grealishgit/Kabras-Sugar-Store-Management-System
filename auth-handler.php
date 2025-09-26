<?php
require_once 'handlers/AuthHandler.php';
$authHandler = new AuthHandler();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'login') {
        $email = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        $user = $authHandler->handleLogin($email, $password);
        if ($user['success']) {
            // Check role match
            if ($role && $user['data']['user']['role'] !== $role) {
                header('Location: login.php?error=Role mismatch.');
                exit();
            }
            // Redirect to dashboard
            $redirect = $user['data']['redirect'] ?? 'index.php';
            header('Location: ' . $redirect);
            exit();
        } else {
            header('Location: login.php?error=' . urlencode($user['error']));
            exit();
        }
    } elseif ($action === 'register') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $nationalId = $_POST['national_id'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'staff';

        $result = $authHandler->handleRegistration($name, $email, $phone, $nationalId, $password, $role);
        if ($result['success']) {
            header('Location: login.php?success=' . urlencode($result['data']['message']));
            exit();
        } else {
            header('Location: login.php?error=' . urlencode($result['error']));
            exit();
        }
    }
}
// Default: redirect to login
header('Location: login.php');
exit();
