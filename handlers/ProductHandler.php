<?php
require_once __DIR__ . '/../config/database.php';

class ProductHandler
{
    private $db;
    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    // Add product
    public function addProduct($data)
    {
        $sql = "INSERT INTO products (name, category, description, price, stock_quantity, unit, batch_number, expiry_date, production_date, supplier, status, created_by) VALUES (:name, :category, :description, :price, :stock_quantity, :unit, :batch_number, :expiry_date, :production_date, :supplier, :status, :created_by)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':category' => $data['category'] ?? null,
            ':description' => $data['description'] ?? null,
            ':price' => $data['price'],
            ':stock_quantity' => $data['stock_quantity'] ?? 0,
            ':unit' => $data['unit'] ?? null,
            ':batch_number' => $data['batch_number'] ?? null,
            ':expiry_date' => $data['expiry_date'] ?? null,
            ':production_date' => $data['production_date'] ?? null,
            ':supplier' => $data['supplier'] ?? null,
            ':status' => $data['status'] ?? 'active',
            ':created_by' => $data['created_by'] ?? null
        ]);
    }

    // Get all products
    public function getAllProducts()
    {
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Edit product
    public function updateProduct($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];
        foreach (
            [
                'name',
                'category',
                'description',
                'price',
                'stock_quantity',
                'unit',
                'batch_number',
                'expiry_date',
                'production_date',
                'supplier',
                'status'
            ] as $field
        ) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $sql = 'UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // Delete product
    public function deleteProduct($id)
    {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Get product by ID
    public function getProductById($id)
    {
        $sql = "SELECT * FROM products WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // In ProductHandler.php
    public function getProductsStock($lowStockThreshold = 10)
    {
        $stmt = $this->db->prepare("SELECT *, 
        CASE WHEN stock_quantity <= :threshold THEN 1 ELSE 0 END AS low_stock 
        FROM products 
        ORDER BY name ASC");
        $stmt->execute([':threshold' => $lowStockThreshold]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
