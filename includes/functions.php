<?php
/**
 * System Core Helper Functions
 * Hardened for PHP 8.3 and InfinityFree Shared Hosting
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

/**
 * Safe HTML Escape Helper for PHP 8.1 / 8.2 / 8.3
 * Prevents deprecation notices when null is passed
 */
function h(?string $str, string $default = ''): string {
    return htmlspecialchars($str ?? $default, ENT_QUOTES, 'UTF-8');
}

/**
 * Return Standard JSON Response
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Parse JSON or Form Request Body
 */
function getRequestBody(): array {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $json = json_decode($rawInput, true);
        if (is_array($json)) {
            return $json;
        }
    }
    return $_POST ?: [];
}

/**
 * Sanitize User String
 */
function sanitize(?string $val): string {
    return trim(htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8'));
}

/**
 * Get currently authenticated user safely
 */
function getCurrentUser(): ?array {
    return class_exists('Auth') ? Auth::user() : null;
}

/**
 * Log System Security Audit Action
 */
function logAuditAction(string $action, string $details, string $severity = 'info'): void {
    try {
        $pdo = Database::getConnection();
        $currentUser = getCurrentUser();
        $badgeId = $currentUser ? ($currentUser['badgeId'] ?? $currentUser['badge_id'] ?? 'SYSTEM') : 'GUEST';
        $role = $currentUser ? ($currentUser['role'] ?? 'clerk') : 'clerk';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $id = 'audit-' . time() . '-' . substr(bin2hex(random_bytes(4)), 0, 6);

        $stmt = $pdo->prepare(
            "INSERT INTO audit_logs (id, timestamp, actor_badge_id, actor_role, action, details, ip_address, severity)
             VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$id, $badgeId, $role, $action, $details, $ip, $severity]);
    } catch (Exception $e) {
        error_log('Audit log failure: ' . $e->getMessage());
    }
}

/**
 * Check Role Permission for a given task ID (1-16)
 * Return type is 'bool' (compliant with PHP 8.3)
 */
function isTaskAllowed(int $taskId, ?string $userRole = null): bool {
    if (!$userRole) {
        $currentUser = getCurrentUser();
        $userRole = $currentUser['role'] ?? 'clerk';
    }

    if ($userRole === 'superadmin' || $userRole === 'super_admin') {
        return true;
    }

    $roleMap = [
        'clerk' => 'role-secretary',
        'officer' => 'role-officer',
        'admin' => 'role-manager',
        'superadmin' => 'role-superadmin',
    ];
    $roleId = $roleMap[$userRole] ?? 'role-secretary';

    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT permission_state FROM role_permissions WHERE role_id = ? AND task_id = ?");
        $stmt->execute([$roleId, $taskId]);
        $row = $stmt->fetch();
        if ($row && $row['permission_state'] === 'allow') {
            return true;
        }
    } catch (Exception $e) {}

    return false;
}

/**
 * Convert Gregorian timestamp to Ethiopian Date (GMT+3 / East Africa Time)
 */
