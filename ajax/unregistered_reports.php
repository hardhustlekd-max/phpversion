<?php
/**
 * AJAX Handler: Unregistered Vehicle Citation & Impound Reports
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
            $id = sanitize($body['id'] ?? ('unreg-' . time() . '-' . rand(100, 999)));
            $reportedAt = sanitize($body['reportedAt'] ?? date('Y-m-d H:i:s'));
            $plateNumber = strtoupper(sanitize($body['plateNumber'] ?? ''));
            $driverName = sanitize($body['driverName'] ?? '');
            $driverPhone = sanitize($body['driverPhone'] ?? '');
            $vehicleCategory = $body['vehicleCategory'] ?? 'electric';
            $engineOrSerialNo = sanitize($body['engineOrSerialNo'] ?? '');
            $chassisNumber = sanitize($body['chassisNumber'] ?? '');
            $motorBrand = sanitize($body['motorBrand'] ?? '');
            $subCity = sanitize($body['subCity'] ?? 'Belay Zeleke');
            $locationName = sanitize($body['locationName'] ?? '');
            $officerBadgeId = strtoupper(sanitize($body['officerBadgeId'] ?? ''));
            $officerName = sanitize($body['officerName'] ?? '');
            $notes = sanitize($body['notes'] ?? '');
            $status = $body['status'] ?? 'pending';
            $resolutionNotes = sanitize($body['resolutionNotes'] ?? '');

            $evidencePhoto = saveBase64Image($body['evidencePhoto'] ?? '', 'evidence');

            $stmt = $pdo->prepare("INSERT INTO unregistered_reports (
                id, reported_at, plate_number, driver_name, driver_phone, vehicle_category,
                engine_or_serial_no, chassis_number, motor_brand, sub_city, location_name,
                officer_badge_id, officer_name, notes, evidence_photo, status, resolution_notes, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            $stmt->execute([
                $id, $reportedAt, $plateNumber, $driverName, $driverPhone, $vehicleCategory,
                $engineOrSerialNo, $chassisNumber, $motorBrand, $subCity, $locationName,
                $officerBadgeId, $officerName, $notes, $evidencePhoto, $status, $resolutionNotes
            ]);

            logAuditAction('REPORT_UNREGISTERED', "Unregistered vehicle report logged by {$officerBadgeId} at {$locationName}", 'warning');
            jsonResponse(['success' => true, 'id' => $id]);
            break;

        case 'update_status':
            $id = sanitize($body['id'] ?? '');
            $status = $body['status'] ?? 'under_investigation';
            $resolutionNotes = sanitize($body['resolutionNotes'] ?? '');

            $stmt = $pdo->prepare("UPDATE unregistered_reports SET status = ?, resolution_notes = ? WHERE id = ?");
            $stmt->execute([$status, $resolutionNotes, $id]);

            logAuditAction('UPDATE_UNREGISTERED_REPORT', "Report {$id} updated to {$status}", 'info');
            jsonResponse(['success' => true]);
            break;

        case 'delete':
            $id = sanitize($_GET['id'] ?? $body['id'] ?? '');
            $stmt = $pdo->prepare("DELETE FROM unregistered_reports WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(['success' => true]);
            break;

        default:
            jsonResponse(['success' => false, 'error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
