<?php
require_once __DIR__ . '/../config/Database.php';

class PaymentsHandler
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Record a new payment (can include sale reference)
     */
    public function recordPayment($sale_id, $customer_id, $amount, $method, $reference_number, $user_id, $notes = null)
    {
        $sql = "INSERT INTO payments 
            (sale_id, customer_id, amount, method, reference_number, user_id, notes, status, payment_date) 
            VALUES (:sale_id, :customer_id, :amount, :method, :reference_number, :user_id, :notes, 'completed', NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':sale_id' => $sale_id,
            ':customer_id' => $customer_id,
            ':amount' => $amount,
            ':method' => $method,
            ':reference_number' => $reference_number,
            ':user_id' => $user_id,
            ':notes' => $notes
        ]);
    }

    /**
     * Add a payment without sale reference (direct payment from cashier modal)
     */
    public function addPayment($customer_id, $amount, $method, $user_id, $sale_id = null, $reference_number = null, $notes = null)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO payments 
            (customer_id, amount, method, user_id, sale_id, reference_number, notes, status, payment_date) 
            VALUES (:customer_id, :amount, :method, :user_id, :sale_id, :reference_number, :notes, 'completed', NOW())
        ");
        return $stmt->execute([
            ':customer_id' => $customer_id,
            ':amount' => $amount,
            ':method' => $method,
            ':user_id' => $user_id,
            ':sale_id' => $sale_id,
            ':reference_number' => $reference_number,
            ':notes' => $notes
        ]);
    }

    /**
     * Get all payments
     */
    public function getAllPayments()
    {
        $sql = "SELECT p.*, 
                       c.name AS customer_name, 
                       u.name AS cashier_name, 
                       s.product_name, s.total_amount AS sale_total
                FROM payments p
                LEFT JOIN customers c ON p.customer_id = c.id
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN sales s ON p.sale_id = s.id
                ORDER BY p.payment_date DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payments by user (Cashier)
     */
    public function getPaymentsByUser($user_id)
    {
        $sql = "SELECT p.*, 
                       c.name AS customer_name, 
                       GROUP_CONCAT(pr.name SEPARATOR ', ') AS product_names, 
                       s.total_amount AS sale_total
                FROM payments p
                LEFT JOIN customers c ON p.customer_id = c.id
                LEFT JOIN sales s ON p.sale_id = s.id
                LEFT JOIN sale_items si ON s.id = si.sale_id
                LEFT JOIN products pr ON si.product_id = pr.id
                WHERE p.user_id = :user_id
                GROUP BY p.id, c.name, s.total_amount
                ORDER BY p.payment_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payments for a specific sale
     */
    public function getPaymentsBySale($sale_id)
    {
        $sql = "SELECT p.*, c.name AS customer_name, u.name AS cashier_name
                FROM payments p
                LEFT JOIN customers c ON p.customer_id = c.id
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.sale_id = :sale_id
                ORDER BY p.payment_date ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':sale_id' => $sale_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total payments for a customer
     */
    public function getCustomerPayments($customer_id)
    {
        $sql = "SELECT SUM(amount) AS total_paid 
                FROM payments 
                WHERE customer_id = :customer_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':customer_id' => $customer_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all customers
     */
    public function getCustomers()
    {
        $sql = "SELECT id, name FROM customers ORDER BY name ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
