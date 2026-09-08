<?php
/**
 * AJAX Payment Receipts Controller
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireLogin(true);

$pdo = Database::getConnection();
$currentUser = Auth::user();
$userRole = Auth::role();
$userBadge = Auth::badgeId();
$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        $search = $_GET['search'] ?? null;
        $query = "SELECT * FROM payment_receipts WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (receipt_number LIKE ? OR owner_name LIKE ? OR plate_number LIKE ? OR phone LIKE ?)";
            $term = "%{$search}%";
            $params = [$term, $term, $term, $term];
        }

        $query .= " ORDER BY created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $receipts = $stmt->fetchAll();

        // Calculate KPI statuses: active, expiring_soon (< 30 days), expired (< 0 days)
        $now = new DateTime();
        $total = count($receipts);
        $activeCount = 0;
        $expiringSoonCount = 0;
        $expiredCount = 0;

        foreach ($receipts as &$rc) {
            $expDate = new DateTime($rc['expiration_date']);
            $diffDays = (int)$now->diff($expDate)->format('%r%a');

            if ($diffDays < 0) {
                $rc['payment_status'] = 'expired';
                $rc['days_remaining'] = $diffDays;
                $expiredCount++;
            } elseif ($diffDays <= 30) {
                $rc['payment_status'] = 'expiring_soon';
                $rc['days_remaining'] = $diffDays;
                $expiringSoonCount++;
            } else {
                $rc['payment_status'] = 'active';
                $rc['days_remaining'] = $diffDays;
                $activeCount++;
            }
        }

        echo json_encode([
            'success' => true,
            'receipts' => $receipts,
            'metrics' => [
                'total' => $total,
                'active' => $activeCount,
                'expiring_soon' => $expiringSoonCount,
                'expired' => $expiredCount
            ]
        ]);
        exit;
    }

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $receiptNo = trim($input['receipt_number'] ?? '');
        $ownerName = trim($input['owner_name'] ?? '');
        $plate = strtoupper(trim($input['plate_number'] ?? ''));
        $phone = trim($input['phone'] ?? '');
        $amount = (float)($input['amount'] ?? 850.00);
        $notes = trim($input['notes'] ?? '');
        $payDate = $input['payment_date'] ?? date('Y-m-d');
        $expDate = $input['expiration_date'] ?? date('Y-m-d', strtotime('+1 year'));
        $regId = $input['owner_registration_id'] ?? null;
        $screenshot = $input['receipt_screenshot'] ?? null;

        $id = 'RCPT-' . bin2hex(random_bytes(4));

        $stmt = $pdo->prepare("
            INSERT INTO payment_receipts (id, receipt_number, owner_registration_id, owner_name, plate_number, phone, payment_date, expiration_date, amount, receipt_screenshot, notes, entered_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$id, $receiptNo, $regId, $ownerName, $plate, $phone, $payDate, $expDate, $amount, $screenshot, $notes, $userBadge]);

        echo json_encode(['success' => true, 'id' => $id, 'receiptNumber' => $receiptNo]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
