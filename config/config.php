<?php
/**
 * Application Configuration
 * Bahir Dar Motorcycle Permit & Enforcement System
 * Optimized for PHP 8.3 & InfinityFree Shared Hosting
 */

// Error reporting (Suppress notices and deprecations in production)
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
@ini_set('display_errors', '0');

// Start secure session safely if not already started
if (session_status() === PHP_SESSION_NONE) {
    @ini_set('session.gc_maxlifetime', '604800');
    if (!headers_sent()) {
        @session_set_cookie_params([
            'lifetime' => 604800,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
        ]);
        @session_start();
    } else {
        @session_start();
    }
}

// Database Credentials
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'permit_db');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// Application Constants
if (!defined('APP_NAME_AM')) define('APP_NAME_AM', 'ባህርዳር ሞተረኞች ማህበር');
if (!defined('APP_NAME_EN')) define('APP_NAME_EN', 'Bahir Dar Motorcyclists Association');
if (!defined('APP_TITLE')) define('APP_TITLE', 'Enforcement Pro - Command Central');
if (!defined('APP_VERSION')) define('APP_VERSION', '2.5.0-PHP');

// Base Paths
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', ROOT_PATH . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR);

// Sub-cities of Bahir Dar
if (!defined('BAHIR_DAR_SUBCITIES')) {
    define('BAHIR_DAR_SUBCITIES', [
        ['en' => 'Fasilo', 'am' => 'ፋሲሎ'],
        ['en' => 'Dagmawi Minilik', 'am' => 'ዳግማዊ ሚኒሊክ'],
        ['en' => 'Belay Zeleke', 'am' => 'በላይ ዘለቀ'],
        ['en' => 'Atse Tewodros', 'am' => 'አጼ ቴወድሮስ'],
        ['en' => 'Gish Abay', 'am' => 'ግሽ አባይ'],
        ['en' => 'Tana', 'am' => 'ጣና']
    ]);
}

// 16 RBAC Task Definitions
if (!defined('SYSTEM_TASKS')) {
    define('SYSTEM_TASKS', [
        1 => ['icon' => 'person_add', 'am' => '1. አዲስ አባል / ተሽከርካሪ መመዝገብ', 'en' => 'Register New Member / Vehicle'],
        2 => ['icon' => 'edit', 'am' => '2. የአባል መመዝገቢያ ማስተካከል', 'en' => 'Edit Member Information'],
        3 => ['icon' => 'two_wheeler', 'am' => '3. የተሽከርካሪ መረጃ ማስተካከል', 'en' => 'Edit Vehicle Info'],
        4 => ['icon' => 'attach_file', 'am' => '4. ሰነዶች መስቀል', 'en' => 'Upload Documents'],
        5 => ['icon' => 'barcode_scanner', 'am' => '5. ባርኮድ ስካን', 'en' => 'Barcode / QR Scan'],
        6 => ['icon' => 'visibility', 'am' => '6. የአባል መረጃ ማየት', 'en' => 'View Member Info'],
        7 => ['icon' => 'list_alt', 'am' => '7. የተመዘገቡ አባላትን ዝርዝር ማየት', 'en' => 'View Registered Members List'],
        8 => ['icon' => 'badge', 'am' => '8. የይለፍ ፈቃድ ባጅ ማተም', 'en' => 'Print Permit Badge'],
        9 => ['icon' => 'analytics', 'am' => '9. የፍተሻ ሪፖርት ማየት', 'en' => 'View Inspection Reports'],
        10 => ['icon' => 'report_problem', 'am' => '10. ህገወጥ ተሽከርካሪ ሪፖርት ማድረግ', 'en' => 'Report Unregistered Vehicles'],
        11 => ['icon' => 'no_drinks', 'am' => '11. የህገወጥ ሞተሮች ማህደር ማየት', 'en' => 'View Unregistered Registry'],
        12 => ['icon' => 'receipt_long', 'am' => '12. የክፍያ ደረሰኞች ማስተዳደር', 'en' => 'Manage Payment Receipts'],
        13 => ['icon' => 'manage_accounts', 'am' => '13. ሚና እና ፈቃድ ማስተዳደር', 'en' => 'Manage Roles & Permissions'],
        14 => ['icon' => 'location_city', 'am' => '14. የክፍለ ከተማ ቁጥጥር', 'en' => 'Sub-City Governance'],
        15 => ['icon' => 'shield', 'am' => '15. የሴኪዩሪቲ ኦዲት መመልከት', 'en' => 'Security Audit Logs'],
        16 => ['icon' => 'database', 'am' => '16. የሲስተም ጥገና እና ዳግም ማስጀመር', 'en' => 'System Maintenance & Reset']
    ]);
}
