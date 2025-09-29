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
}