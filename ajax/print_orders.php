<?php
/**
 * AJAX Handler: Print Orders (Batch Card Print Orders)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'create';
$body = getRequestBody();

try {
    $pdo = Database::getConnection();

    switch ($action) {
        case 'create':
            $id = sanitize($body['id'] ?? ('order-' . time() . '-' . rand(100, 999)));
            $orderDate = sanitize($body['orderDate'] ?? date('Y-m-d'));
            $registrationIds = $body['registrationIds'] ?? [];
            $totalItems = count($registrationIds);
            $status = $body['status'] ?? 'pending';
            $notes = sanitize($body['notes'] ?? '');

            $jsonIds = json_encode($registrationIds, JSON_UNESCAPED_UNICODE);

            $stmt = $pdo->prepare("INSERT INTO print_orders (id, order_date, total_items, registration_ids, status, notes, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$id, $orderDate, $totalItems, $jsonIds, $status, $notes]);

            // Also update the status of those registrations to 'ordered_print'
            if (!empty($registrationIds)) {
                $placeholders = str_repeat('?,', count($registrationIds) - 1) . '?';
                $updateStmt = $pdo->prepare("UPDATE registrations SET status = 'ordered_print', updated_at = NOW() WHERE id IN ($placeholders)");
                $updateStmt->execute($registrationIds);
            }

            logAuditAction('CREATE_PRINT_ORDER', "Created batch print order {$id} with {$totalItems} cards", 'info');
            jsonResponse(['success' => true, 'id' => $id]);
            break;

        case 'update_status':
            $id = sanitize($body['id'] ?? '');
            $status = $body['status'] ?? 'completed';

            if (empty($id)) {
                jsonResponse(['success' => false, 'error' => 'Missing order ID'], 400);
            }

            $stmt = $pdo->prepare("UPDATE print_orders SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $id]);

            // If status is completed, update registration status to 'printed'
            if ($status === 'completed') {
                $fetchStmt = $pdo->prepare("SELECT registration_ids FROM print_orders WHERE id = ?");
                $fetchStmt->execute([$id]);
                $row = $fetchStmt->fetch();
                if ($row) {
                    $regIds = json_decode($row['registration_ids'] ?: '[]', true);
                    if (!empty($regIds) && is_array($regIds)) {
                        $placeholders = str_repeat('?,', count($regIds) - 1) . '?';
                        $updateStmt = $pdo->prepare("UPDATE registrations SET status = 'printed', updated_at = NOW() WHERE id IN ($placeholders)");
                        $updateStmt->execute($regIds);
                    }
                }
            }

            logAuditAction('UPDATE_PRINT_ORDER', "Batch print order {$id} status updated to {$status}", 'info');
            jsonResponse(['success' => true]);
            break;

        case 'list':
            $stmt = $pdo->query("SELECT * FROM print_orders ORDER BY order_date DESC");
            $orders = $stmt->fetchAll();
            jsonResponse(['success' => true, 'orders' => $orders]);
            break;

        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
