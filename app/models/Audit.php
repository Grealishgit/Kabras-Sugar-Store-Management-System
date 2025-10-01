<?php
require_once __DIR__ . '/../config/database.php';

class Audit
{
    private $conn;
    private $table_audit_reports = 'audit_reports';
    private $table_compliance_audits = 'compliance_audits';
    private $table_compliance_violations = 'compliance_violations';

    // Audit Reports properties
    public $id;
    public $audit_date;
    public $audit_type;
    public $conducted_by;
    public $status;
    public $comments;
    public $follow_up_actions;
    public $completion_date;

    // Compliance Violations properties
    public $violation_date;
    public $category;
    public $reported_by;
    public $description;
    public $severity;
    public $resolution_notes;
    public $resolved_by;
    public $created_at;
    public $updated_at;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // AUDIT REPORTS CRUD OPERATIONS

    /**
     * Create audit report
     */
    public function createAuditReport()
    {
        $query = "INSERT INTO " . $this->table_audit_reports . " 
                  SET audit_date = :audit_date, 
                      audit_type = :audit_type, 
                      conducted_by = :conducted_by, 
                      status = :status, 
                      comments = :comments, 
                      follow_up_actions = :follow_up_actions, 
                      completion_date = :completion_date";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->audit_date = htmlspecialchars(strip_tags($this->audit_date));
        $this->audit_type = htmlspecialchars(strip_tags($this->audit_type));
        $this->conducted_by = htmlspecialchars(strip_tags($this->conducted_by));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->comments = htmlspecialchars(strip_tags($this->comments));
        $this->follow_up_actions = htmlspecialchars(strip_tags($this->follow_up_actions));
        $this->completion_date = htmlspecialchars(strip_tags($this->completion_date));

        // Bind data
        $stmt->bindParam(':audit_date', $this->audit_date);
        $stmt->bindParam(':audit_type', $this->audit_type);
        $stmt->bindParam(':conducted_by', $this->conducted_by);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':comments', $this->comments);
        $stmt->bindParam(':follow_up_actions', $this->follow_up_actions);
        $stmt->bindParam(':completion_date', $this->completion_date);

