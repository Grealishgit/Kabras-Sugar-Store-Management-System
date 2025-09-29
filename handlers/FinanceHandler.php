<?php
require_once __DIR__ . '/../config/database.php';

class FinanceHandler
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    /**
     * Add a new expense to the database
     * @param string $expense_date
     * @param string $vendor
     * @param string $category
     * @param float $amount
     * @param int $recorded_by
     * @return bool
     */
    public function addExpense($expense_date, $vendor, $category, $amount, $recorded_by)
    {
        if ($amount > 0 && $category) {
            $stmt = $this->db->prepare("INSERT INTO expenses (date, vendor, category, amount, recorded_by) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$expense_date, $vendor, $category, $amount, $recorded_by]);
        }
        return false;
    }
}
