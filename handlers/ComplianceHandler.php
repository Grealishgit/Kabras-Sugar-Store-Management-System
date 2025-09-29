<?php
require_once __DIR__ . '/../config/database.php';

class ComplianceHandler
{
    private $db;
    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    // Summary stats
    public function getAuditStats()
    {
        $total = $this->db->query("SELECT COUNT(*) as total FROM compliance_audits")->fetch()['total'];
        $pending = $this->db->query("SELECT COUNT(*) as pending FROM compliance_audits WHERE status = 'Pending'")->fetch()['pending'];
        return [
            'total' => $total,
            'pending' => $pending
        ];
    }
    public function getViolationStats()
    {
        $total = $this->db->query("SELECT COUNT(*) as total FROM compliance_violations")->fetch()['total'];
        $resolved = $this->db->query("SELECT COUNT(*) as resolved FROM compliance_violations WHERE status = 'Resolved'")->fetch()['resolved'];
        $pending = $this->db->query("SELECT COUNT(*) as pending FROM compliance_violations WHERE status = 'Pending'")->fetch()['pending'];
        return [
            'total' => $total,
            'resolved' => $resolved,
            'pending' => $pending
        ];
    }
    // Recent activities (last 30 days)
    public function getRecentAudits($days = 30)
    {
        $stmt = $this->db->prepare("SELECT a.*, u.name as inspector FROM compliance_audits a JOIN users u ON a.conducted_by = u.id WHERE a.audit_date >= DATE_SUB(NOW(), INTERVAL :days DAY) ORDER BY a.audit_date DESC");
        $stmt->execute([':days' => $days]);
        return $stmt->fetchAll();
    }
    public function getRecentViolations($days = 30)
    {
        $stmt = $this->db->prepare("SELECT v.*, u.name as reporter, r.name as resolver FROM compliance_violations v JOIN users u ON v.reported_by = u.id LEFT JOIN users r ON v.resolved_by = r.id WHERE v.violation_date >= DATE_SUB(NOW(), INTERVAL :days DAY) ORDER BY v.violation_date DESC");
        $stmt->execute([':days' => $days]);
        return $stmt->fetchAll();
    }
    // All audits
    public function getAllAudits()
    {
        $stmt = $this->db->query("SELECT a.*, u.name as inspector FROM compliance_audits a JOIN users u ON a.conducted_by = u.id ORDER BY a.audit_date DESC");
        return $stmt->fetchAll();
    }
    // All violations
    public function getAllViolations()
    {
        $stmt = $this->db->query("SELECT v.*, u.name as reporter, r.name as resolver FROM compliance_violations v JOIN users u ON v.reported_by = u.id LEFT JOIN users r ON v.resolved_by = r.id ORDER BY v.violation_date DESC");
        return $stmt->fetchAll();
    }
    // Compliance reminders (expiring licenses, upcoming audits, pending actions)
    public function getReminders()
    {
        // Example: upcoming audits in next 7 days
        $upcomingAudits = $this->db->query("SELECT * FROM compliance_audits WHERE audit_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) AND status = 'Pending'")->fetchAll();
        // Example: unresolved violations
        $pendingViolations = $this->db->query("SELECT * FROM compliance_violations WHERE status = 'Pending'")->fetchAll();
        return [
            'upcoming_audits' => $upcomingAudits,
            'pending_violations' => $pendingViolations
        ];
    }
}
