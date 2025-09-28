<?php
require_once __DIR__ . '/../config/database.php';

class SalesHandler
{
    private $db;
    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    // Get all products (active)
    public function getProducts()
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE status = 'active' ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add a sale (single product, simple version)
    public function addSale($cashierId, $productId, $quantity)
    {
        // Get product price
        $stmt = $this->db->prepare("SELECT price FROM products WHERE id = :pid");
        $stmt->execute([':pid' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) return false;
        $unitPrice = $product['price'];
        $totalAmount = $unitPrice * $quantity;

        // Insert sale
        $stmt = $this->db->prepare("INSERT INTO sales (user_id, total_amount) VALUES (:uid, :total)");
        $stmt->execute([':uid' => $cashierId, ':total' => $totalAmount]);
        $saleId = $this->db->lastInsertId();
        if (!$saleId) return false;

        // Insert sale item
        $stmt = $this->db->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (:sid, :pid, :qty, :price)");
        $stmt->execute([
            ':sid' => $saleId,
            ':pid' => $productId,
            ':qty' => $quantity,
            ':price' => $unitPrice
        ]);

        // Update product stock
        $stmt = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid");
        $stmt->execute([':qty' => $quantity, ':pid' => $productId]);

        return $saleId;
    }

    // Get sales for a cashier
    public function getSalesByCashier($cashierId)
    {
        $stmt = $this->db->prepare("SELECT s.*, si.product_id, si.quantity, si.unit_price, p.name AS product_name
			FROM sales s
			JOIN sale_items si ON s.id = si.sale_id
			JOIN products p ON si.product_id = p.id
			WHERE s.user_id = :uid
			ORDER BY s.sale_date DESC");
        $stmt->execute([':uid' => $cashierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Get today's sales for a cashier
    public function getTodaySales($cashierId)
    {
        $stmt = $this->db->prepare("SELECT SUM(total_amount) as total FROM sales WHERE user_id = :uid AND DATE(sale_date) = CURDATE()");
        $stmt->execute([':uid' => $cashierId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['total'] : 0;
    }

    // Get weekly sales for a cashier (last 7 days)
    public function getWeeklySales($cashierId)
    {
        $stmt = $this->db->prepare("SELECT SUM(total_amount) as total FROM sales WHERE user_id = :uid AND sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stmt->execute([':uid' => $cashierId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['total'] : 0;
    }

    // Get monthly sales for a cashier (current month)
    public function getMonthlySales($cashierId)
    {
        $stmt = $this->db->prepare("SELECT SUM(total_amount) as total FROM sales WHERE user_id = :uid AND MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())");
        $stmt->execute([':uid' => $cashierId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['total'] : 0;
    }

    // Get recent sales (last 10 sales) for a cashier
    public function getRecentSales($cashierId, $limit = 10)
    {
        $stmt = $this->db->prepare("SELECT s.*, si.product_id, si.quantity, si.unit_price, p.name AS product_name
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN products p ON si.product_id = p.id
            WHERE s.user_id = :uid
            ORDER BY s.sale_date DESC
            LIMIT :lim");
        $stmt->bindValue(':uid', $cashierId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Process a sale (multiple products)
    public function processSale($cashierId, $items)
    {
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

        // Insert sale
        $stmt = $this->db->prepare("INSERT INTO sales (user_id, total_amount) VALUES (:uid, :total)");
        $stmt->execute([':uid' => $cashierId, ':total' => $totalAmount]);
        $saleId = $this->db->lastInsertId();
        if (!$saleId) return false;

        // Insert sale items
        foreach ($saleItems as $item) {
            $stmt = $this->db->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES (:sid, :pid, :qty, :price)");
            $stmt->execute([
                ':sid' => $saleId,
                ':pid' => $item['product_id'],
                ':qty' => $item['quantity'],
                ':price' => $item['unit_price']
            ]);
            // Update product stock
            $stmt = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid");
            $stmt->execute([':qty' => $item['quantity'], ':pid' => $item['product_id']]);
        }
        return $saleId;
    }
}
