<?php
/**
 * AJAX Auth Actions Controller & Bridge
 * Supports both JSON API calls and standard HTTP browser redirects
 */

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Handle Logout
if ($action === 'logout') {
    if (class_exists('Auth')) {
        Auth::logout();
    } else {
        $_SESSION = [];
        if (session_id()) session_destroy();
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'redirect' => 'pages/login.php']);
        exit;
    }

    $target = file_exists(__DIR__ . '/../login.php') ? '../login.php' : '../pages/login.php';
    header("Location: $target");
    exit;
}

// Handle Check Session
if ($action === 'check') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'loggedIn' => class_exists('Auth') ? Auth::isLoggedIn() : !empty($_SESSION['user_id']),
        'user' => class_exists('Auth') ? Auth::user() : null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Handle Login / Switch Role (POST)
header('Content-Type: application/json; charset=utf-8');
$raw = file_get_contents('php://input');
$input = (!empty($raw) ? json_decode($raw, true) : null) ?: $_POST;

if ($action === 'login') {
    $badge = trim($input['badge_id'] ?? $input['badgeId'] ?? '');
    $pass = $input['password'] ?? '';
    $role = $input['role'] ?? null;

    if (empty($badge)) {
        echo json_encode(['success' => false, 'error' => 'Badge ID or Email is required']);
        exit;
    }

    $res = Auth::login($badge, $pass, $role);
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'switch_role') {
    $role = $input['role'] ?? 'clerk';
    $res = Auth::switchRole($role);
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action: ' . htmlspecialchars($action)]);
