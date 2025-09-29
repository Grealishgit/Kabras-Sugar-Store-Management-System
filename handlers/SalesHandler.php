<?php
require_once __DIR__ . '/../config/database.php';

class SalesHandler
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    // Get total sales (all time)
    public function getTotalSales()
    {
        $stmt = $this->db->prepare("SELECT SUM(total_amount) AS total_sales FROM sales");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_sales'] ?? 0;
    }

    // Get today's sales (all time)
    public function getTodaySalesAll()
    {
        $stmt = $this->db->prepare("SELECT SUM(total_amount) AS total_sales FROM sales WHERE DATE(sale_date) = CURDATE()");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_sales'] ?? 0;
    }

    // Get all products (active)
    public function getProducts()
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE status = 'active' ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add a sale (single product)
    public function addSale($cashierId, $productId, $quantity)
    {
        $stmt = $this->db->prepare("SELECT price FROM products WHERE id = :pid");
        $stmt->execute([':pid' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) return false;

        $unitPrice = $product['price'];
        $totalAmount = $unitPrice * $quantity;

        $stmt = $this->db->prepare("INSERT INTO sales (user_id, total_amount) VALUES (:uid, :total)");
        $stmt->execute([':uid' => $cashierId, ':total' => $totalAmount]);
        $saleId = $this->db->lastInsertId();
        if (!$saleId) return false;

        $stmt = $this->db->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (:sid, :pid, :qty, :price)");
        $stmt->execute([
            ':sid' => $saleId,
            ':pid' => $productId,
            ':qty' => $quantity,
            ':price' => $unitPrice
        ]);

        $stmt = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid");
        $stmt->execute([':qty' => $quantity, ':pid' => $productId]);

        return $saleId;
    }

    // Get sales for a cashier
    public function getSalesByCashier($cashierId)
    {
        $stmt = $this->db->prepare("
            SELECT s.*, si.product_id, si.quantity, si.unit_price, p.name AS product_name
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN products p ON si.product_id = p.id
            WHERE s.user_id = :uid
            ORDER BY s.sale_date DESC
        ");
        $stmt->execute([':uid' => $cashierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get today's sales stats for a cashier
    public function getTodaySales($cashierId)
    {
        $stats = [
            'total_transactions' => 0,
            'total_quantity' => 0,
            'avg_unit_price' => 0,
            'total_amount' => 0
        ];

        $stmt = $this->db->prepare("SELECT s.id FROM sales s WHERE s.user_id = :uid AND DATE(s.sale_date) = CURDATE()");
        $stmt->execute([':uid' => $cashierId]);
        $sales = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stats['total_transactions'] = count($sales);

        if ($sales) {
            $in = implode(',', array_map('intval', $sales));
            $q = $this->db->query("SELECT SUM(quantity) as total_quantity, AVG(unit_price) as avg_unit_price FROM sale_items WHERE sale_id IN ($in)");
            $row = $q->fetch(PDO::FETCH_ASSOC);
            $stats['total_quantity'] = $row['total_quantity'] ?? 0;
            $stats['avg_unit_price'] = $row['avg_unit_price'] ?? 0;

            $q2 = $this->db->query("SELECT SUM(total_amount) as total_amount FROM sales WHERE id IN ($in)");
            $row2 = $q2->fetch(PDO::FETCH_ASSOC);
            $stats['total_amount'] = $row2['total_amount'] ?? 0;
        }

        return $stats;
    }

    // Get weekly sales stats for a cashier
    public function getWeeklySales($cashierId)
    {
        $stats = [
            'total_transactions' => 0,
            'total_quantity' => 0,
            'avg_unit_price' => 0,
            'total_amount' => 0
        ];

        $stmt = $this->db->prepare("
            SELECT s.id 
            FROM sales s 
            WHERE s.user_id = :uid AND s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ");
        $stmt->execute([':uid' => $cashierId]);
        $sales = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stats['total_transactions'] = count($sales);

        if ($sales) {
            $in = implode(',', array_map('intval', $sales));
            $q = $this->db->query("SELECT SUM(quantity) as total_quantity, AVG(unit_price) as avg_unit_price FROM sale_items WHERE sale_id IN ($in)");
            $row = $q->fetch(PDO::FETCH_ASSOC);
            $stats['total_quantity'] = $row['total_quantity'] ?? 0;
            $stats['avg_unit_price'] = $row['avg_unit_price'] ?? 0;

            $q2 = $this->db->query("SELECT SUM(total_amount) as total_amount FROM sales WHERE id IN ($in)");
            $row2 = $q2->fetch(PDO::FETCH_ASSOC);
            $stats['total_amount'] = $row2['total_amount'] ?? 0;
        }

        return $stats;
    }

    // Get monthly sales stats for a cashier
    public function getMonthlySales($cashierId)
    {
        $stats = [
            'total_transactions' => 0,
            'total_quantity' => 0,
            'avg_unit_price' => 0,
            'total_amount' => 0
        ];

        $stmt = $this->db->prepare("
            SELECT s.id 
            FROM sales s 
            WHERE s.user_id = :uid 
            AND MONTH(s.sale_date) = MONTH(CURDATE()) 
            AND YEAR(s.sale_date) = YEAR(CURDATE())
        ");
        $stmt->execute([':uid' => $cashierId]);
        $sales = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stats['total_transactions'] = count($sales);

        if ($sales) {
            $in = implode(',', array_map('intval', $sales));
            $q = $this->db->query("SELECT SUM(quantity) as total_quantity, AVG(unit_price) as avg_unit_price FROM sale_items WHERE sale_id IN ($in)");
            $row = $q->fetch(PDO::FETCH_ASSOC);
            $stats['total_quantity'] = $row['total_quantity'] ?? 0;
            $stats['avg_unit_price'] = $row['avg_unit_price'] ?? 0;

            $q2 = $this->db->query("SELECT SUM(total_amount) as total_amount FROM sales WHERE id IN ($in)");
            $row2 = $q2->fetch(PDO::FETCH_ASSOC);
            $stats['total_amount'] = $row2['total_amount'] ?? 0;
        }

        return $stats;
    }

    // Get recent sales (last 10 sales) for a cashier
    public function getRecentSales($cashierId, $limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT s.*, si.product_id, si.quantity, si.unit_price, p.name AS product_name
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN products p ON si.product_id = p.id
            WHERE s.user_id = :uid
            ORDER BY s.sale_date DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':uid', $cashierId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Process a sale (multiple products)
    public function processSale($customerId, $cashierId, $items)
    {
        if (!is_array($items)) return false;
        $customerId = $customerId ? intval($customerId) : 1;
        $totalAmount = 0;
        $saleItems = [];

        foreach ($items as $productId => $qty) {
            $qty = intval($qty);
            if ($qty > 0) {
                $stmt = $this->db->prepare("SELECT price FROM products WHERE id = :pid");
                $stmt->execute([':pid' => $productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($product) {
                    $unitPrice = $product['price'];
                    $totalAmount += $unitPrice * $qty;
                    $saleItems[] = [
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice
                    ];
                }
            }
        }

        if (empty($saleItems)) return false;

        $stmt = $this->db->prepare("INSERT INTO sales (customer_id, user_id, total_amount) VALUES (:cid, :uid, :total)");
        $stmt->execute([':cid' => $customerId, ':uid' => $cashierId, ':total' => $totalAmount]);
        $saleId = $this->db->lastInsertId();
        if (!$saleId) return false;

        foreach ($saleItems as $item) {
            $stmt = $this->db->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (:sid, :pid, :qty, :price)");
            $stmt->execute([
                ':sid' => $saleId,
                ':pid' => $item['product_id'],
                ':qty' => $item['quantity'],
                ':price' => $item['unit_price']
            ]);

            $stmt = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid");
            $stmt->execute([':qty' => $item['quantity'], ':pid' => $item['product_id']]);
        }

        return $saleId;
    }
}
