<?php

require_once __DIR__ . '/../config/database.php';

class AuditHandler
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    // AUDIT REPORTS METHODS

    /**
     * Get all audit reports with pagination and filtering
     */
    public function getAuditReports($page = 1, $limit = 10, $filters = [])
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT ar.*, u.name as conducted_by_name 
                FROM audit_reports ar 
                LEFT JOIN users u ON ar.conducted_by = u.id 
                WHERE 1=1";

        $params = [];

        // Apply filters
        if (!empty($filters['audit_type'])) {
            $sql .= " AND ar.audit_type = ?";
            $params[] = $filters['audit_type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND ar.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND ar.audit_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND ar.audit_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (ar.comments LIKE ? OR u.name LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY ar.audit_date DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of audit reports
     */
    public function getAuditReportsCount($filters = [])
    {
        $sql = "SELECT COUNT(*) as total FROM audit_reports ar 
                LEFT JOIN users u ON ar.conducted_by = u.id 
                WHERE 1=1";

        $params = [];

        // Apply same filters as getAuditReports
        if (!empty($filters['audit_type'])) {
            $sql .= " AND ar.audit_type = ?";
            $params[] = $filters['audit_type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND ar.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND ar.audit_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND ar.audit_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (ar.comments LIKE ? OR u.name LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Fetch all audit reports with conducted_by name
    public function getAllAuditReports()
    {
        $sql = "SELECT ar.*, u.name AS conducted_by_name FROM audit_reports ar LEFT JOIN users u ON ar.conducted_by = u.id ORDER BY ar.audit_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get audit report by ID
     */
    public function getAuditReportById($id)
    {
        $sql = "SELECT ar.*, u.name as conducted_by_name 
                FROM audit_reports ar 
                LEFT JOIN users u ON ar.conducted_by = u.id 
                WHERE ar.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new audit report
     */
    public function createAuditReport($data)
    {
        $sql = "INSERT INTO audit_reports (audit_date, audit_type, conducted_by, status, comments, follow_up_actions, completion_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['audit_date'],
            $data['audit_type'],
            $data['conducted_by'],
            $data['status'],
            $data['comments'] ?? null,
            $data['follow_up_actions'] ?? null,
            $data['completion_date'] ?? null
        ]);
    }

    /**
     * Update audit report
     */
    public function updateAuditReport($id, $data)
    {
        $sql = "UPDATE audit_reports SET 
                audit_date = ?, audit_type = ?, conducted_by = ?, status = ?, 
                comments = ?, follow_up_actions = ?, completion_date = ? 
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['audit_date'],
            $data['audit_type'],
            $data['conducted_by'],
            $data['status'],
            $data['comments'] ?? null,
            $data['follow_up_actions'] ?? null,
            $data['completion_date'] ?? null,
            $id
        ]);
    }

    /**
     * Delete audit report
     */
    public function deleteAuditReport($id)
    {
        $sql = "DELETE FROM audit_reports WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // COMPLIANCE AUDITS METHODS

    /**
     * Get all compliance audits
     */
    public function getComplianceAudits($page = 1, $limit = 10, $filters = [])
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT ca.*, u.name as conducted_by_name 
                FROM compliance_audits ca 
                LEFT JOIN users u ON ca.conducted_by = u.id 
                WHERE 1=1";

        $params = [];

        // Apply filters
        if (!empty($filters['audit_type'])) {
            $sql .= " AND ca.audit_type = ?";
            $params[] = $filters['audit_type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND ca.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (ca.comments LIKE ? OR u.name LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY ca.audit_date DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get compliance audit by ID
     */
    public function getComplianceAuditById($id)
    {
        $sql = "SELECT ca.*, u.name as conducted_by_name 
                FROM compliance_audits ca 
                LEFT JOIN users u ON ca.conducted_by = u.id 
                WHERE ca.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create compliance audit
     */
    public function createComplianceAudit($data)
    {
        $sql = "INSERT INTO compliance_audits (audit_date, audit_type, conducted_by, status, comments) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['audit_date'],
            $data['audit_type'],
            $data['conducted_by'],
            $data['status'],
            $data['comments'] ?? null
        ]);
    }

    /**
     * Update compliance audit
     */
    public function updateComplianceAudit($id, $data)
    {
        $sql = "UPDATE compliance_audits SET 
                audit_date = ?, audit_type = ?, conducted_by = ?, status = ?, comments = ? 
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['audit_date'],
            $data['audit_type'],
            $data['conducted_by'],
            $data['status'],
            $data['comments'] ?? null,
            $id
        ]);
    }

    /**
     * Delete a compliance audit
     */
    public function deleteComplianceAudit($id)
    {
        $sql = "DELETE FROM compliance_audits WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // COMPLIANCE VIOLATIONS METHODS

    /**
     * Get all compliance violations
     */
    public function getComplianceViolations($page = 1, $limit = 10, $filters = [])
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT cv.*, 
                u1.name as reported_by_name, 
                u2.name as resolved_by_name 
                FROM compliance_violations cv 
                LEFT JOIN users u1 ON cv.reported_by = u1.id 
                LEFT JOIN users u2 ON cv.resolved_by = u2.id 
                WHERE 1=1";

        $params = [];

        // Apply filters
        if (!empty($filters['category'])) {
            $sql .= " AND cv.category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['severity'])) {
            $sql .= " AND cv.severity = ?";
            $params[] = $filters['severity'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND cv.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (cv.description LIKE ? OR u1.name LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY cv.violation_date DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get compliance violation by ID
     */
    public function getComplianceViolationById($id)
    {
        $sql = "SELECT cv.*, 
                u1.name as reported_by_name, 
                u2.name as resolved_by_name 
                FROM compliance_violations cv 
                LEFT JOIN users u1 ON cv.reported_by = u1.id 
                LEFT JOIN users u2 ON cv.resolved_by = u2.id 
                WHERE cv.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create compliance violation
     */
    public function createComplianceViolation($data)
    {
        $sql = "INSERT INTO compliance_violations (violation_date, category, reported_by, description, severity, status, resolution_notes, resolved_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['violation_date'],
            $data['category'],
            $data['reported_by'],
            $data['description'],
            $data['severity'],
            $data['status'],
            $data['resolution_notes'] ?? null,
            $data['resolved_by'] ?? null
        ]);
    }

    /**
     * Update compliance violation
     */
    public function updateComplianceViolation($id, $data)
    {
        $sql = "UPDATE compliance_violations SET 
                violation_date = ?, category = ?, reported_by = ?, description = ?, 
                severity = ?, status = ?, resolution_notes = ?, resolved_by = ? 
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $data['violation_date'],
            $data['category'],
            $data['reported_by'],
            $data['description'],
            $data['severity'],
            $data['status'],
            $data['resolution_notes'] ?? null,
            $data['resolved_by'] ?? null,
            $id
        ]);
    }

    /**
     * Delete compliance violation
     */
    public function deleteComplianceViolation($id)
    {
        $sql = "DELETE FROM compliance_violations WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // STATISTICS METHODS

    /**
     * Get audit statistics
     */
    public function getAuditStatistics()
    {
        $stats = [];

        // Audit Reports Stats
        $sql = "SELECT 
                COUNT(*) as total_audits,
                SUM(CASE WHEN status = 'Passed' THEN 1 ELSE 0 END) as passed_audits,
                SUM(CASE WHEN status = 'Failed' THEN 1 ELSE 0 END) as failed_audits,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_audits
                FROM audit_reports";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['audit_reports'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Compliance Audits Stats
        $sql = "SELECT 
                COUNT(*) as total_compliance_audits,
                SUM(CASE WHEN status = 'Passed' THEN 1 ELSE 0 END) as passed_compliance,
                SUM(CASE WHEN status = 'Failed' THEN 1 ELSE 0 END) as failed_compliance,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_compliance
                FROM compliance_audits";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['compliance_audits'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Compliance Violations Stats
        $sql = "SELECT 
                COUNT(*) as total_violations,
                SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved_violations,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_violations,
                SUM(CASE WHEN severity = 'High' THEN 1 ELSE 0 END) as high_severity,
                SUM(CASE WHEN DATE(violation_date) = CURDATE() THEN 1 ELSE 0 END) as today_violations
                FROM compliance_violations";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stats['compliance_violations'] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Get all users for dropdowns
     */
    public function getAllUsers()
    {
        $sql = "SELECT id, name, role FROM users ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}