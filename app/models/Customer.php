<?php require_once __DIR__ . '/../../config/database.php';

class Customer
{
    private $conn;
    private $table = 'customers';

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAllCustomers()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCustomerById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createCustomer($data)
    {
        $query = "INSERT INTO " . $this->table . " 
(customer_code, name, email, phone, address, town, type, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // Generate customer code if not provided
        $customer_code = $data['customer_code'] ?? $this->generateCustomerCode();

        return $stmt->execute([
            $customer_code,
            $data['name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['town'] ?? null,
            $data['type'] ?? 'individual',
            $data['status'] ?? 'active',
            $data['notes'] ?? null
        ]);
    }

    public function updateCustomer($id, $data)
    {
        $query = "UPDATE " . $this->table . " 
SET name=?,
        email=?,
        phone=?,
        address=?,
        town=?,
        type=?,
        status=?,
        notes=? WHERE id=?";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            $data['name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['town'] ?? null,
            $data['type'] ?? 'individual',
            $data['status'] ?? 'active',
            $data['notes'] ?? null,
            $id
        ]);
    }

    public function deleteCustomer($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function searchCustomers($search)
    {
        $query = "SELECT * FROM " . $this->table . " 
WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? OR customer_code LIKE ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filterCustomers($type = null, $status = null)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE 1=1";
        $params = [];

        if ($type) {
            $query .= " AND type = ?";
            $params[] = $type;
        }

        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        $query .= " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCustomerStats()
    {
        $stats = [];

        // Total customers
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Active customers
        $query = "SELECT COUNT(*) as active FROM " . $this->table . " WHERE status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['active'] = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

        // Individual customers
        $query = "SELECT COUNT(*) as individual FROM " . $this->table . " WHERE type = 'individual'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['individual'] = $stmt->fetch(PDO::FETCH_ASSOC)['individual'];

        // Business customers
        $query = "SELECT COUNT(*) as business FROM " . $this->table . " WHERE type = 'business'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['business'] = $stmt->fetch(PDO::FETCH_ASSOC)['business'];

        return $stats;
    }

    private function generateCustomerCode()
    {
        $prefix = 'CUST';
        $timestamp = time();
        $random = rand(100, 999);
        return $prefix . $timestamp . $random;
    }
}
