<?php
// UserHandler.php - Handles AJAX requests for user CRUD operations
require_once __DIR__ . '/../app/models/User.php';
header('Content-Type: application/json');

$userModel = new User();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Get user info by ID
    $user = $userModel->findById($_GET['id']);
    if ($user) {
        unset($user['password']); // Remove password
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'error' => 'User not found']);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update' && isset($_POST['id'])) {
        // Update user info
        $id = $_POST['id'];
        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'national_id' => $_POST['national_id'],
            'role' => $_POST['role']
        ];
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }
        $result = $userModel->updateUser($id, $data);
        echo json_encode(['success' => $result]);
        exit();
    }
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        // Delete user
        $result = $userModel->deleteUser($_POST['id']);
        echo json_encode(['success' => $result]);
        exit();
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
