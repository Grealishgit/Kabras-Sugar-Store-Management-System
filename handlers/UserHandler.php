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

// Handle HTTP requests
$userHandler = new UserHandler();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $user = $userHandler->getUserById($_GET['id']);
        if ($user) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'User not found']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'national_id' => $_POST['national_id'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? ''
            ];
            $result = $userHandler->addUser($data);
            header('Content-Type: application/json');
            if ($result) {
                echo json_encode(['success' => true, 'user_id' => $result]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to add user']);
            }
            break;

        case 'update':
            $id = $_POST['id'] ?? '';
            $data = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'national_id' => $_POST['national_id'] ?? '',
                'role' => $_POST['role'] ?? ''
            ];
            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }
            $result = $userHandler->updateUser($id, $data);
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            break;

        case 'delete':
            $id = $_POST['id'] ?? '';
            $result = $userHandler->deleteUser($id);
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            break;

        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}