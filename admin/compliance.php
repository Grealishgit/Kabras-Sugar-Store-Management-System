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
                'violation_date' => $_POST['violation_date'],
                'category' => $_POST['category'],
                'reported_by' => $_POST['reported_by'],
                'description' => $_POST['description'],
                'severity' => $_POST['severity'],
                'status' => $_POST['status'],
                'resolution_notes' => $_POST['resolution_notes'] ?? '',
                'resolved_by' => !empty($_POST['resolved_by']) ? $_POST['resolved_by'] : null
            ];

            $result = $auditHandler->createComplianceViolation($data);
            echo json_encode(['success' => $result]);
            exit();

        case 'update':
            $id = $_POST['id'];
            $data = [
                'violation_date' => $_POST['violation_date'],
                'category' => $_POST['category'],
                'reported_by' => $_POST['reported_by'],
                'description' => $_POST['description'],
                'severity' => $_POST['severity'],
                'status' => $_POST['status'],
                'resolution_notes' => $_POST['resolution_notes'] ?? '',
                'resolved_by' => !empty($_POST['resolved_by']) ? $_POST['resolved_by'] : null
            ];

            $result = $auditHandler->updateComplianceViolation($id, $data);
            echo json_encode(['success' => $result]);
            exit();

        case 'delete':
            $id = $_POST['id'];
            $result = $auditHandler->deleteComplianceViolation($id);
            echo json_encode(['success' => $result]);
            exit();

        case 'get':
            $id = $_POST['id'];
            $violation = $auditHandler->getComplianceViolationById($id);
            echo json_encode($violation);
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
if (!empty($_GET['category'])) $filters['category'] = $_GET['category'];
if (!empty($_GET['severity'])) $filters['severity'] = $_GET['severity'];
if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];

// Get compliance violations
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$violations = $auditHandler->getComplianceViolations($page, $limit, $filters);

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
    <title>Compliance Violations Management</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/compliance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <main class="compliance-main-content">
        <div class="compliance-container">
            <div class="compliance-title">
                <h1><i class="fas fa-shield-alt"></i> Compliance Violations Management</h1>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Report New Violation
                </button>
            </div>

            <!-- Statistics Cards -->
            <div class="compliance-summary-row">
                <div class="compliance-card">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Total Violations</h4>
                        <div class="card-value"><?= $stats['compliance_violations']['total_violations'] ?? 0 ?></div>
                    </div>
                </div>
                <div class="compliance-card resolved">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Resolved</h4>
                        <div class="card-value"><?= $stats['compliance_violations']['resolved_violations'] ?? 0 ?></div>
                    </div>
                </div>
                <div class="compliance-card pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Pending</h4>
                        <div class="card-value"><?= $stats['compliance_violations']['pending_violations'] ?? 0 ?></div>
                    </div>
                </div>
                <div class="compliance-card high-severity">
                    <div class="stat-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="stat-info">
                        <h4>High Severity</h4>
                        <div class="card-value"><?= $stats['compliance_violations']['high_severity'] ?? 0 ?></div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Search violations..."
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>

                    <div class="filter-controls">
                        <select name="category">
                            <option value="">All Categories</option>
                            <option value="Financial"
                                <?= ($_GET['category'] ?? '') === 'Financial' ? 'selected' : '' ?>>Financial</option>
                            <option value="Stock" <?= ($_GET['category'] ?? '') === 'Stock' ? 'selected' : '' ?>>Stock
                            </option>
                            <option value="Safety" <?= ($_GET['category'] ?? '') === 'Safety' ? 'selected' : '' ?>>
                                Safety</option>
                            <option value="Legal" <?= ($_GET['category'] ?? '') === 'Legal' ? 'selected' : '' ?>>Legal
                            </option>
                        </select>

                        <select name="severity">
                            <option value="">All Severity</option>
                            <option value="Low" <?= ($_GET['severity'] ?? '') === 'Low' ? 'selected' : '' ?>>Low
                            </option>
                            <option value="Medium" <?= ($_GET['severity'] ?? '') === 'Medium' ? 'selected' : '' ?>>
                                Medium</option>
                            <option value="High" <?= ($_GET['severity'] ?? '') === 'High' ? 'selected' : '' ?>>High
                            </option>
                        </select>

                        <select name="status">
                            <option value="">All Status</option>
                            <option value="Pending" <?= ($_GET['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>
                                Pending</option>
                            <option value="Resolved" <?= ($_GET['status'] ?? '') === 'Resolved' ? 'selected' : '' ?>>
                                Resolved</option>
                        </select>

                        <button type="submit" class="btn btn-secondary">Filter</button>
                        <a href="compliance.php" class="btn btn-outline">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Violations Table -->
            <div class="compliance-activities-row">
                <div class="compliance-table-card">
                    <h4 class="compliance-section-title">Compliance Violations</h4>
                    <table class="compliance-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Severity</th>
                                <th>Reported By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($violations)): ?>
                                <tr>
                                    <td colspan="8" class="no-data">No compliance violations found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($violations as $violation): ?>
                                    <tr>
                                        <td>#<?= $violation['id'] ?></td>
                                        <td><?= date('M j, Y', strtotime($violation['violation_date'])) ?></td>
                                        <td><span
                                                class="category-badge category-<?= strtolower($violation['category']) ?>"><?= $violation['category'] ?></span>
                                        </td>
                                        <td class="description-cell" title="<?= htmlspecialchars($violation['description']) ?>">
                                            <?= strlen($violation['description']) > 50 ? substr(htmlspecialchars($violation['description']), 0, 50) . '...' : htmlspecialchars($violation['description']) ?>
                                        </td>
                                        <td><span
                                                class="severity-badge severity-<?= strtolower($violation['severity']) ?>"><?= $violation['severity'] ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($violation['reported_by_name']) ?></td>
                                        <td><span
                                                class="status-badge status-<?= strtolower($violation['status']) ?>"><?= $violation['status'] ?></span>
                                        </td>
                                        <td class="actions">
                                            <button onclick="viewViolation(<?= $violation['id'] ?>)" class="btn-action btn-view"
                                                title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="editViolation(<?= $violation['id'] ?>)" class="btn-action btn-edit"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteViolation(<?= $violation['id'] ?>)" class="btn-action btn-delete"
                                                title="Delete">
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
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="violationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Report New Violation</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>

            <form id="violationForm">
                <input type="hidden" id="violationId" name="id">

                <div class="form-group">
                    <label for="violation_date">Violation Date *</label>
                    <input type="date" id="violation_date" name="violation_date" required>
                </div>

                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Financial">Financial</option>
                        <option value="Stock">Stock</option>
                        <option value="Safety">Safety</option>
                        <option value="Legal">Legal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="reported_by">Reported By *</label>
                    <select id="reported_by" name="reported_by" required>
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= $user['role'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="4" required
                        placeholder="Describe the compliance violation..."></textarea>
                </div>

                <div class="form-group">
                    <label for="severity">Severity *</label>
                    <select id="severity" name="severity" required>
                        <option value="">Select Severity</option>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="resolved_by">Resolved By</label>
                    <select id="resolved_by" name="resolved_by">
                        <option value="">Select User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= $user['role'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="resolution_notes">Resolution Notes</label>
                    <textarea id="resolution_notes" name="resolution_notes" rows="4"
                        placeholder="Enter resolution notes..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Violation</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>View Compliance Violation</h2>
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

    <script src="../assets/js/compliance.js"></script>
</body>

</html>