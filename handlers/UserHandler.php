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

