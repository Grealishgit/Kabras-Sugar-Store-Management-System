<?php
// Database configuration for Kabras Sugar Store Management System

class Database
{
    private $host;
    private $db;
    private $user;
    private $pass;
    private $charset = 'utf8mb4';

    public $pdo;

    public function __construct()
    {
        // Use environment variables if available (for Docker), otherwise use defaults
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db = getenv('DB_DATABASE') ?: 'kabras_store';
        $this->user = getenv('DB_USERNAME') ?: 'root';
        $this->pass = getenv('DB_PASSWORD') ?: 'Hunter42.';
    }

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
                PDO::ATTR_EMULATE_PREPARES => false,
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
