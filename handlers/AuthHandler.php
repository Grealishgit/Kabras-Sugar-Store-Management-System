<?php

/**
 * Authentication Handler for Kabras Sugar Store
 * Handles login, registration (only by admin), logout, and session management
 */

require_once __DIR__ . '/../app/models/User.php';

class AuthHandler
{
    private $userModel;
    private $sessionTimeout = 3600; // 1 hour

    public function __construct()
    {
        $this->userModel = new User();
        $this->startSession();
        // Auto-create default admin if not present
        $defaultAdminEmail = 'admin@kabrasugar.com';
        if (!$this->userModel->findByEmail($defaultAdminEmail)) {
            $adminData = [
                'name' => 'Default Admin',
                'email' => $defaultAdminEmail,
                'phone' => '',
                'national_id' => '',
                'password' => 'hunter123', // pass plain password, User model will hash
                'role' => 'admin'
            ];
            $this->userModel->createUser($adminData);
        }
    }

    /**
     * Start secure session
     */
    private function startSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', 0); // set 1 if HTTPS

            session_start();

            if (!isset($_SESSION['initiated'])) {
                session_regenerate_id(true);
                $_SESSION['initiated'] = true;
            }
        }
    }

    /**
     * Handle user login
     */
    public function handleLogin($email, $password)
    {
        if (empty($email) || empty($password)) {
            return $this->errorResponse("Please enter both email and password.");
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            $this->createUserSession($user);

            return $this->successResponse([
                "message" => "Welcome back, {$user['name']}!",
                "user" => [
                    "id" => $user['id'],
                    "name" => $user['name'],
                    "email" => $user['email'],
                    "role" => $user['role']
                ],
                "redirect" => $this->getRedirectUrl($user['role'])
            ]);
        }

        return $this->errorResponse("Invalid email or password.");
    }

    /**
     * Handle user registration (only admin can create staff/managers)
     */
    public function handleRegistration($name, $email, $phone, $nationalId, $password, $role = 'staff')
    {
        // Only allow staff/manager creation by admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            return $this->errorResponse("Only administrators can create new accounts.");
        }

        if (empty($name) || empty($email) || empty($password)) {
            return $this->errorResponse("Name, email, and password are required.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->errorResponse("Invalid email format.");
        }

        if ($this->userModel->emailExists($email)) {
            return $this->errorResponse("Email already exists. Use another one.");
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $userData = [
            "name" => $name,
            "email" => strtolower($email),
            "phone" => $phone,
            "national_id" => $nationalId,
            "password" => $hashedPassword,
            "role" => $role
        ];

        $userId = $this->userModel->createUser($userData);

        if ($userId) {
            return $this->successResponse([
                "message" => "Account for {$name} ({$role}) created successfully!",
                "user_id" => $userId
            ]);
        }

        return $this->errorResponse("Account creation failed. Try again.");
    }

    /**
     * Handle logout
     */
    public function handleLogout()
    {
        session_unset();
        session_destroy();
        return $this->successResponse([
            "message" => "You have been logged out.",
            "redirect" => "index.php"
        ]);
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']) &&
            (time() - $_SESSION['login_time']) < $this->sessionTimeout;
    }

    /**
     * Get current user
     */
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) return null;

        return [
            "id" => $_SESSION['user_id'],
            "name" => $_SESSION['user_name'],
            "email" => $_SESSION['user_email'],
            "role" => $_SESSION['user_role']
        ];
    }

    /**
     * Create user session
     */
    private function createUserSession($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();

        session_regenerate_id(true);
    }

    /**
     * Get redirect URL by role
     */
    private function getRedirectUrl($role)
    {
        switch ($role) {
            case 'admin':
                return "admin/dashboard.php";
            case 'manager':
                return "manager/dashboard.php";
            case 'staff':
                return "staff/dashboard.php";
            default:
                return "index.php";
        }
    }

    /**
     * Success response
     */
    private function successResponse($data)
    {
        return ["success" => true, "data" => $data];
    }

    /**
     * Error response
     */
    private function errorResponse($message)
    {
        return ["success" => false, "error" => $message];
    }
}