<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && $_POST['logout'] == '1') {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}


require_once '../handlers/AuthHandler.php';
require_once '../handlers/AuditHandler.php';

$authHandler = new AuthHandler();

// Ensure user is logged in
if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in first.');
    exit();
}

$currentUser = $authHandler->getCurrentUser();

// Restrict only Cashier
if ($currentUser['role'] !== 'Accountant') {
    header('Location: ../login.php?error=Access denied. Cashier privileges required.');
    exit();
}

$auditHandler = new AuditHandler();
$auditReports = $auditHandler->getAllAuditReports();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accountant Dashboard | Audit Reports</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/audits.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <h2>Audit Reports</h2>
        <?php
        // Stats calculation
        $countPassed = 0;
        $countFailed = 0;
        $countPending = 0;
        $countCompleted = 0;
        $typeCounts = [
            'Financial' => 0,
            'Stock' => 0,
            'Safety' => 0,
            'Regulatory' => 0
        ];
        foreach ($auditReports as $ar) {
            if (($ar['status'] ?? '') === 'Passed') $countPassed++;
            elseif (($ar['status'] ?? '') === 'Failed') $countFailed++;
            elseif (($ar['status'] ?? '') === 'Pending') $countPending++;
            if (!empty($ar['completion_date'])) $countCompleted++;
            if (!empty($ar['audit_type']) && isset($typeCounts[$ar['audit_type']])) $typeCounts[$ar['audit_type']]++;
        }
        ?>
        <div class="audit-stats">
            <div class="stat">
                <p class="label">Passed</p> <span class="value green-bg"><?= $countPassed ?></span>
            </div>
            <div class="stat">
                <p class="label">Failed</p> <span class="value red-bg"><?= $countFailed ?></span>
            </div>
            <div class="stat">
                <p class="label">Pending</p> <span class="value yellow-bg"><?= $countPending ?></span>
            </div>
            <div class="stat">
                <p class="label">Completed</p> <span class="value blue-bg"><?= $countCompleted ?></span>
            </div>
            <?php foreach ($typeCounts as $type => $count): ?>
                <div class="stat">
                    <p class="label"><?= htmlspecialchars($type) ?></p> <span class="value gray-bg"><?= $count ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Conducted By</th>
                    <th>Status</th>
                    <th>Comments</th>
                    <th>Follow Up Actions</th>
                    <th>Completion Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($auditReports as $ar): ?>
                    <tr>
                        <td><?= htmlspecialchars($ar['id']) ?></td>
                        <td><?= htmlspecialchars($ar['audit_date']) ?></td>
                        <td><?= htmlspecialchars($ar['audit_type']) ?></td>
                        <td><?= htmlspecialchars($ar['conducted_by_name'] ?? $ar['conducted_by']) ?></td>
                        <td class="status-cell <?php
                                                if (($ar['status'] ?? '') === 'Passed') echo 'green-bg';
                                                elseif (($ar['status'] ?? '') === 'Failed') echo 'red-bg';
                                                ?>"><?= htmlspecialchars($ar['status']) ?></td>
                        <td><?= nl2br(htmlspecialchars($ar['comments'] ?? '')) ?></td>
                        <td><?= nl2br(htmlspecialchars($ar['follow_up_actions'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($ar['completion_date'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>