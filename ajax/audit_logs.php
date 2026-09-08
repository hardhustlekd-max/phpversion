<?php
/**
 * AJAX Handler: System Audit Trail Logs
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'list';

try {
    $pdo = Database::getConnection();

    switch ($action) {
        case 'list':
            $limit = min(500, (int)($_GET['limit'] ?? 100));
            $stmt = $pdo->prepare("SELECT * FROM audit_logs ORDER BY timestamp DESC LIMIT ?");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $logs = $stmt->fetchAll();

            $formatted = array_map(function($al) {
                return [
                    'id' => $al['id'],
                    'timestamp' => $al['timestamp'],
                    'actorBadgeId' => $al['actor_badge_id'],
                    'actorRole' => $al['actor_role'],
                    'action' => $al['action'],
                    'details' => $al['details'],
                    'ipAddress' => $al['ip_address'],
                    'severity' => $al['severity'],
                ];
            }, $logs);

            jsonResponse(['success' => true, 'auditLogs' => $formatted]);
            break;

        case 'clear':
            $stmt = $pdo->query("DELETE FROM audit_logs");
            logAuditAction('CLEAR_AUDIT_LOGS', 'All system audit logs were cleared by administrator', 'warning');
            jsonResponse(['success' => true]);
            break;

        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
