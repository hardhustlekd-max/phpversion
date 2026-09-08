<?php
/**
 * AJAX Unregistered Vehicle Reports Controller
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireLogin(true);

$pdo = Database::getConnection();
$userBadge = Auth::badgeId();
$userName = Auth::user()['fullName'] ?? 'Officer';
$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $pdo->query("SELECT * FROM unregistered_vehicle_reports ORDER BY created_at DESC");
        $reports = $stmt->fetchAll();
        echo json_encode(['success' => true, 'reports' => $reports]);
        exit;
    }

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $id = 'UNREG-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $plate = strtoupper(trim($input['plate_number'] ?? ''));
        $driverName = trim($input['driver_name'] ?? '');
        $driverPhone = trim($input['driver_phone'] ?? '');
        $category = $input['vehicle_category'] ?? 'gas_under_110cc';
        $engine = trim($input['engine_or_serial_no'] ?? '');
        $chassis = trim($input['chassis_number'] ?? '');
        $brand = trim($input['motor_brand'] ?? '');
        $subCity = trim($input['sub_city'] ?? 'Central');
        $location = trim($input['location_name'] ?? 'Roadside Patrol');
        $notes = trim($input['notes'] ?? '');
        $evidence = $input['evidence_photo'] ?? null;

        $stmt = $pdo->prepare("
            INSERT INTO unregistered_vehicle_reports (
                id, reported_at, plate_number, driver_name, driver_phone, vehicle_category,
                engine_or_serial_no, chassis_number, motor_brand, sub_city, location_name,
                officer_badge_id, officer_name, notes, evidence_photo, status, created_at
            ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([
            $id, $plate, $driverName, $driverPhone, $category,
            $engine, $chassis, $brand, $subCity, $location,
            $userBadge, $userName, $notes, $evidence
        ]);

        echo json_encode(['success' => true, 'id' => $id]);
        exit;
    }

    if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = $input['id'] ?? '';
        $status = $input['status'] ?? 'under_investigation';
        $resNotes = $input['resolution_notes'] ?? '';

        $stmt = $pdo->prepare("UPDATE unregistered_vehicle_reports SET status = ?, resolution_notes = ? WHERE id = ?");
        $stmt->execute([$status, $resNotes, $id]);

        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
