<?php
// api/get_users.php
require_once '../handlers/UserHandler.php';
header('Content-Type: application/json');
$userHandler = new UserHandler();
$users = $userHandler->getAllUsers();
echo json_encode([
    'success' => true,
    'users' => $users
]);
