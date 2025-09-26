<?php
// User model for Siritamu Resort
require_once __DIR__ . '/../../config/database.php';

class User
{
    public $id;
    public $name;
    public $email;
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

    // Create a new guest user
    public function createGuest($data)
    {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'guest')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
    }

    // Create a staff or admin user
    public function createUser($data)
    {
        try {
            error_log("USER MODEL: Creating user with data: " . json_encode(array_merge($data, ['password' => '[HIDDEN]'])));


            $sql = "INSERT INTO users (name, email, password, role, designation, staff_number) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);

            $params = [
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'],
                $data['designation'] ?? null,
                $data['staff_number'] ?? null
            ];

            error_log("USER MODEL: SQL query: " . $sql);
            error_log("USER MODEL: Parameters: " . json_encode($params));

            $result = $stmt->execute($params);
            error_log("USER MODEL: Execute result: " . json_encode($result));

            if ($result) {
                $lastId = $this->db->lastInsertId();
                error_log("USER MODEL: Last insert ID: " . $lastId);
                return $lastId;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("USER MODEL: SQL Error: " . json_encode($errorInfo));
                return false;
            }
        } catch (Exception $e) {
            error_log("USER MODEL: Exception in createUser: " . $e->getMessage());
            error_log("USER MODEL: Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    // Generate unique staff number
    public function generateStaffNumber()
    {
        try {
            error_log("USER MODEL: Generating staff number...");
            $attempts = 0;
            $maxAttempts = 10;

            do {
                $attempts++;
                // Generate random 4-digit number
                $randomDigits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $staffNumber = 'SR' . $randomDigits . '2025';

                error_log("USER MODEL: Generated candidate: " . $staffNumber . " (attempt $attempts)");

                // Check if this staff number already exists
                $sql = "SELECT COUNT(*) FROM users WHERE staff_number = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$staffNumber]);
                $exists = $stmt->fetchColumn() > 0;

                error_log("USER MODEL: Staff number exists check: " . ($exists ? 'YES' : 'NO'));

                if ($attempts >= $maxAttempts) {
                    error_log("USER MODEL: Max attempts reached for staff number generation");
                    break;
                }
            } while ($exists);

            error_log("USER MODEL: Final staff number: " . $staffNumber);
            return $staffNumber;
        } catch (Exception $e) {
            error_log("USER MODEL: Exception in generateStaffNumber: " . $e->getMessage());
            // Fallback to timestamp-based number
            $fallback = 'SR' . date('md') . '2025';
            error_log("USER MODEL: Using fallback staff number: " . $fallback);
            return $fallback;
        }
    }

    // Check if staff number exists
    public function staffNumberExists($staffNumber)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE staff_number = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$staffNumber]);
        return $stmt->fetchColumn() > 0;
    }

    // Find user by email
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // Find user by email or username (for compatibility)
    public function findByIdentifier($identifier)
    {
        $sql = "SELECT * FROM users WHERE email = ? OR name = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch();
    }

    // Verify login for guests (from database)
    public function verifyGuestLogin($email, $password)
    {
        $user = $this->findByEmail($email);
        if ($user && $user['role'] === 'guest' && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Verify login for staff and admin (check database)
    public function verifyStaffLogin($identifier, $password)
    {
        // Check database for staff/admin users
        $user = $this->findByEmail($identifier);
        if ($user && ($user['role'] === 'staff' || $user['role'] === 'admin') && password_verify($password, $user['password'])) {
            return $user;
        }

        // Fallback to hardcoded admin for initial setup (remove after creating real admin)
        if ($identifier === 'wafula' && $password === 'admin') {
            return [
                'id' => 999,
                'name' => 'System Admin',
                'email' => 'admin@siritamu.com',
                'role' => 'admin',
                'designation' => 'system_admin',
                'staff_number' => 'ADMIN001'
            ];
        }

        return false;
    }

    // General login method that handles all user types
    public function verifyLogin($identifier, $password, $role = 'guest')
    {
        // First try to find the user in the database
        $user = $this->findByIdentifier($identifier);

        // If user exists and password matches
        if ($user && password_verify($password, $user['password'])) {
            // Check if the role matches what was requested
            if ($user['role'] === $role) {
                return $user;
            }
        }

        // Special case for hardcoded admin
        if ($role === 'admin' && $identifier === 'wafula' && $password === 'admin') {
            return [
                'id' => 999,
                'name' => 'System Admin',
                'email' => 'admin@siritamu.com',
                'role' => 'admin',
                'designation' => 'system_admin',
                'staff_number' => 'ADMIN001'
            ];
        }

        return false;
    }

    // Get all guests
    public function getAllGuests()
    {
        $sql = "SELECT * FROM users WHERE role = 'guest' ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }



    // Check if email exists
    public function emailExists($email)
    {
        $user = $this->findByEmail($email);
        return $user !== false;
    }

    public function getPdo()
    {
        return $this->db;
    }

    /**
     * Get all staff members for dropdown selection
     */
    public function getStaffMembers()
    {
        try {
            $sql = "SELECT id, name, designation FROM users WHERE role = 'staff' ORDER BY name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching staff members: " . $e->getMessage());
            return [];
        }
    }
}