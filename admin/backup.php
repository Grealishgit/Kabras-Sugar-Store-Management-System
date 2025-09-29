<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout']) && $_POST['logout'] == '1') {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}
require_once '../handlers/AuthHandler.php';

$authHandler = new AuthHandler();

// Ensure user is logged in and is admin
if (!$authHandler->isLoggedIn()) {
    header('Location: ../login.php?error=Please log in to access the admin dashboard.');
    exit();
}

$currentUser = $authHandler->getCurrentUser();

if ($currentUser['role'] !== 'Admin') {
    header('Location: ../login.php?error=Access denied. Admin privileges required.');
    exit();
}

// Handle database backup
if (isset($_POST['backup_db'])) {
    $host = "localhost";
    $user = "root";
    $pass = "Hunter42.";
    $dbname = "kabras_store";

    $backupDir = realpath(__DIR__ . '/../backups');
    if (!is_dir($backupDir) || !is_writable($backupDir)) {
        $error = "Backup directory is not writable. Please check folder permissions: $backupDir";
    } else {
        $backupFile = $backupDir . DIRECTORY_SEPARATOR . 'db-backup-' . date("Y-m-d-H-i-s") . '.sql';
        $command = "mysqldump --user={$user} --password={$pass} --host={$host} {$dbname} > \"{$backupFile}\"";
        system($command, $output);
        if ($output === 0 && file_exists($backupFile)) {
            $success = "Database backup created successfully! File saved at: $backupFile";
        } else {
            $error = "Database backup failed. Please check server permissions and mysqldump path.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Database</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/backup.css">
</head>

<body><?php include '../includes/sidebar.php';
        ?><main class="main-content">
        <div class="container">
            <h1>Backup Database</h1>
            <?php if (isset($success)): ?>
                <p class="success-msg">
                    <?= htmlspecialchars($success); ?>
                </p>
            <?php elseif (isset($error)): ?>
                <p class="error-msg">
                    <?= htmlspecialchars($error); ?>
                </p>
            <?php endif; ?>
            <form method="POST" action="backup.php" class="backup-form">
                <button type="submit" name="backup_db" class="btn-backup">Create Backup</button>
            </form>
        </div>
    </main>
</body>

</html>