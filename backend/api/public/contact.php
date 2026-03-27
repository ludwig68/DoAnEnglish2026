<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$fullName = trim((string) ($data['full_name'] ?? ''));
$email = trim((string) ($data['email'] ?? ''));
$message = trim((string) ($data['message'] ?? ''));

$nameCheck = Validator::checkStringLength($fullName, 2, 100);
if ($nameCheck !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Họ và tên: ' . $nameCheck]);
    exit;
}

$emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
$emailCheck = Validator::checkRegex($email, $emailPattern, 'Định dạng email không hợp lệ.');
if ($emailCheck !== true) {
    echo json_encode(['status' => 'error', 'message' => $emailCheck]);
    exit;
}

$messageCheck = Validator::checkStringLength($message, 10, 2000);
if ($messageCheck !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Nội dung phản hồi: ' . $messageCheck]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO contacts (full_name, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$fullName, $email, $message]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Cảm ơn bạn đã liên hệ. Trung tâm sẽ phản hồi trong thời gian sớm nhất.',
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi Database: ' . $e->getMessage(),
    ]);
}
