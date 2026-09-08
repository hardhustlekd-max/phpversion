<?php
/**
 * AJAX Roadside Verification Logs Controller
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireLogin(true);

$pdo = Database::getConnection();
$userBadge = Auth::badgeId();
$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $pdo->query("SELECT * FROM verification_logs ORDER BY created_at DESC LIMIT 200");
        $logs = $stmt->fetchAll();
        echo json_encode(['success' => true, 'logs' => $logs]);
        exit;
    }

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $id = 'VLOG-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $plate = strtoupper(trim($input['plate_number'] ?? $input['plateNumber'] ?? ''));
        $name = trim($input['full_name'] ?? $input['fullName'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $category = $input['vehicle_category'] ?? $input['vehicleCategory'] ?? 'electric';
        $engine = $input['engine_or_serial_no'] ?? $input['engineOrSerialNo'] ?? '';
        $permitStatus = $input['permit_status'] ?? $input['permitStatus'] ?? 'approved';
        $verifStatus = $input['verification_status'] ?? $input['verificationStatus'] ?? 'verified';
        $notes = trim($input['officer_notes'] ?? $input['officerNotes'] ?? '');
        $location = trim($input['location_name'] ?? $input['locationName'] ?? 'Fasilo Checkpoint');
        $regId = $input['registration_id'] ?? $input['registrationId'] ?? null;
        $portrait = $input['user_portrait_photo'] ?? $input['userPortraitPhoto'] ?? null;

        $stmt = $pdo->prepare("
            INSERT INTO verification_logs (
                id, scanned_at, plate_number, full_name, phone, vehicle_category, engine_or_serial_no,
                permit_status, verification_status, officer_notes, officer_badge_id, location_name,
                user_portrait_photo, registration_id, created_at
            ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $id, $plate, $name, $phone, $category, $engine,
            $permitStatus, $verifStatus, $notes, $userBadge, $location,
            $portrait, $regId
        ]);

        echo json_encode(['success' => true, 'id' => $id]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
