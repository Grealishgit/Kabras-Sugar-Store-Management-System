<?php
// User.php
require_once __DIR__ . '../../../config/database.php';

class User
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    /**
     * Update user info (admin only)
     */
    public function updateUser($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params[':name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $fields[] = 'email = :email';
            $params[':email'] = $data['email'];
        }
        if (isset($data['phone'])) {
            $fields[] = 'phone = :phone';
            $params[':phone'] = $data['phone'];
        }
        if (isset($data['national_id'])) {
            $fields[] = 'national_id = :national_id';
            $params[':national_id'] = $data['national_id'];
        }
        if (isset($data['role'])) {
            $fields[] = 'role = :role';
            $params[':role'] = $data['role'];
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = 'password = :password';
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (empty($fields)) return false;

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Create new user (admin, manager, or staff)
     */
    public function createUser($data)
    {
        try {
            $sql = "INSERT INTO users (name, email, phone, national_id, password, role) 
                    VALUES (:name, :email, :phone, :national_id, :password, :role)";
            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':name' => $data['name'],
                ':email' => $data['email'],
                ':phone' => $data['phone'],
                ':national_id' => $data['national_id'],
                ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
                ':role' => $data['role']
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Create user failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find user by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if email already exists
     */
    public function emailExists($email)
    {
        return $this->findByEmail($email) !== false;
    }

    /**
     * Verify login
     */
    public function verifyLogin($email, $password)
    {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    /**
     * Get all users (optional, for admin panel)
     */
    public function getAllUsers()
    {
        $sql = "SELECT id, name, email, phone, national_id, role, created_at, last_login FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete user (admin only)
     */
    public function deleteUser($id)
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Update user role (admin only)
     */
    public function updateRole($id, $role)
    {
        $sql = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':role' => $role, ':id' => $id]);
    }

    /**
     * Update last_login timestamp for user
     */
    public function updateLastLogin($userId)
    {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }
}