        if ($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    /**
     * Read all audit reports
     */
    public function readAuditReports()
    {
        $query = "SELECT ar.*, u.name as conducted_by_name 
                  FROM " . $this->table_audit_reports . " ar 
                  LEFT JOIN users u ON ar.conducted_by = u.id 
                  ORDER BY ar.audit_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Read single audit report
     */
    public function readSingleAuditReport()
    {
        $query = "SELECT ar.*, u.name as conducted_by_name 
                  FROM " . $this->table_audit_reports . " ar 
                  LEFT JOIN users u ON ar.conducted_by = u.id 
                  WHERE ar.id = :id 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->audit_date = $row['audit_date'];
            $this->audit_type = $row['audit_type'];
            $this->conducted_by = $row['conducted_by'];
            $this->status = $row['status'];
            $this->comments = $row['comments'];
            $this->follow_up_actions = $row['follow_up_actions'];
            $this->completion_date = $row['completion_date'];
        }

        return $row;
    }

    /**
     * Update audit report
     */
    public function updateAuditReport()
    {
        $query = "UPDATE " . $this->table_audit_reports . " 
                  SET audit_date = :audit_date, 
                      audit_type = :audit_type, 
                      conducted_by = :conducted_by, 
                      status = :status, 
                      comments = :comments, 
                      follow_up_actions = :follow_up_actions, 
                      completion_date = :completion_date 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->audit_date = htmlspecialchars(strip_tags($this->audit_date));
        $this->audit_type = htmlspecialchars(strip_tags($this->audit_type));
        $this->conducted_by = htmlspecialchars(strip_tags($this->conducted_by));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->comments = htmlspecialchars(strip_tags($this->comments));
        $this->follow_up_actions = htmlspecialchars(strip_tags($this->follow_up_actions));
        $this->completion_date = htmlspecialchars(strip_tags($this->completion_date));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind data
        $stmt->bindParam(':audit_date', $this->audit_date);
        $stmt->bindParam(':audit_type', $this->audit_type);
        $stmt->bindParam(':conducted_by', $this->conducted_by);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':comments', $this->comments);
        $stmt->bindParam(':follow_up_actions', $this->follow_up_actions);
        $stmt->bindParam(':completion_date', $this->completion_date);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    /**
     * Delete audit report
     */
    public function deleteAuditReport()
    {
        $query = "DELETE FROM " . $this->table_audit_reports . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // COMPLIANCE AUDITS CRUD OPERATIONS

    /**
     * Read all compliance audits
     */
    public function readComplianceAudits()
    {
        $query = "SELECT ca.*, u.name as conducted_by_name 
                  FROM " . $this->table_compliance_audits . " ca 
                  LEFT JOIN users u ON ca.conducted_by = u.id 
                  ORDER BY ca.audit_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Create compliance audit
     */
    public function createComplianceAudit()
    {
        $query = "INSERT INTO " . $this->table_compliance_audits . " 
                  SET audit_date = :audit_date, 
                      audit_type = :audit_type, 
                      conducted_by = :conducted_by, 
                      status = :status, 
                      comments = :comments";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->audit_date = htmlspecialchars(strip_tags($this->audit_date));
        $this->audit_type = htmlspecialchars(strip_tags($this->audit_type));
        $this->conducted_by = htmlspecialchars(strip_tags($this->conducted_by));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->comments = htmlspecialchars(strip_tags($this->comments));

        // Bind data
        $stmt->bindParam(':audit_date', $this->audit_date);
        $stmt->bindParam(':audit_type', $this->audit_type);
        $stmt->bindParam(':conducted_by', $this->conducted_by);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':comments', $this->comments);

        if ($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // COMPLIANCE VIOLATIONS CRUD OPERATIONS

    /**
     * Read all compliance violations
     */
    public function readComplianceViolations()
    {
        $query = "SELECT cv.*, 
                         u1.name as reported_by_name, 
                         u2.name as resolved_by_name 
                  FROM " . $this->table_compliance_violations . " cv 
                  LEFT JOIN users u1 ON cv.reported_by = u1.id 
                  LEFT JOIN users u2 ON cv.resolved_by = u2.id 
                  ORDER BY cv.violation_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Create compliance violation
     */
    public function createComplianceViolation()
    {
        $query = "INSERT INTO " . $this->table_compliance_violations . " 
                  SET violation_date = :violation_date, 
                      category = :category, 
                      reported_by = :reported_by, 
                      description = :description, 
                      severity = :severity, 
                      status = :status, 
                      resolution_notes = :resolution_notes, 
                      resolved_by = :resolved_by";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->violation_date = htmlspecialchars(strip_tags($this->violation_date));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->reported_by = htmlspecialchars(strip_tags($this->reported_by));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->severity = htmlspecialchars(strip_tags($this->severity));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->resolution_notes = htmlspecialchars(strip_tags($this->resolution_notes));
        $this->resolved_by = htmlspecialchars(strip_tags($this->resolved_by));

        // Bind data
        $stmt->bindParam(':violation_date', $this->violation_date);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':reported_by', $this->reported_by);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':severity', $this->severity);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':resolution_notes', $this->resolution_notes);
        $stmt->bindParam(':resolved_by', $this->resolved_by);

        if ($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    /**
     * Read single compliance violation
     */
    public function readSingleComplianceViolation()
    {
        $query = "SELECT cv.*, 
                         u1.name as reported_by_name, 
                         u2.name as resolved_by_name 
                  FROM " . $this->table_compliance_violations . " cv 
                  LEFT JOIN users u1 ON cv.reported_by = u1.id 
                  LEFT JOIN users u2 ON cv.resolved_by = u2.id 
                  WHERE cv.id = :id 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->violation_date = $row['violation_date'];
            $this->category = $row['category'];
            $this->reported_by = $row['reported_by'];
            $this->description = $row['description'];
            $this->severity = $row['severity'];
            $this->status = $row['status'];
            $this->resolution_notes = $row['resolution_notes'];
            $this->resolved_by = $row['resolved_by'];
        }

        return $row;
    }

    /**
     * Update compliance violation
     */
    public function updateComplianceViolation()
    {
        $query = "UPDATE " . $this->table_compliance_violations . " 
                  SET violation_date = :violation_date, 
                      category = :category, 
                      reported_by = :reported_by, 
                      description = :description, 
                      severity = :severity, 
                      status = :status, 
                      resolution_notes = :resolution_notes, 
                      resolved_by = :resolved_by 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->violation_date = htmlspecialchars(strip_tags($this->violation_date));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->reported_by = htmlspecialchars(strip_tags($this->reported_by));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->severity = htmlspecialchars(strip_tags($this->severity));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->resolution_notes = htmlspecialchars(strip_tags($this->resolution_notes));
        $this->resolved_by = htmlspecialchars(strip_tags($this->resolved_by));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind data
        $stmt->bindParam(':violation_date', $this->violation_date);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':reported_by', $this->reported_by);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':severity', $this->severity);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':resolution_notes', $this->resolution_notes);
        $stmt->bindParam(':resolved_by', $this->resolved_by);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    /**
     * Delete compliance violation
     */
    public function deleteComplianceViolation()
    {
        $query = "DELETE FROM " . $this->table_compliance_violations . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    /**
     * Get all users for dropdowns
     */
    public function getAllUsers()
    {
        $query = "SELECT id, name, role FROM users ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Search and filter functionality
     */
    public function searchAuditReports($searchTerm, $type = null, $status = null)
    {
        $query = "SELECT ar.*, u.name as conducted_by_name 
                  FROM " . $this->table_audit_reports . " ar 
                  LEFT JOIN users u ON ar.conducted_by = u.id 
                  WHERE (ar.comments LIKE :search OR u.name LIKE :search)";

        $params = [':search' => '%' . $searchTerm . '%'];

        if ($type) {
            $query .= " AND ar.audit_type = :type";
            $params[':type'] = $type;
        }

        if ($status) {
            $query .= " AND ar.status = :status";
            $params[':status'] = $status;
        }

        $query .= " ORDER BY ar.audit_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt;
    }
}
