<?php
/**
 * AJAX RBAC Matrix API Controller
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/rbac.php';

Auth::requireLogin(true);
$userRole = Auth::role();
$action = $_GET['action'] ?? 'get';

try {
    if ($action === 'get') {
        $matrix = RBAC::getMatrix();
        echo json_encode([
            'success' => true,
            'matrix' => $matrix,
            'tasks' => SYSTEM_TASKS
        ]);
        exit;
    }

    if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!RBAC::isAllowed($userRole, 14) && $userRole !== 'superadmin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Permission denied: Task 14 (Permission Management)']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $matrix = $input['matrix'] ?? [];

        if (empty($matrix) || !is_array($matrix)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid matrix payload']);
            exit;
        }

        $ok = RBAC::saveMatrix($matrix);
        echo json_encode(['success' => $ok]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
