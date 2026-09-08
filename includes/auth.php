<?php
/**
 * Authentication & Session Management
 * Bahir Dar Motorcycle Permit & Enforcement System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class Auth {
    /**
     * Check if user is currently signed in
     */
    public static function isLoggedIn(): bool {
        return !empty($_SESSION['user_id']) && !empty($_SESSION['badge_id']);
    }

    /**
     * Get current user session data
     */
    public static function user(): ?array {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? '',
            'badgeId' => $_SESSION['badge_id'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'fullName' => $_SESSION['full_name'] ?? 'System User',
            'role' => $_SESSION['role'] ?? 'clerk',
            'subCity' => $_SESSION['sub_city'] ?? 'Central Command'
        ];
    }

    /**
     * Get user role
     */
    public static function role(): string {
        return $_SESSION['role'] ?? 'clerk';
    }

    /**
     * Get user badge ID
     */
    public static function badgeId(): string {
        return $_SESSION['badge_id'] ?? '';
    }

    /**
     * Require active login session
     */
    public static function requireLogin(bool $isAjax = false): void {
        if (!self::isLoggedIn()) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required. Session expired.']);
                exit;
            } else {
                $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
                $isInPages = (strpos($scriptPath, '/pages/') !== false);
                $target = $isInPages ? '../login.php' : 'login.php';
                header("Location: $target");
                exit;
            }
        }
    }

    /**
     * Require specific roles
     */
    public static function requireRoles(array $allowedRoles, bool $isAjax = false): void {
        self::requireLogin($isAjax);
        $userRole = self::role();
        if (!in_array($userRole, $allowedRoles, true)) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied: insufficient permissions.']);
                exit;
            } else {
                die('<h1>Access Denied</h1><p>Your role does not have permission to view this section.</p>');
            }
        }
    }

    /**
     * Attempt login by Badge ID or Email
     */
    public static function login(string $badgeOrEmail, string $password = '', ?string $roleHint = null): array {
        $cleanInput = trim($badgeOrEmail);
        if (empty($cleanInput)) {
            return ['success' => false, 'error' => 'Badge ID or Email is required'];
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(badge_id) = LOWER(?) OR LOWER(email) = LOWER(?) LIMIT 1");
            $stmt->execute([$cleanInput, $cleanInput]);
            $user = $stmt->fetch();

            if (!$user) {
                // Infer role for demo or new system login
                $userRole = $roleHint ?: 'clerk';
                $fullName = 'System Clerk';
                $upperBadge = strtoupper($cleanInput);

                if (str_contains($upperBadge, 'SUPER') || $upperBadge === 'SUPER-ADMIN-01') {
                    $userRole = 'superadmin';
                    $fullName = 'ካሌብ ታደሰ (Kaleb Tadesse - Chief Super Admin)';
                } elseif (str_contains($upperBadge, 'ADMIN') || $upperBadge === 'ADMIN-001') {
                    $userRole = 'admin';
                    $fullName = 'ዳዊት ኃይሌ (Dawit Haile)';
                } elseif (str_contains($upperBadge, 'OFFICER') || $upperBadge === 'OFFICER-442') {
                    $userRole = 'officer';
                    $fullName = 'አበበ ደስታ (Abebe Desta)';
                } else {
                    $fullName = 'ሳራ ተሾመ (Sara Teshome)';
                }

                $email = str_contains($cleanInput, '@') ? $cleanInput : strtolower($cleanInput) . '@permit.gov.et';
                $userId = 'user-' . $userRole . '-' . $cleanInput;
                $defaultHash = password_hash($password ?: 'admin123', PASSWORD_BCRYPT);

                $insertStmt = $pdo->prepare("
                    INSERT INTO users (id, badge_id, email, password_hash, full_name, role, sub_city, status, last_login_at, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'Central Command', 'active', NOW(), NOW())
                ");
                $insertStmt->execute([$userId, $cleanInput, $email, $defaultHash, $fullName, $userRole]);

                $user = [
                    'id' => $userId,
                    'badge_id' => $cleanInput,
                    'email' => $email,
                    'full_name' => $fullName,
                    'role' => $userRole,
                    'sub_city' => 'Central Command',
                    'status' => 'active'
                ];
            } else {
                if ($user['status'] === 'disabled') {
                    return ['success' => false, 'error' => 'This account has been disabled by System Administrator'];
                }

                // If password is provided, verify against password_hash (or allow demo passwords)
                if (!empty($password) && !empty($user['password_hash'])) {
                    // Check standard bcrypt verification, or allow demo password override
                    if (!password_verify($password, $user['password_hash']) &&
                        $password !== 'admin123' && $password !== 'clerk123' && $password !== 'officer123' && $password !== 'password123') {
                        return ['success' => false, 'error' => 'Invalid badge ID or password'];
                    }
                }

                // Update last login
                $upStmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
                $upStmt->execute([$user['id']]);
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['badge_id'] = $user['badge_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['sub_city'] = $user['sub_city'] ?? 'Central Command';

            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'badgeId' => $user['badge_id'],
                    'email' => $user['email'],
                    'fullName' => $user['full_name'],
                    'role' => $user['role'],
                    'subCity' => $user['sub_city'] ?? 'Central Command'
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Login error: ' . $e->getMessage()];
        }
    }

    /**
     * Switch role (for testing and multi-role admins)
     */
    public static function switchRole(string $newRole): array {
        if (!self::isLoggedIn()) {
            return ['success' => false, 'error' => 'Not logged in'];
        }

        $validRoles = ['clerk', 'officer', 'admin', 'superadmin', 'it_specialist'];
        if (!in_array($newRole, $validRoles, true)) {
            return ['success' => false, 'error' => 'Invalid role specified'];
        }

        $_SESSION['role'] = $newRole;

        return ['success' => true, 'role' => $newRole];
    }

    /**
     * End user session
     */
    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }
}
