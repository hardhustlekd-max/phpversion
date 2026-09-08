<?php
/**
 * AJAX System Users Management Controller
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/rbac.php';

Auth::requireLogin(true);
$userRole = Auth::role();
if (!RBAC::isAllowed($userRole, 13) && $userRole !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Permission denied: Task 13 (User Management)']);
    exit;
}

$pdo = Database::getConnection();
$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $pdo->query("SELECT id, badge_id, email, full_name, role, sub_city, status, last_login_at, created_at FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll();
        echo json_encode(['success' => true, 'users' => $users]);
        exit;
    }

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $badge = strtoupper(trim($input['badge_id'] ?? ''));
        $email = trim($input['email'] ?? '');
        $name = trim($input['full_name'] ?? '');
        $role = trim($input['role'] ?? 'clerk');
        $subCity = trim($input['sub_city'] ?? 'Central Command');
        $password = $input['password'] ?? 'admin123';

        if (empty($badge) || empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Badge ID and Full Name are required']);
            exit;
        }

        $id = 'user-' . $role . '-' . $badge;
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("
            INSERT INTO users (id, badge_id, email, password_hash, full_name, role, sub_city, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([$id, $badge, $email, $hash, $name, $role, $subCity]);

        echo json_encode(['success' => true, 'id' => $id]);
        exit;
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = $input['id'] ?? '';
        $role = $input['role'] ?? null;
        $status = $input['status'] ?? null;
        $password = $input['password'] ?? null;

        if ($role) {
            $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$role, $id]);
        }
        if ($status) {
            $pdo->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$status, $id]);
        }
        if ($password) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $id]);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = $input['id'] ?? '';

        // Protect chief super admin from deletion
        if (str_contains($id, 'SUPER-ADMIN-01')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Chief Super Admin account cannot be deleted']);
            exit;
        }

        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
