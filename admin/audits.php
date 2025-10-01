<?php
session_start();
require_once '../handlers/AuthHandler.php';
require_once '../handlers/AuditHandler.php';
require_once '../app/models/User.php';

$authHandler = new AuthHandler();
$auditHandler = new AuditHandler();
$userHandler = new User();

// Ensure user is logged in and is Admin
if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in first.');
    exit();
}

$currentUser = $authHandler->getCurrentUser();
if ($currentUser['role'] !== 'Admin') {
    header('Location: ../login.php?error=Access denied. Admin privileges required.');
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    switch ($_POST['action']) {
        case 'create':
            $data = [
                'audit_date' => $_POST['audit_date'],
                'audit_type' => $_POST['audit_type'],
                'conducted_by' => $_POST['conducted_by'],
                'status' => $_POST['status'],
                'comments' => $_POST['comments'] ?? '',
                'follow_up_actions' => $_POST['follow_up_actions'] ?? '',
                'completion_date' => !empty($_POST['completion_date']) ? $_POST['completion_date'] : null
            ];

            $result = $auditHandler->createAuditReport($data);
            echo json_encode(['success' => $result]);
            exit();

        case 'update':
            $id = $_POST['id'];
            $data = [
                'audit_date' => $_POST['audit_date'],
                'audit_type' => $_POST['audit_type'],
                'conducted_by' => $_POST['conducted_by'],
                'status' => $_POST['status'],
                'comments' => $_POST['comments'] ?? '',
                'follow_up_actions' => $_POST['follow_up_actions'] ?? '',
                'completion_date' => !empty($_POST['completion_date']) ? $_POST['completion_date'] : null
            ];

            $result = $auditHandler->updateAuditReport($id, $data);
            echo json_encode(['success' => $result]);
            exit();

        case 'delete':
            $id = $_POST['id'];
            $result = $auditHandler->deleteAuditReport($id);
            echo json_encode(['success' => $result]);
            exit();

        case 'get':
            $id = $_POST['id'];
            $audit = $auditHandler->getAuditReportById($id);
            echo json_encode($audit);
            exit();

        // Compliance Audit actions
        case 'create_compliance':
            $data = [
                'audit_date' => $_POST['audit_date'],
                'audit_type' => $_POST['audit_type'],
                'conducted_by' => $_POST['conducted_by'],
                'status' => $_POST['status'],
                'comments' => $_POST['comments'] ?? ''
            ];

            $result = $auditHandler->createComplianceAudit($data);
            echo json_encode(['success' => $result]);
            exit();

        case 'update_compliance':
            $id = $_POST['id'];
            $data = [
                'audit_date' => $_POST['audit_date'],
                'audit_type' => $_POST['audit_type'],
                'conducted_by' => $_POST['conducted_by'],
                'status' => $_POST['status'],
                'comments' => $_POST['comments'] ?? ''
            ];

            $result = $auditHandler->updateComplianceAudit($id, $data);
            echo json_encode(['success' => $result]);
            exit();

        case 'delete_compliance':
            $id = $_POST['id'];
            $result = $auditHandler->deleteComplianceAudit($id);
            echo json_encode(['success' => $result]);
            exit();

        case 'get_compliance':
            $id = $_POST['id'];
            $complianceAudit = $auditHandler->getComplianceAuditById($id);
            echo json_encode($complianceAudit);
            exit();
    }
}

// Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && $_POST['logout'] == '1') {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// Get filters
$filters = [];
if (!empty($_GET['audit_type'])) $filters['audit_type'] = $_GET['audit_type'];
if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];

// Get audit reports
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$audits = $auditHandler->getAuditReports($page, $limit, $filters);
$totalAudits = $auditHandler->getAuditReportsCount($filters);
$totalPages = ceil($totalAudits / $limit);

// Get compliance audits
$complianceAudits = $auditHandler->getComplianceAudits($page, $limit, $filters);

// Get all users for dropdowns
$users = $auditHandler->getAllUsers();

