<?php
/**
 * AJAX Handler: Payment Receipts & Municipal Permit Fees
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
            $id = sanitize($body['id'] ?? ('rcpt-' . time() . '-' . rand(100, 999)));
            $receiptNumber = sanitize($body['receiptNumber'] ?? '');
            $ownerRegId = sanitize($body['ownerRegistrationId'] ?? '');
            $ownerName = sanitize($body['ownerName'] ?? '');
            $plateNumber = strtoupper(sanitize($body['plateNumber'] ?? ''));
            $phone = sanitize($body['phone'] ?? '');
            $paymentDate = sanitize($body['paymentDate'] ?? date('Y-m-d'));
            $expirationDate = sanitize($body['expirationDate'] ?? date('Y-m-d', strtotime('+1 year')));
            $amount = (float)($body['amount'] ?? 0.00);
            $notes = sanitize($body['notes'] ?? '');
            $enteredBy = sanitize($body['enteredBy'] ?? 'CLERK-001');

            $screenshot = saveBase64Image($body['receiptScreenshot'] ?? '', 'receipts');

            $stmt = $pdo->prepare("SELECT id FROM payment_receipts WHERE receipt_number = ?");
            $stmt->execute([$receiptNumber]);
            if ($stmt->fetch()) {
                $update = $pdo->prepare("UPDATE payment_receipts SET
                    owner_registration_id = ?, owner_name = ?, plate_number = ?, phone = ?,
                    payment_date = ?, expiration_date = ?, amount = ?, receipt_screenshot = COALESCE(?, receipt_screenshot),
                    notes = ?, entered_by = ? WHERE receipt_number = ?");
                $update->execute([$ownerRegId, $ownerName, $plateNumber, $phone, $paymentDate, $expirationDate, $amount, $screenshot, $notes, $enteredBy, $receiptNumber]);
            } else {
                $insert = $pdo->prepare("INSERT INTO payment_receipts (
                    id, receipt_number, owner_registration_id, owner_name, plate_number, phone,
                    payment_date, expiration_date, amount, receipt_screenshot, notes, entered_by, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $insert->execute([$id, $receiptNumber, $ownerRegId, $ownerName, $plateNumber, $phone, $paymentDate, $expirationDate, $amount, $screenshot, $notes, $enteredBy]);
            }

            logAuditAction('SAVE_PAYMENT_RECEIPT', "Saved municipal fee receipt {$receiptNumber} for {$ownerName} ({$plateNumber})", 'info');
            jsonResponse(['success' => true, 'id' => $id, 'receiptNumber' => $receiptNumber]);
            break;

        case 'delete':
            $id = sanitize($_GET['id'] ?? $body['id'] ?? '');
            $stmt = $pdo->prepare("DELETE FROM payment_receipts WHERE id = ? OR receipt_number = ?");
            $stmt->execute([$id, $id]);
            logAuditAction('DELETE_PAYMENT_RECEIPT', "Deleted payment receipt {$id}", 'warning');
            jsonResponse(['success' => true]);
            break;

        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
