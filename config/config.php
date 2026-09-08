<?php
/**
 * Application Configuration
 * Bahir Dar Motorcycle Permit & Enforcement System
 */

// Error reporting (set to 0 in production)
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '0');

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // 7-day session lifetime
    ini_set('session.gc_maxlifetime', 604800);
    session_set_cookie_params([
        'lifetime' => 604800,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
    ]);
    session_start();
}

// Database Credentials (Default for XAMPP/WAMP/Laragon)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'permit_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Application Constants
define('APP_NAME_AM', 'ባህርዳር ሞተረኞች ማህበር');
define('APP_NAME_EN', 'Bahir Dar Motorcyclists Association');
define('APP_TITLE', 'Enforcement Pro - Command Central');
define('APP_VERSION', '2.5.0-PHP');

// Base Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR);

// Sub-cities of Bahir Dar
const BAHIR_DAR_SUBCITIES = [
    ['en' => 'Fasilo', 'am' => 'ፋሲሎ'],
    ['en' => 'Dagmawi Minilik', 'am' => 'ዳግማዊ ሚኒሊክ'],
    ['en' => 'Belay Zeleke', 'am' => 'በላይ ዘለቀ'],
    ['en' => 'Atse Tewodros', 'am' => 'አጼ ቴወድሮስ'],
    ['en' => 'Gish Abay', 'am' => 'ግሽ አባይ'],
    ['en' => 'Tana', 'am' => 'ጣና']
];

// 16 RBAC Task Definitions
const SYSTEM_TASKS = [
    1 => ['icon' => 'person_add', 'am' => '1. አዲስ አባል / ተሽከርካሪ መመዝገብ', 'en' => 'Register New Member / Vehicle'],
    2 => ['icon' => 'edit', 'am' => '2. የአባል መመዝገቢያ ማስተካከል', 'en' => 'Edit Member Information'],
    3 => ['icon' => 'two_wheeler', 'am' => '3. የተሽከርካሪ መረጃ ማስተካከል', 'en' => 'Edit Vehicle Info'],
    4 => ['icon' => 'attach_file', 'am' => '4. ሰነዶች መስቀል', 'en' => 'Upload Documents'],
    5 => ['icon' => 'barcode_scanner', 'am' => '5. ባርኮድ ስካን', 'en' => 'Barcode / QR Scan'],
    6 => ['icon' => 'visibility', 'am' => '6. የአባል መረጃ ማየት', 'en' => 'View Member Info'],
    7 => ['icon' => 'two_wheeler', 'am' => '7. የተሽከርካሪ መረጃ ማየት', 'en' => 'View Vehicle Info'],
    8 => ['icon' => 'list_alt', 'am' => '8. የአባላት ዝርዝር ማየት', 'en' => 'View Members List'],
    9 => ['icon' => 'bar_chart', 'am' => '9. የተሽከርካሪ ሁኔታን ማየት', 'en' => 'View Count/Statistics'],
    10 => ['icon' => 'description', 'am' => '10. የፍተሻ ሪፖርቶችና ታሪክ', 'en' => 'Verification Log Visibility & Reports'],
    11 => ['icon' => 'delete', 'am' => '11. አባል መሰረዝ', 'en' => 'Delete Member'],
    12 => ['icon' => 'database', 'am' => '12. Database ማስተዳደር', 'en' => 'DB Management'],
    13 => ['icon' => 'group_add', 'am' => '13. ተጠቃሚ መፍጠር / ማዋቀር', 'en' => 'User Management'],
    14 => ['icon' => 'security', 'am' => '14. ፈቃዶች ማስተዳደር', 'en' => 'Permission Management'],
    15 => ['icon' => 'analytics', 'am' => '15. የክፍያ ደረሰኞች ስታቲስቲክስ', 'en' => 'Payment Receipt KPIs'],
    16 => ['icon' => 'table_view', 'am' => '16. የክፍያ ደረሰኞች ማህደር ሰንጠረዥ', 'en' => 'Payment Receipt Records Table']
];

// System Default Demo Credentials
const DEFAULT_SYSTEM_CREDENTIALS = [
    'clerk' => [
        'badgeId' => 'CLERK-209',
        'role' => 'clerk',
        'email' => 'clerk@addisababa.gov.et',
        'fullName' => 'ሳራ ተሾመ (Sara Teshome)'
    ],
    'officer' => [
        'badgeId' => 'OFFICER-442',
        'role' => 'officer',
        'email' => 'officer@addisababa.gov.et',
        'fullName' => 'አበበ ደስታ (Abebe Desta)'
    ],
    'admin' => [
        'badgeId' => 'ADMIN-001',
        'role' => 'admin',
        'email' => 'admin@addisababa.gov.et',
        'fullName' => 'ዳዊት ኃይሌ (Dawit Haile)'
    ],
    'superadmin' => [
        'badgeId' => 'SUPER-ADMIN-01',
        'role' => 'superadmin',
        'email' => 'superadmin@permit.gov.et',
        'fullName' => 'ካሌብ ታደሰ (Kaleb Tadesse - Chief Super Admin)'
    ]
];
