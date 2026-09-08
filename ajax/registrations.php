<?php
/**
 * AJAX Motorcycle Registrations Controller
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/rbac.php';

Auth::requireLogin(true);

$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$userBadge = Auth::badgeId();
$action = $_GET['action'] ?? 'list';

try {
    // 1. LIST REGISTRATIONS
    if ($action === 'list') {
        $status = $_GET['status'] ?? null;
        $subCity = $_GET['sub_city'] ?? null;
        $search = $_GET['search'] ?? null;
        $todayOnly = !empty($_GET['today']);

        $query = "SELECT * FROM motorcycle_registrations WHERE 1=1";
        $params = [];

        // Scoping for non-superadmins: hide hidden records
        if ($userRole !== 'superadmin' && $userRole !== 'super_admin') {
            $query .= " AND hide_from_other_users = 0";
        }

        // Scoping for clerks: only see records registered by them
        if ($userRole === 'clerk') {
            $query .= " AND (LOWER(registered_by) = LOWER(?) OR registered_by = '')";
            $params[] = $userBadge;
        }

        if (!empty($status) && $status !== 'all') {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        if (!empty($subCity) && $subCity !== 'all') {
            $query .= " AND sub_city = ?";
            $params[] = $subCity;
        }

        if ($todayOnly) {
            $query .= " AND (DATE(created_at) = CURDATE() OR registration_date = CURDATE())";
        }

        if (!empty($search)) {
            $query .= " AND (plate_number LIKE ? OR full_name LIKE ? OR phone LIKE ? OR engine_or_serial_no LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $query .= " ORDER BY created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        echo json_encode(['success' => true, 'registrations' => $rows]);
        exit;
    }

    // 2. GET SINGLE REGISTRATION
    if ($action === 'get') {
        $id = $_GET['id'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM motorcycle_registrations WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Registration not found']);
            exit;
        }
        echo json_encode(['success' => true, 'registration' => $row]);
        exit;
    }

    // 3. LOOKUP BY PLATE OR QR
    if ($action === 'lookup') {
        $queryStr = trim($_GET['query'] ?? '');
        if (empty($queryStr)) {
            echo json_encode(['success' => false, 'error' => 'No query provided']);
            exit;
        }

        // Check if QR URL or ID or Plate
        $cleanPlate = preg_replace('/^https?:\/\/[^\/]+\/verify\//i', '', $queryStr);
        $stmt = $pdo->prepare("
            SELECT * FROM motorcycle_registrations 
            WHERE plate_number = ? OR id = ? OR qr_code_data = ? OR engine_or_serial_no = ? 
            LIMIT 1
        ");
        $stmt->execute([$cleanPlate, $cleanPlate, $queryStr, $cleanPlate]);
        $row = $stmt->fetch();

        echo json_encode(['success' => true, 'registration' => $row ?: null]);
        exit;
    }

    // 4. CREATE REGISTRATION
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!RBAC::isAllowed($userRole, 1) && $userRole !== 'superadmin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Permission denied: Task 1']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $id = 'REG-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $fullName = trim($input['full_name'] ?? $input['fullName'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $plateNumber = strtoupper(trim($input['plate_number'] ?? $input['plateNumber'] ?? ''));
        $engineNo = trim($input['engine_or_serial_no'] ?? $input['engineOrSerialNo'] ?? '');
        $category = $input['vehicle_category'] ?? $input['vehicleCategory'] ?? 'electric';
        $brand = $input['motor_brand'] ?? $input['motorBrand'] ?? '';
        $model = $input['motorModel'] ?? $input['motor_model'] ?? '';
        $subCity = $input['sub_city'] ?? $input['subCity'] ?? 'Fasilo';
        $blood = $input['blood_group'] ?? $input['bloodGroup'] ?? 'O+';
        $chassis = $input['chassis_number'] ?? $input['chassisNumber'] ?? '';
        $receiptNo = $input['receipt_number'] ?? $input['receiptNumber'] ?? '';
        $paymentAmount = $input['payment_amount'] ?? $input['paymentAmount'] ?? '850.00';

        $portrait = $input['user_portrait_photo'] ?? $input['userPortraitPhoto'] ?? 'image/app/logo.png';
        $nationalId = $input['national_id_photo'] ?? $input['nationalIdPhoto'] ?? '';
        $license = $input['driving_license_photo'] ?? $input['drivingLicensePhoto'] ?? '';
        $permit = $input['driving_permit_photo'] ?? $input['drivingPermitPhoto'] ?? '';

        $qrData = "https://enforcement.gov.et/verify/{$plateNumber}";

        $stmt = $pdo->prepare("
            INSERT INTO motorcycle_registrations (
                id, full_name, phone, user_portrait_photo, national_id_photo, driving_license_photo, driving_permit_photo,
                vehicle_category, motor_brand, motorModel, chassis_number, engine_or_serial_no, plate_number,
                registration_date, status, qr_code_data, registered_by, sub_city, blood_group, receipt_number, payment_amount, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'pending_approval', ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $id, $fullName, $phone, $portrait, $nationalId, $license, $permit,
            $category, $brand, $model, $chassis, $engineNo, $plateNumber,
            $qrData, $userBadge, $subCity, $blood, $receiptNo, $paymentAmount
        ]);

        // If receipt was provided, also create payment receipt entry
        if (!empty($receiptNo)) {
            $rcptStmt = $pdo->prepare("
                INSERT INTO payment_receipts (id, receipt_number, owner_registration_id, owner_name, plate_number, phone, payment_date, expiration_date, amount, entered_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), ?, ?, NOW())
                ON DUPLICATE KEY UPDATE amount = VALUES(amount)
            ");
            $rcptStmt->execute([
                'RCPT-' . bin2hex(random_bytes(4)),
                $receiptNo,
                $id,
                $fullName,
                $plateNumber,
                $phone,
                $paymentAmount,
                $userBadge
            ]);
        }

        echo json_encode(['success' => true, 'id' => $id, 'plateNumber' => $plateNumber]);
        exit;
    }

    // 5. UPDATE STATUS (approve, reject, ordered_print, printed)
    if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = $input['id'] ?? '';
        $newStatus = $input['status'] ?? '';
        $reason = $input['reason'] ?? null;

        $validStatuses = ['pending_approval', 'approved', 'rejected', 'ordered_print', 'printed'];
        if (!in_array($newStatus, $validStatuses, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid status']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE motorcycle_registrations SET status = ?, rejection_reason = ? WHERE id = ?");
        $stmt->execute([$newStatus, $reason, $id]);

        echo json_encode(['success' => true, 'status' => $newStatus]);
        exit;
    }

    // 6. BATCH APPROVE
    if ($action === 'batch_approve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $ids = $input['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No registration IDs supplied']);
            exit;
        }

        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("UPDATE motorcycle_registrations SET status = 'approved' WHERE id IN ($placeholders)");
        $stmt->execute($ids);

        echo json_encode(['success' => true, 'count' => count($ids)]);
        exit;
    }

    // 7. DELETE REGISTRATION
    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!RBAC::isAllowed($userRole, 11) && $userRole !== 'superadmin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Permission denied: Task 11 (Delete Member)']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = $input['id'] ?? '';

        $stmt = $pdo->prepare("DELETE FROM motorcycle_registrations WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
