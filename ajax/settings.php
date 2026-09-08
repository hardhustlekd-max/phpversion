<?php
/**
 * AJAX System Settings Controller
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireLogin(true);

$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'get';

try {
    if ($action === 'get') {
        $stmt = $pdo->query("SELECT * FROM system_settings WHERE id = 'global_config' LIMIT 1");
        $settings = $stmt->fetch();
        if ($settings && !empty($settings['frozen_subcities'])) {
            $settings['frozen_subcities'] = json_decode($settings['frozen_subcities'], true);
        }
        echo json_encode(['success' => true, 'settings' => $settings]);
        exit;
    }

    if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $officer = $input['officer_name'] ?? 'አበበ ደስታ (Abebe Desta)';
        $dept = $input['department'] ?? 'የትራፊክ ማኔጅመንትና ህግ ማስከበሪያ (Traffic Mgmt & Enforcement)';
        $subCityOffice = $input['sub_city_office'] ?? 'በላይ ዘለቀ ክፍለ ከተማ (Belay Zeleke)';
        $printer = $input['default_printer'] ?? 'Zebra ZD621 Industrial PVC Card Printer';
        $stock = $input['card_stock_type'] ?? 'CR80 Standard PVC Card (85.6 x 54 mm)';
        $calendar = $input['calendar_system'] ?? 'ethiopian';
        $autoPrint = !empty($input['auto_print_qr']) ? 1 : 0;
        $emailAlerts = !empty($input['email_alerts']) ? 1 : 0;
        $security2fa = !empty($input['security_2fa']) ? 1 : 0;
        $highRiskAlerts = !empty($input['high_risk_alerts']) ? 1 : 0;

        // Clerk visibility permissions
        $showClerkPermit = !empty($input['show_clerk_permit_status']) ? 1 : 0;
        $showClerkSubmissions = !empty($input['show_clerk_submissions_action']) ? 1 : 0;
        $showClerkApproved = !empty($input['show_clerk_approved_vehicles_action']) ? 1 : 0;
        $showClerkPaymentKpis = !empty($input['show_clerk_payment_kpis']) ? 1 : 0;
        $showClerkPaymentTable = !empty($input['show_clerk_payment_records_table']) ? 1 : 0;
        $clerkKpiPerm = $input['clerk_payment_kpi_permission'] ?? 'deny';
        $clerkTablePerm = $input['clerk_payment_table_permission'] ?? 'deny';

        $frozenSubcities = isset($input['frozen_subcities']) ? json_encode($input['frozen_subcities']) : null;

        $exists = $pdo->query("SELECT id FROM system_settings WHERE id = 'global_config' LIMIT 1")->fetch();
        if ($exists) {
            $stmt = $pdo->prepare("
                UPDATE system_settings SET
                    officer_name = ?, department = ?, sub_city_office = ?, default_printer = ?,
                    card_stock_type = ?, calendar_system = ?, auto_print_qr = ?, email_alerts = ?,
                    security_2fa = ?, high_risk_alerts = ?, show_clerk_permit_status = ?,
                    show_clerk_submissions_action = ?, show_clerk_approved_vehicles_action = ?,
                    show_clerk_payment_kpis = ?, show_clerk_payment_records_table = ?,
                    clerk_payment_kpi_permission = ?, clerk_payment_table_permission = ?,
                    frozen_subcities = COALESCE(?, frozen_subcities)
                WHERE id = 'global_config'
            ");
            $stmt->execute([
                $officer, $dept, $subCityOffice, $printer,
                $stock, $calendar, $autoPrint, $emailAlerts,
                $security2fa, $highRiskAlerts, $showClerkPermit,
                $showClerkSubmissions, $showClerkApproved,
                $showClerkPaymentKpis, $showClerkPaymentTable,
                $clerkKpiPerm, $clerkTablePerm, $frozenSubcities
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO system_settings (
                    id, officer_name, department, sub_city_office, default_printer,
                    card_stock_type, calendar_system, auto_print_qr, email_alerts,
                    security_2fa, high_risk_alerts, show_clerk_permit_status,
                    show_clerk_submissions_action, show_clerk_approved_vehicles_action,
                    show_clerk_payment_kpis, show_clerk_payment_records_table,
                    clerk_payment_kpi_permission, clerk_payment_table_permission,
                    frozen_subcities, updated_at
                ) VALUES (
                    'global_config', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                )
            ");
            $stmt->execute([
                $officer, $dept, $subCityOffice, $printer,
                $stock, $calendar, $autoPrint, $emailAlerts,
                $security2fa, $highRiskAlerts, $showClerkPermit,
                $showClerkSubmissions, $showClerkApproved,
                $showClerkPaymentKpis, $showClerkPaymentTable,
                $clerkKpiPerm, $clerkTablePerm, $frozenSubcities ?? '[]'
            ]);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
