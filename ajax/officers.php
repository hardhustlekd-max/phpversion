<?php
/**
 * AJAX Handler: Field Officers & Shift Assignments
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'save';
$body = getRequestBody();

try {
    $pdo = Database::getConnection();

    switch ($action) {
        case 'save':
            if (empty($body['id'])) {
                jsonResponse(['success' => false, 'error' => 'Missing officer ID'], 400);
            }

            $id = sanitize($body['id']);
            $officerName = sanitize($body['officerName'] ?? '');
            $badgeId = strtoupper(sanitize($body['badgeId'] ?? ''));
            $subCity = sanitize($body['subCity'] ?? 'Belay Zeleke');
            $locationName = sanitize($body['locationName'] ?? '');
            $shift = $body['shift'] ?? 'morning';
            $status = $body['status'] ?? 'active';
            $assignedLocation = sanitize($body['assignedLocation'] ?? $locationName);
            $phone = sanitize($body['phone'] ?? '');
            $shiftHours = sanitize($body['shiftHours'] ?? '08:00 AM - 04:00 PM');
            $assignedDate = sanitize($body['assignedDate'] ?? date('Y-m-d'));

            $stmt = $pdo->prepare("SELECT id FROM officers WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->fetch()) {
                $sql = "UPDATE officers SET officer_name = ?, badge_id = ?, sub_city = ?, location_name = ?,
                        shift = ?, status = ?, assigned_location = ?, phone = ?, shift_hours = ?,
                        assigned_date = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$officerName, $badgeId, $subCity, $locationName, $shift, $status, $assignedLocation, $phone, $shiftHours, $assignedDate, $id]);
            } else {
                $sql = "INSERT INTO officers (id, officer_name, badge_id, sub_city, location_name, shift, status, assigned_location, phone, shift_hours, assigned_date, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id, $officerName, $badgeId, $subCity, $locationName, $shift, $status, $assignedLocation, $phone, $shiftHours, $assignedDate]);
            }

            logAuditAction('SAVE_OFFICER', "Saved officer assignment {$officerName} ({$badgeId})", 'info');
            jsonResponse(['success' => true, 'id' => $id]);
            break;

        case 'update':
            $id = sanitize($body['id'] ?? '');
            $updates = $body['updates'] ?? [];
            if (empty($id) || empty($updates)) {
                jsonResponse(['success' => false, 'error' => 'Missing ID or updates'], 400);
            }

            $fields = [];
            $params = [];
            $allowedMap = [
                'officerName' => 'officer_name',
                'badgeId' => 'badge_id',
                'subCity' => 'sub_city',
                'locationName' => 'location_name',
                'shift' => 'shift',
                'status' => 'status',
                'assignedLocation' => 'assigned_location',
                'phone' => 'phone',
                'shiftHours' => 'shift_hours',
            ];

            foreach ($updates as $key => $val) {
                if (isset($allowedMap[$key])) {
                    $fields[] = "{$allowedMap[$key]} = ?";
                    $params[] = $val;
                }
            }

            if (!empty($fields)) {
                $params[] = $id;
                $sql = "UPDATE officers SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }

            jsonResponse(['success' => true]);
            break;

        case 'delete':
            $id = sanitize($_GET['id'] ?? $body['id'] ?? '');
            $stmt = $pdo->prepare("DELETE FROM officers WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(['success' => true]);
            break;

        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