// Get statistics
$stats = $auditHandler->getAuditStatistics();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Reports Management</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/audits.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-clipboard-check"></i> Audit Reports Management</h1>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add New Audit
                </button>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['audit_reports']['total_audits'] ?? 0 ?></h3>
                        <p>Total Audits</p>
                    </div>
                </div>
                <div class="stat-card passed">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['audit_reports']['passed_audits'] ?? 0 ?></h3>
                        <p>Passed Audits</p>
                    </div>
                </div>
                <div class="stat-card failed">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['audit_reports']['failed_audits'] ?? 0 ?></h3>
                        <p>Failed Audits</p>
                    </div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['audit_reports']['pending_audits'] ?? 0 ?></h3>
                        <p>Pending Audits</p>
                    </div>
                </div>
            </div>

            <!-- Compliance Audits Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shield-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['compliance_audits']['total_compliance_audits'] ?? 0 ?></h3>
                        <p>Total Compliance Audits</p>
                    </div>
                </div>
                <div class="stat-card passed">
                    <div class="stat-icon">
                        <i class="fas fa-check-shield"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['compliance_audits']['passed_compliance'] ?? 0 ?></h3>
                        <p>Passed Compliance</p>
                    </div>
                </div>
                <div class="stat-card failed">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['compliance_audits']['failed_compliance'] ?? 0 ?></h3>
                        <p>Failed Compliance</p>
                    </div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['compliance_audits']['pending_compliance'] ?? 0 ?></h3>
                        <p>Pending Compliance</p>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Search audits..."
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>

                    <div class="filter-controls">
                        <select name="audit_type">
                            <option value="">All Types</option>
                            <option value="Financial" <?= ($_GET['audit_type'] ?? '') === 'Financial' ? 'selected' : '' ?>>Financial</option>
                            <option value="Stock" <?= ($_GET['audit_type'] ?? '') === 'Stock' ? 'selected' : '' ?>>Stock</option>
                            <option value="Safety" <?= ($_GET['audit_type'] ?? '') === 'Safety' ? 'selected' : '' ?>>Safety</option>
                            <option value="Regulatory" <?= ($_GET['audit_type'] ?? '') === 'Regulatory' ? 'selected' : '' ?>>Regulatory</option>
                        </select>

                        <select name="status">
                            <option value="">All Status</option>
                            <option value="Pending" <?= ($_GET['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Passed" <?= ($_GET['status'] ?? '') === 'Passed' ? 'selected' : '' ?>>Passed</option>
                            <option value="Failed" <?= ($_GET['status'] ?? '') === 'Failed' ? 'selected' : '' ?>>Failed</option>
                        </select>

                        <button type="submit" class="btn btn-secondary">Filter</button>
                        <a href="audits.php" class="btn btn-outline">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Audits Table -->
            <div class="table-container">
                <table class="audits-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Conducted By</th>
                            <th>Status</th>
                            <th>Completion Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($audits)): ?>
                            <tr>
                                <td colspan="7" class="no-data">No audit reports found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($audits as $audit): ?>
                                <tr>
                                    <td>#<?= $audit['id'] ?></td>
                                    <td><?= date('M j, Y', strtotime($audit['audit_date'])) ?></td>
                                    <td><span class="type-badge type-<?= strtolower($audit['audit_type']) ?>"><?= $audit['audit_type'] ?></span></td>
                                    <td><?= htmlspecialchars($audit['conducted_by_name']) ?></td>
                                    <td><span class="status-badge status-<?= strtolower($audit['status']) ?>"><?= $audit['status'] ?></span></td>
                                    <td><?= $audit['completion_date'] ? date('M j, Y', strtotime($audit['completion_date'])) : 'N/A' ?></td>
                                    <td class="actions">
                                        <button onclick="viewAudit(<?= $audit['id'] ?>)" class="btn-action btn-view" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editAudit(<?= $audit['id'] ?>)" class="btn-action btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteAudit(<?= $audit['id'] ?>)" class="btn-action btn-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&<?= http_build_query($_GET) ?>" class="btn btn-outline">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>&<?= http_build_query($_GET) ?>"
                            class="btn <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&<?= http_build_query($_GET) ?>" class="btn btn-outline">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Compliance Audits Table -->
            <div class="table-container" style="margin-top: 32px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0; color: #1BB02C; display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-shield-check"></i> Compliance Audits
                    </h2>
                    <button class="btn btn-primary" onclick="openAddComplianceModal()">
                        <i class="fas fa-plus"></i> Add Compliance Audit
                    </button>
                </div>
                <table class="audits-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Conducted By</th>
                            <th>Status</th>
                            <th>Comments</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($complianceAudits)): ?>
                            <tr>
                                <td colspan="7" class="no-data">No compliance audits found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($complianceAudits as $complianceAudit): ?>
                                <tr>
                                    <td>#<?= $complianceAudit['id'] ?></td>
                                    <td><?= date('M j, Y', strtotime($complianceAudit['audit_date'])) ?></td>
                                    <td><span class="type-badge type-<?= strtolower($complianceAudit['audit_type']) ?>"><?= $complianceAudit['audit_type'] ?></span></td>
                                    <td><?= htmlspecialchars($complianceAudit['conducted_by_name']) ?></td>
                                    <td><span class="status-badge status-<?= strtolower($complianceAudit['status']) ?>"><?= $complianceAudit['status'] ?></span></td>
                                    <td class="description-cell" title="<?= htmlspecialchars($complianceAudit['comments'] ?? '') ?>">
                                        <?= $complianceAudit['comments'] ? (strlen($complianceAudit['comments']) > 50 ? substr(htmlspecialchars($complianceAudit['comments']), 0, 50) . '...' : htmlspecialchars($complianceAudit['comments'])) : 'N/A' ?>
                                    </td>
                                    <td class="actions">
                                        <button onclick="viewComplianceAudit(<?= $complianceAudit['id'] ?>)" class="btn-action btn-view" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editComplianceAudit(<?= $complianceAudit['id'] ?>)" class="btn-action btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteComplianceAudit(<?= $complianceAudit['id'] ?>)" class="btn-action btn-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="auditModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Audit</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>

            <form id="auditForm">
                <input type="hidden" id="auditId" name="id">

                <div class="form-group">
                    <label for="audit_date">Audit Date *</label>
                    <input type="date" id="audit_date" name="audit_date" required>
                </div>

                <div class="form-group">
                    <label for="audit_type">Audit Type *</label>
                    <select id="audit_type" name="audit_type" required>
                        <option value="">Select Type</option>
                        <option value="Financial">Financial</option>
                        <option value="Stock">Stock</option>
                        <option value="Safety">Safety</option>
                        <option value="Regulatory">Regulatory</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="conducted_by">Conducted By *</label>
                    <select id="conducted_by" name="conducted_by" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= $user['role'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Passed">Passed</option>
                        <option value="Failed">Failed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="completion_date">Completion Date</label>
                    <input type="date" id="completion_date" name="completion_date">
                </div>

                <div class="form-group">
                    <label for="comments">Comments</label>
                    <textarea id="comments" name="comments" rows="4" placeholder="Enter audit comments..."></textarea>
                </div>

                <div class="form-group">
                    <label for="follow_up_actions">Follow-up Actions</label>
                    <textarea id="follow_up_actions" name="follow_up_actions" rows="4" placeholder="Enter follow-up actions..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Audit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>View Audit Report</h2>
                <span class="close" onclick="closeViewModal()">&times;</span>
            </div>

            <div id="viewContent" class="view-content">
                <!-- Content will be populated by JavaScript -->
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeViewModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Compliance Audit Modal -->
    <div id="complianceAuditModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="complianceModalTitle">Add New Compliance Audit</h2>
                <span class="close" onclick="closeComplianceModal()">&times;</span>
            </div>

            <form id="complianceAuditForm">
                <input type="hidden" id="complianceAuditId" name="id">

                <div class="form-group">
                    <label for="compliance_audit_date">Audit Date *</label>
                    <input type="date" id="compliance_audit_date" name="audit_date" required>
                </div>

                <div class="form-group">
                    <label for="compliance_audit_type">Audit Type *</label>
                    <select id="compliance_audit_type" name="audit_type" required>
                        <option value="">Select Type</option>
                        <option value="Financial">Financial</option>
                        <option value="Stock">Stock</option>
                        <option value="Safety">Safety</option>
                        <option value="Regulatory">Regulatory</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="compliance_conducted_by">Conducted By *</label>
                    <select id="compliance_conducted_by" name="conducted_by" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= $user['role'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="compliance_status">Status *</label>
                    <select id="compliance_status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Passed">Passed</option>
                        <option value="Failed">Failed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="compliance_comments">Comments</label>
                    <textarea id="compliance_comments" name="comments" rows="4" placeholder="Enter compliance audit comments..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeComplianceModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Compliance Audit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Compliance Audit Modal -->
    <div id="viewComplianceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>View Compliance Audit</h2>
                <span class="close" onclick="closeViewComplianceModal()">&times;</span>
            </div>

            <div id="viewComplianceContent" class="view-content">
                <!-- Content will be populated by JavaScript -->
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeViewComplianceModal()">Close</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/audits.js"></script>
</body>

</html>