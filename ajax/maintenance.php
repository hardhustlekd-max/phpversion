<?php
/**
 * AJAX Maintenance Controller (Super Admin)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/rbac.php';

Auth::requireLogin(true);
$userRole = Auth::role();

if ($userRole !== 'superadmin' && $userRole !== 'super_admin' && !RBAC::isAllowed($userRole, 12)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Super Admin DB maintenance access required']);
    exit;
}

$pdo = Database::getConnection();
$action = $_GET['action'] ?? '';

try {
    if ($action === 'backup_export') {
        $backup = [
            'exported_at' => date('Y-m-d H:i:s'),
            'version' => APP_VERSION,
            'users' => $pdo->query("SELECT id, badge_id, email, full_name, role, sub_city, status, created_at FROM users")->fetchAll(),
            'motorcycle_registrations' => $pdo->query("SELECT * FROM motorcycle_registrations")->fetchAll(),
            'payment_receipts' => $pdo->query("SELECT * FROM payment_receipts")->fetchAll(),
            'verification_logs' => $pdo->query("SELECT * FROM verification_logs")->fetchAll(),
            'unregistered_vehicle_reports' => $pdo->query("SELECT * FROM unregistered_vehicle_reports")->fetchAll(),
            'role_permissions' => $pdo->query("SELECT * FROM role_permissions")->fetchAll(),
            'system_settings' => $pdo->query("SELECT * FROM system_settings")->fetchAll()
        ];

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="permit_db_backup_' . date('Ymd_His') . '.json"');
        echo json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'purge_rejected' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("DELETE FROM motorcycle_registrations WHERE status = 'rejected'");
        $stmt->execute();
        $count = $stmt->rowCount();
        echo json_encode(['success' => true, 'purged' => $count]);
        exit;
    }

    if ($action === 'clear_audit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $pdo->exec("TRUNCATE TABLE system_audit_logs");
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'reset_database' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Reset operational tables
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("TRUNCATE TABLE motorcycle_registrations;");
        $pdo->exec("TRUNCATE TABLE payment_receipts;");
        $pdo->exec("TRUNCATE TABLE verification_logs;");
        $pdo->exec("TRUNCATE TABLE unregistered_vehicle_reports;");
        $pdo->exec("TRUNCATE TABLE print_batch_orders;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // Update settings reset epoch
        $pdo->exec("UPDATE system_settings SET system_reset_epoch = UNIX_TIMESTAMP(), last_system_reset_at = NOW() WHERE id = 'global_config'");

        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown maintenance action']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
