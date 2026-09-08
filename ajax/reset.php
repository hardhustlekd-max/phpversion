<?php
/**
 * AJAX Handler: Database Reset & Factory Restore
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'clear_data';
$body = getRequestBody();

// Superadmin or Admin authorization check
$currentUser = getCurrentUser();
if (!$currentUser || !in_array($currentUser['role'], ['superadmin', 'admin'])) {
    jsonResponse(['success' => false, 'error' => 'Unauthorized. Super Admin or Admin access required.'], 403);
}

try {
    $pdo = Database::getConnection();

    switch ($action) {
        case 'clear_data':
            // Truncate transaction tables while preserving users, settings, and permissions
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec("TRUNCATE TABLE registrations;");
            $pdo->exec("TRUNCATE TABLE verifications;");
            $pdo->exec("TRUNCATE TABLE unregistered_reports;");
            $pdo->exec("TRUNCATE TABLE payment_receipts;");
            $pdo->exec("TRUNCATE TABLE print_orders;");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

            // Update reset epoch in settings
            $epoch = time();
            $now = date('c');
            $pdo->exec("UPDATE system_settings SET system_reset_epoch = {$epoch}, last_system_reset_at = '{$now}' WHERE setting_key = 'global_config'");

            logAuditAction('SYSTEM_DATA_RESET', 'All transaction records (registrations, verifications, reports, receipts, orders) cleared.', 'critical');
            jsonResponse(['success' => true, 'message' => 'Transaction data successfully cleared.']);
            break;

        case 'factory_reset':
            // Confirmation check
            $confirmCode = $body['confirmCode'] ?? '';
            if ($confirmCode !== 'RESET-PERMIT-2026') {
                jsonResponse(['success' => false, 'error' => 'Invalid confirmation code. Must be RESET-PERMIT-2026'], 400);
            }

            $sqlFile = APP_ROOT . '/sql/database.sql';
            if (!file_exists($sqlFile)) {
                jsonResponse(['success' => false, 'error' => 'Seed SQL file not found'], 500);
            }

            $sql = file_get_contents($sqlFile);
            $pdo->exec($sql);

            logAuditAction('FACTORY_RESET', 'System factory reset executed: database schema and initial seeds reloaded.', 'critical');
            jsonResponse(['success' => true, 'message' => 'System factory reset completed successfully.']);
            break;

        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