function toEthiopianDate($timestamp = null): array {
    if (!$timestamp) {
        $date = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    } elseif (is_numeric($timestamp)) {
        $date = new DateTime("@$timestamp");
        $date->setTimezone(new DateTimeZone('Africa/Addis_Ababa'));
    } else {
        try {
            $date = new DateTime($timestamp, new DateTimeZone('Africa/Addis_Ababa'));
        } catch (Exception $e) {
            $date = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
        }
    }

    $gYear = (int)$date->format('Y');
    $gMonth = (int)$date->format('n');
    $gDay = (int)$date->format('j');
    $dayOfWeek = (int)$date->format('w');
    $hours = (int)$date->format('G');
    $minutes = (int)$date->format('i');
    $seconds = (int)$date->format('s');

    // JDN Algorithm
    $a = (int)floor((14 - $gMonth) / 12);
    $y = $gYear + 4800 - $a;
    $m = $gMonth + 12 * $a - 3;
    $jdn = $gDay + (int)floor((153 * $m + 2) / 5) + 365 * $y + (int)floor($y / 4) - (int)floor($y / 100) + (int)floor($y / 400) - 32045;

    $ethJdnOffset = 1723856;
    $daysSinceEpoch = $jdn - $ethJdnOffset;
    $ethEra = (int)floor($daysSinceEpoch / 1461);
    $remDaysInEra = $daysSinceEpoch % 1461;
    $ethYearInEra = min((int)floor($remDaysInEra / 365), 3);
    $dayOfYear = $remDaysInEra - $ethYearInEra * 365;

    $ethYear = $ethEra * 4 + $ethYearInEra;
    $ethMonth = (int)floor($dayOfYear / 30) + 1;
    $ethDay = ($dayOfYear % 30) + 1;

    $ethMonths = [
        1 => ['am' => 'መስከረም', 'en' => 'Meskerem'],
        2 => ['am' => 'ጥቅምት', 'en' => 'Tikimt'],
        3 => ['am' => 'ኅዳር', 'en' => 'Hidar'],
        4 => ['am' => 'ታኅሣሥ', 'en' => 'Tahsas'],
        5 => ['am' => 'ጥር', 'en' => 'Tir'],
        6 => ['am' => 'የካቲት', 'en' => 'Yekatit'],
        7 => ['am' => 'መጋቢት', 'en' => 'Megabit'],
        8 => ['am' => 'ሚያዝያ', 'en' => 'Miyazya'],
        9 => ['am' => 'ግንቦት', 'en' => 'Ginbot'],
        10 => ['am' => 'ሰኔ', 'en' => 'Sene'],
        11 => ['am' => 'ሐምሌ', 'en' => 'Hamle'],
        12 => ['am' => 'ነሐሴ', 'en' => 'Nehase'],
        13 => ['am' => 'ጳጉሜ', 'en' => 'Pagume']
    ];

    $weekdays = [
        0 => ['am' => 'እሑድ', 'en' => 'Sunday'],
        1 => ['am' => 'ሰኞ', 'en' => 'Monday'],
        2 => ['am' => 'ማክሰኞ', 'en' => 'Tuesday'],
        3 => ['am' => 'ረቡዕ', 'en' => 'Wednesday'],
        4 => ['am' => 'ሐሙስ', 'en' => 'Thursday'],
        5 => ['am' => 'ዓርብ', 'en' => 'Friday'],
        6 => ['am' => 'ቅዳሜ', 'en' => 'Saturday']
    ];

    $monthData = $ethMonths[$ethMonth] ?? $ethMonths[1];
    $weekdayData = $weekdays[$dayOfWeek] ?? $weekdays[0];

    $pad = fn($n) => str_pad((string)$n, 2, '0', STR_PAD_LEFT);
    $displayHours = $hours % 12 ?: 12;
    $ampm = $hours >= 12 ? 'PM' : 'AM';

    return [
        'year' => $ethYear,
        'month' => $ethMonth,
        'day' => $ethDay,
        'monthNameAm' => $monthData['am'],
        'monthNameEn' => $monthData['en'],
        'weekdayAm' => $weekdayData['am'],
        'weekdayEn' => $weekdayData['en'],
        'formattedAm' => "{$weekdayData['am']}፣ {$monthData['am']} {$ethDay} ቀን {$ethYear} ዓ.ም",
        'formattedEn' => "{$weekdayData['en']}, {$monthData['en']} {$ethDay}, {$ethYear} E.C.",
        'timeAm' => "{$pad($displayHours)}:{$pad($minutes)}:{$pad($seconds)} " . ($ampm === 'AM' ? 'ጠዋት' : 'ከሰዓት'),
        'timeEn' => "{$pad($displayHours)}:{$pad($minutes)}:{$pad($seconds)} {$ampm} (EAT)"
    ];
}

/**
 * Save Base64 image payload into local image directory
 * Returns relative path (e.g. image/permits/abc123.jpg)
 */
function saveBase64Image(string $base64Data, string $subfolder = 'permits'): ?string {
    if (empty($base64Data)) return null;

    if (!str_starts_with($base64Data, 'data:image/')) {
        return $base64Data;
    }

    if (!preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
        return null;
    }

    $ext = strtolower($type[1]);
    if ($ext === 'jpeg') $ext = 'jpg';
    if (!in_array($ext, ['jpg', 'png', 'gif', 'webp'])) {
        $ext = 'jpg';
    }

    $data = substr($base64Data, strpos($base64Data, ',') + 1);
    $decoded = base64_decode($data);
    if ($decoded === false) {
        return null;
    }

    $root = defined('ROOT_PATH') ? ROOT_PATH : (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__));
    $targetDir = $root . '/image/' . trim($subfolder, '/') . '/';
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0775, true);
    }

    $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
    $filePath = $targetDir . $fileName;

    if (file_put_contents($filePath, $decoded) !== false) {
        return 'image/' . trim($subfolder, '/') . '/' . $fileName;
    }

    return null;
}
