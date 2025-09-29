<?php session_start();
require_once '../handlers/AuthHandler.php';
require_once '../handlers/ComplianceHandler.php';

$authHandler = new AuthHandler();

if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in first.');
    exit();
}

$currentUser = $authHandler->getCurrentUser();

if ($currentUser['role'] !== 'Accountant') {
    header('Location: ../login.php?error=Access denied. Cashier privileges required.');
    exit();
}

$complianceHandler = new ComplianceHandler();
$auditStats = $complianceHandler->getAuditStats();
$violationStats = $complianceHandler->getViolationStats();
$recentAudits = $complianceHandler->getRecentAudits(30);
$recentViolations = $complianceHandler->getRecentViolations(30);
$allAudits = $complianceHandler->getAllAudits();
$allViolations = $complianceHandler->getAllViolations();
$reminders = $complianceHandler->getReminders();


// Handle resolve violation action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_violation_id'])) {
    $resolveId = intval($_POST['resolve_violation_id']);

    if ($resolveId > 0) {
        $db = (new Database())->connect();
        $stmt = $db->prepare("UPDATE compliance_violations SET status = 'Resolved', resolved_by = ? WHERE id = ?");
        $stmt->execute([$currentUser['id'], $resolveId]);
        header('Location: compliance.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_audits_csv'])) {
    try {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Create output stream
        $output = fopen('php://output', 'w');

        if (!$output) {
            throw new Exception('Failed to create output stream');
        }

        // Add BOM for proper UTF-8 encoding in Excel
        fwrite($output, "\xEF\xBB\xBF");

        // Write CSV headers
        $headers = [
            'ID',
            'Date',
            'Type',
            'Inspector',
            'Status',
            'Comments'
        ];
        fputcsv($output, $headers, ',', '"', '\\');

        // Check if audits data exists
        if (empty($allAudits)) {
            fputcsv($output, ['No audit logs found'], ',', '"', '\\');
        } else {

            // Write audit data
            foreach ($allAudits as $audit) {
                $row = [
                    $audit['id'] ?? '',
                    $audit['audit_date'] ?? '',
                    $audit['audit_type'] ?? '',
                    $audit['inspector'] ?? '',
                    $audit['status'] ?? '',
                    $audit['comments'] ?? ''
                ];
                fputcsv($output, $row, ',', '"', '\\');
            }
        }

        fclose($output);
        exit();
    } catch (Exception $e) {
        // Handle errors gracefully
        http_response_code(500);
        header('Content-Type: text/plain');
        echo 'Error exporting audit logs: ' . $e->getMessage();
        exit();
    }
}

// Export Violations CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_violations_csv'])) {
    try {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="violations_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Create output stream
        $output = fopen('php://output', 'w');

        if (!$output) {
            throw new Exception('Failed to create output stream');
        }

        // Add BOM for proper UTF-8 encoding in Excel
        fwrite($output, "\xEF\xBB\xBF");

        // Write CSV headers
        $headers = [
            'ID',
            'Date',
            'Category',
            'Reported By',
            'Description',
            'Severity',
            'Status',
            'Resolver'
        ];
        fputcsv($output, $headers, ',', '"', '\\');

        // Check if violations data exists
        if (empty($allViolations)) {
            fputcsv($output, ['No violations found'], ',', '"', '\\');
        } else {

            // Write violations data
            foreach ($allViolations as $violation) {
                $row = [
                    $violation['id'] ?? '',
                    $violation['violation_date'] ?? '',
                    $violation['category'] ?? '',
                    $violation['reporter'] ?? '',
                    // Clean description text for CSV
                    isset($violation['description']) ? strip_tags($violation['description']) : '',
                    $violation['severity'] ?? '',
                    $violation['status'] ?? '',
                    $violation['resolver'] ?? ''
                ];
                fputcsv($output, $row, ',', '"', '\\');
            }
        }

        fclose($output);
        exit();
    } catch (Exception $e) {
        // Handle errors gracefully
        http_response_code(500);
        header('Content-Type: text/plain');
        echo 'Error exporting violations: ' . $e->getMessage();
        exit();
    }
}

