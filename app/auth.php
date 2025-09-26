<?php
// User model for Kabras Sugar Store
require_once __DIR__ . '/../../config/database.php';

class User
{
    public $id;
    public $name;
    public $email;
    public $phone;
    public $national_id;
    public $password;
    public $role;
    public $created_at;
    public $designation;
    public $staff_number;

    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    /** Create a staff / manager / admin user */
    public function createUser($data)
    {
        try {
            $sql = "INSERT INTO users (name, email, phone, national_id, password, role, designation, staff_number)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);

            $params = [
                $data['name'],
                $data['email'],
                $data['phone'] ?? null,
                $data['national_id'] ?? null,
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'],
                $data['designation'] ?? null,
                $data['staff_number'] ?? null
            ];

            $result = $stmt->execute($params);
            if ($result) {
                return $this->db->lastInsertId();
            } else {
                error_log("User::createUser SQL Error: " . json_encode($stmt->errorInfo()));
                return false;
            }
        } catch (Exception $e) {
            error_log("User::createUser Exception: " . $e->getMessage());
            return false;
        }
    }

    /** Generate unique staff number */
    public function generateStaffNumber()
    {
        $maxAttempts = 10;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $randomDigits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $staffNumber = 'KB' . $randomDigits . date('y'); // e.g. KB042520

            $sql = "SELECT COUNT(*) FROM users WHERE staff_number = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$staffNumber]);
            if ($stmt->fetchColumn() == 0) {
                return $staffNumber;
            }
        }
        // fallback
        return 'KB' . time();
    }

    /** Check if email exists */
    public function emailExists($email)
    {
        return $this->findByEmail($email) !== false;
    }

    /** Find user by email */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Find user by email or name */
    public function findByIdentifier($identifier)
    {
        $sql = "SELECT * FROM users WHERE email = ? OR name = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Verify login for staff/manager/admin */
    public function verifyLogin($identifier, $password)
    {
        $user = $this->findByIdentifier($identifier);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        // optional hardcoded admin for first-time setup
        if ($identifier === 'admin' && $password === 'admin') {
            return [
                'id' => 0,
                'name' => 'System Admin',
                'email' => 'admin@kabras.com',
                'role' => 'admin',
                'designation' => 'System Admin',
                'staff_number' => 'ADMIN001'
            ];
        }
        return false;
    }

    /** Get all staff */
    public function getStaffMembers()
    {
        $sql = "SELECT id, name, designation FROM users WHERE role = 'staff' ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPdo()
    {
        return $this->db;
    }
}