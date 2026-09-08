<?php
/**
 * AJAX Data Synchronization Endpoint
 * Returns state payload for client hydration and offline caching
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireLogin(true);

$pdo = Database::getConnection();
$userRole = Auth::role();
$userBadge = Auth::badgeId();

try {
    // Scoped query for registrations
    $regQuery = "SELECT * FROM motorcycle_registrations WHERE 1=1";
    $regParams = [];

    if ($userRole !== 'superadmin' && $userRole !== 'super_admin') {
        $regQuery .= " AND hide_from_other_users = 0";
    }
    if ($userRole === 'clerk') {
        $regQuery .= " AND (LOWER(registered_by) = LOWER(?) OR registered_by = '')";
        $regParams[] = $userBadge;
    }
    $regQuery .= " ORDER BY created_at DESC";
    $regStmt = $pdo->prepare($regQuery);
    $regStmt->execute($regParams);
    $registrations = $regStmt->fetchAll();

    // Officers
    $officers = $pdo->query("SELECT * FROM officer_assignments WHERE status = 'active'")->fetchAll();

    // Print orders
    $printOrders = $pdo->query("SELECT * FROM print_batch_orders ORDER BY created_at DESC")->fetchAll();

    // Verification logs
    $verifications = $pdo->query("SELECT * FROM verification_logs ORDER BY created_at DESC LIMIT 100")->fetchAll();

    // Unregistered reports
    $unregistered = $pdo->query("SELECT * FROM unregistered_vehicle_reports ORDER BY created_at DESC LIMIT 100")->fetchAll();

    // Payment receipts
    $receipts = $pdo->query("SELECT * FROM payment_receipts ORDER BY created_at DESC LIMIT 200")->fetchAll();

    // System Settings
    $settings = $pdo->query("SELECT * FROM system_settings WHERE id = 'global_config' LIMIT 1")->fetch();

    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'registrations' => $registrations,
            'officers' => $officers,
            'print_orders' => $printOrders,
            'verification_logs' => $verifications,
            'unregistered_reports' => $unregistered,
            'payment_receipts' => $receipts,
            'settings' => $settings
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
