<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../utils/JwtHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

JwtHelper::requireAuth('admin');

if (empty($_FILES['image'])) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Vui long chon anh de tai len.']);
    exit;
}

$file = $_FILES['image'];

if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Khong the tai anh len.']);
    exit;
}

$maxSize = 5 * 1024 * 1024;
if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxSize) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Anh tai len toi da 5MB.']);
    exit;
}

$originalName = (string) ($file['name'] ?? '');
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (!in_array($extension, $allowedExtensions, true)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Chi chap nhan anh JPG, JPEG, PNG, WEBP hoac GIF.']);
    exit;
}

$tmpName = $file['tmp_name'] ?? '';
$mimeType = null;

if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo) {
        $mimeType = finfo_file($finfo, $tmpName);
        finfo_close($finfo);
    }
}

$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
if ($mimeType !== null && !in_array($mimeType, $allowedMimeTypes, true)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Tep tai len khong phai la anh hop le.']);
    exit;
}

$uploadDir = dirname(__DIR__, 2) . '/uploads/course-images';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Khong tao duoc thu muc luu anh.']);
    exit;
}

$fileName = 'course_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
$targetPath = $uploadDir . '/' . $fileName;

if (!move_uploaded_file($tmpName, $targetPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Khong luu duoc anh tai len.']);
    exit;
}

// Lưu đường dẫn tương đối — hoạt động trên cả localhost lẫn production
$relativePath = '/backend/uploads/course-images/' . $fileName;

// Build full URL để trả về cho frontend hiển thị ngay
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$projectBasePath = preg_replace('#/backend/api/admin/upload_course_image\.php$#', '', $scriptName);
$publicUrl = $scheme . '://' . $host . $projectBasePath . $relativePath;

echo json_encode([
    'status' => 'success',
    'message' => 'Tai anh thanh cong.',
    'data' => [
        'url' => $publicUrl,
        'relative_path' => $relativePath,
        'file_name' => $fileName,
    ],
]);