?>< !DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accountant Dashboard | Compliance</title>
        <link rel="stylesheet" href="../assets/css/sidebar.css">
        <link rel="stylesheet" href="../assets/css/compliance.css">
    </head>

    <body><?php include '../includes/sidebar.php';
            ?><div class="compliance-main-content">
            <h1 class="compliance-title">Compliance Dashboard</h1>
            <div class="compliance-summary-row">
                <div class="compliance-card">
                    <h4>Total Audits</h4>
                    <div class="card-value"><?= $auditStats['total'] ?></div>
                </div>
                <div class="compliance-card">
                    <h4>Pending Audits</h4>
                    <div class="card-value"><?= $auditStats['pending'] ?></div>
                </div>
                <div class="compliance-card">
                    <h4>Violations Reported</h4>
                    <div class="card-value"><?= $violationStats['total'] ?></div>
                </div>
                <div class="compliance-card">
                    <h4>Resolved Violations</h4>
                    <div class="card-value"><?= $violationStats['resolved'] ?></div>
                </div>
                <div class="compliance-card">
                    <h4>Unresolved Violations</h4>
                    <div class="card-value"><?= $violationStats['pending'] ?></div>
                </div>
            </div>
            <h2 class="compliance-section-title">Recent Compliance Activities (Last 30 Days)</h2>
            <div class="compliance-activities-row">
                <div class="compliance-table-card">
                    <h3>Recent Audits & Inspections</h3>
                    <table class="compliance-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Inspector</th>
                                <th>Status</th>
                                <th>Comments</th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($recentAudits as $a): ?><tr>
                                    <td><?= $a['id'] ?></td>
                                    <td><?= htmlspecialchars($a['audit_date']) ?></td>
                                    <td><?= htmlspecialchars($a['audit_type']) ?></td>
                                    <td><?= htmlspecialchars($a['inspector']) ?></td>
                                    <td><?= htmlspecialchars($a['status']) ?></td>
                                    <td><?= htmlspecialchars($a['comments']) ?></td>
                                </tr><?php endforeach;
                                        ?><?php if (empty($recentAudits)): ?><tr>
                                    <td colspan="6">No recent audits.</td>
                                </tr><?php endif;
                                        ?></tbody>
                    </table>
                </div>
                <div class="compliance-table-card">
                    <h3>Recent Violations & Issues</h3>
                    <table class="compliance-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Reported By</th>
                                <th>Description</th>
                                <th>Severity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($recentViolations as $v): ?><tr>
                                    <td><?= $v['id'] ?></td>
                                    <td><?= htmlspecialchars($v['violation_date']) ?></td>
                                    <td><?= htmlspecialchars($v['category']) ?></td>
                                    <td><?= htmlspecialchars($v['reporter']) ?></td>
                                    <td><?= htmlspecialchars($v['description']) ?></td>
                                    <td><?= htmlspecialchars($v['severity']) ?></td>
                                    <td><?= htmlspecialchars($v['status']) ?></td>
                                </tr><?php endforeach;
                                        ?><?php if (empty($recentViolations)): ?><tr>
                                    <td colspan="7">No recent violations.</td>
                                </tr><?php endif;
                                        ?></tbody>
                    </table>
                </div>
            </div>
            <h2 class="compliance-section-title">Audit & Inspection Logs</h2>
            <div class="compliance-table-card">
                <form method="post" style="display:inline; float:right; margin-left:8px;"><input type="hidden"
                        name="export_audits_csv" value="1"><button type="submit" class="btn-export-audits">Export
                        Audits CSV</button></form>
                <table class="compliance-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Inspector</th>
                            <th>Status</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($allAudits as $a): ?><tr>
                                <td><?= $a['id'] ?></td>
                                <td><?= htmlspecialchars($a['audit_date']) ?></td>
                                <td><?= htmlspecialchars($a['audit_type']) ?></td>
                                <td><?= htmlspecialchars($a['inspector']) ?></td>
                                <td><?= htmlspecialchars($a['status']) ?></td>
                                <td><?= htmlspecialchars($a['comments'] ?? '') ?></td>
                            </tr><?php endforeach;
                                    ?><?php if (empty($allAudits)): ?><tr>
                                <td colspan="6">No audits found.</td>
                            </tr><?php endif;
                                    ?></tbody>
                </table>
            </div>
            <h2 class="compliance-section-title">Violations & Issues</h2>
            <div class="compliance-table-card">
                <form method="post" style="display:inline; float:right; margin-left:8px;"><input type="hidden"
                        name="export_violations_csv" value="1"><button type="submit"
                        class="btn btn-export-violations">Export Violations CSV</button></form>
                <table class="compliance-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Reported By</th>
                            <th>Description</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Resolver</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($allViolations as $v): ?><tr>
                                <td><?= $v['id'] ?></td>
                                <td><?= htmlspecialchars($v['violation_date']) ?></td>
                                <td><?= htmlspecialchars($v['category']) ?></td>
                                <td><?= htmlspecialchars($v['reporter']) ?></td>
                                <td><?= htmlspecialchars($v['description']) ?></td>
                                <td><?= htmlspecialchars($v['severity']) ?></td>
                                <td><?= htmlspecialchars($v['status']) ?><?php if ($v['category'] === 'Financial' && $v['status'] === 'Pending'): ?>
                                    <form method="post" style="display:inline;"><input type="hidden"
                                            name="resolve_violation_id" value="<?= $v['id'] ?>"><button type="submit"
                                            class="btn btn-resolve">Resolve</button></form><?php endif;
                                                                                            ?>
                                </td>
                                <td><?= htmlspecialchars($v['resolver'] ?? '') ?></td>
                            </tr><?php endforeach;
                                    ?><?php if (empty($allViolations)): ?><tr>
                                <td colspan="8">No violations found.</td>
                            </tr><?php endif;
                                    ?></tbody>
                </table>
            </div>
            <h2 class="compliance-section-title">Compliance Reminders / Alerts</h2>
            <div class="compliance-reminders-row">
                <div class="compliance-reminder-card">
                    <h4>Upcoming Audits (Next 7 Days)</h4>
                    <ul><?php foreach ($reminders['upcoming_audits'] as $a): ?><li>
                                <?= htmlspecialchars($a['audit_type']) ?>audit on
                                <?= htmlspecialchars($a['audit_date']) ?>(Status: <?= htmlspecialchars($a['status']) ?>)
                            </li>
                            <?php endforeach;
                            ?><?php if (empty($reminders['upcoming_audits'])): ?>
                            <li>No upcoming audits.</li><?php endif;
                                                        ?>
                    </ul>
                </div>
                <div class="compliance-reminder-card">
                    <h4>Pending Violations</h4>
                    <ul><?php foreach ($reminders['pending_violations'] as $v): ?><li>
                                <?= htmlspecialchars($v['category']) ?>violation reported on
                                <?= htmlspecialchars($v['violation_date']) ?>(Severity:
                                <?= htmlspecialchars($v['severity']) ?>)</li>
                            <?php endforeach;
                            ?><?php if (empty($reminders['pending_violations'])): ?><li>No pending violations.</li><?php endif;

                                                                                                                    ?></ul>
                </div>
            </div>
        </div>

    </body>

    </html>