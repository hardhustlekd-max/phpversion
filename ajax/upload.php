<?php
/**
 * AJAX File Upload Handler
 * Securely uploads images to the appropriate image/ subfolder
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireLogin(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (empty($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Upload error code: ' . $file['error']]);
    exit;
}

// Security: Check mime type and extension safely across various hosting setups
$allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/jpg' => 'jpg'];
$mimeType = '';

if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo) {
        $mimeType = finfo_file($finfo, $file['tmp_name']) ?: '';
        finfo_close($finfo);
    }
} elseif (function_exists('mime_content_type')) {
    $mimeType = mime_content_type($file['tmp_name']) ?: '';
} elseif (function_exists('getimagesize')) {
    $imgInfo = @getimagesize($file['tmp_name']);
    if ($imgInfo && !empty($imgInfo['mime'])) {
        $mimeType = $imgInfo['mime'];
    }
}

// Fallback to client-provided type / extension if server modules are restricted
if (empty($mimeType)) {
    $clientExt = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (in_array($clientExt, ['jpg', 'jpeg'])) $mimeType = 'image/jpeg';
    elseif ($clientExt === 'png') $mimeType = 'image/png';
    elseif ($clientExt === 'webp') $mimeType = 'image/webp';
}

if (!isset($allowedTypes[$mimeType])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Only JPG, PNG, and WebP images are permitted']);
    exit;
}

// Destination category
$category = $_POST['category'] ?? 'portraits';
$validCategories = ['portraits', 'national_ids', 'licenses', 'permits', 'receipts', 'evidence', 'app'];
if (!in_array($category, $validCategories, true)) {
    $category = 'portraits';
}

$targetDir = UPLOAD_DIR . $category . DIRECTORY_SEPARATOR;
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$ext = $allowedTypes[$mimeType];
$filename = $category . '_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
$targetFile = $targetDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetFile)) {
    $webUrl = 'image/' . $category . '/' . $filename;
    echo json_encode([
        'success' => true,
        'url' => $webUrl,
        'filename' => $filename
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
}
