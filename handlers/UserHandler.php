<?php // UserHandler.php - Provides user data for staff visualization
require_once __DIR__ . '/../app/models/User.php';

class UserHandler
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function getAllUsers()
    {
        return $this->userModel->getAllUsers();
    }

    public function getUserById($id)
    {
        return $this->userModel->findById($id);
    }
    public function addUser($data)
    {
        return $this->userModel->createUser($data);
    }
    public function updateUser($id, $data)
    {
        return $this->userModel->updateUser($id, $data);
    }
    public function deleteUser($id)
    {
        return $this->userModel->deleteUser($id);
    }
}

// AJAX/REST handler
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $handler = new UserHandler();
    if (isset($_GET['id'])) {
        $user = $handler->getUserById($_GET['id']);
        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'error' => 'User not found']);
        }
    } else {
        $users = $handler->getAllUsers();
        echo json_encode(['success' => true, 'users' => $users]);
    }
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $handler = new UserHandler();
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $required = ['name', 'email', 'role', 'password'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'error' => 'Missing field: ' . $field]);
                exit();
            }
        }
        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'] ?? '',
            'national_id' => $_POST['national_id'] ?? '',
            'role' => $_POST['role'],
            'password' => $_POST['password']
        ];
        $id = $handler->addUser($data);
        echo json_encode(['success' => !!$id, 'id' => $id]);
        exit();
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Missing user id']);
            exit();
        }
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'national_id' => $_POST['national_id'] ?? '',
            'role' => $_POST['role'] ?? '',
        ];
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }
        $ok = $handler->updateUser($id, $data);
        echo json_encode(['success' => $ok]);
        exit();
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Missing user id']);
            exit();
        }
        $ok = $handler->deleteUser($id);
        echo json_encode(['success' => $ok]);
        exit();
    }
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit();
}