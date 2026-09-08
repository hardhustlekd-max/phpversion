<?php
/**
 * AJAX Auth Controller
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    if ($action === 'login') {
        $badge = $input['badge_id'] ?? $input['badgeId'] ?? '';
        $pass = $input['password'] ?? '';
        $role = $input['role'] ?? null;

        $res = Auth::login($badge, $pass, $role);
        echo json_encode($res);
        exit;
    }

    if ($action === 'switch_role') {
        $role = $input['role'] ?? 'clerk';
        $res = Auth::switchRole($role);
        echo json_encode($res);
        exit;
    }
}

if ($action === 'logout') {
    Auth::logout();
    header('Location: ../index.php?page=login');
    exit;
}

if ($action === 'check') {
    echo json_encode([
        'loggedIn' => Auth::isLoggedIn(),
        'user' => Auth::user()
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
