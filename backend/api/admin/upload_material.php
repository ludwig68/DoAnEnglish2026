<?php
// ═══════════════════════════════════════════════════════════════
// API: Upload tài liệu giảng dạy (Admin only)
// ═══════════════════════════════════════════════════════════════
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

$authUser = JwtHelper::requireAuth();
if (($authUser['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Chỉ Admin mới có quyền tải tài liệu lên.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// Validate file
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Không có file hoặc file bị lỗi.']);
    exit;
}

$file = $_FILES['file'];
$maxSize = 50 * 1024 * 1024; // 50MB

if ($file['size'] > $maxSize) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'File quá lớn (tối đa 50MB).']);
    exit;
}

// Allowed types
$allowedMimes = [
    'application/pdf',
    'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/x-m4a', 'audio/mp4',
    'video/mp4', 'video/webm', 'video/ogg',
    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg', 'image/png', 'image/webp', 'image/gif',
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimes)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Loại file không được hỗ trợ: ' . $mimeType]);
    exit;
}

// Determine file type category
$fileType = 'other';
if (str_contains($mimeType, 'pdf')) $fileType = 'pdf';
elseif (str_starts_with($mimeType, 'audio/')) $fileType = 'audio';
elseif (str_starts_with($mimeType, 'video/')) $fileType = 'video';
elseif (str_starts_with($mimeType, 'image/')) $fileType = 'image';
elseif (str_contains($mimeType, 'word') || str_contains($mimeType, 'powerpoint') || str_contains($mimeType, 'presentation') || str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) $fileType = 'document';

// Create upload directory
$uploadDir = dirname(__DIR__, 2) . '/uploads/materials';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = uniqid('mat_') . '_' . time() . '.' . $ext;
$targetPath = $uploadDir . '/' . $fileName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Không thể lưu file lên server.']);
    exit;
}

// Build relative URL
$relativePath = '/backend/uploads/materials/' . $fileName;

echo json_encode([
    'status' => 'success',
    'message' => 'Tải file thành công.',
    'data' => [
        'file_url' => $relativePath,
        'file_type' => $fileType,
        'file_size' => $file['size'],
        'original_name' => $file['name'],
    ]
]);
