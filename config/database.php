<?php
// Database configuration for Sheywe Hospital

class Database
{
    private $host = 'localhost';
    private $db   = 'kabras_store';
    private $user = 'root';
    private $pass = 'Hunter42.';
    private $charset = 'utf8mb4';

    public $pdo;

    public function connect()
    {
        if ($this->pdo) {
            return $this->pdo;
        }
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return $this->pdo;
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
}

// Test connection in console
if (php_sapi_name() === 'cli') {
    $db = new Database();
    $conn = $db->connect();
    if ($conn) {
        echo "Database connected!\n";
    }
}
