<?php

require_once __DIR__ . '/../config/database.php';

class AuditHandler
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    // Fetch all audit reports with conducted_by name
    public function getAllAuditReports()
    {
        $sql = "SELECT ar.*, u.name AS conducted_by_name FROM audit_reports ar LEFT JOIN users u ON ar.conducted_by = u.id ORDER BY ar.audit_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
