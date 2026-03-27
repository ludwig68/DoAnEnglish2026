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
$phone = trim((string) ($data['phone'] ?? ''));
$email = trim((string) ($data['email'] ?? ''));
$note = trim((string) ($data['note'] ?? ''));

$nameCheck = Validator::checkStringLength($fullName, 2, 100);
if ($nameCheck !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Họ và tên: ' . $nameCheck]);
    exit;
}

$phoneCheck = Validator::checkNumberFormat($phone, '/^[0-9+\s]{9,15}$/');
if ($phoneCheck !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Số điện thoại không hợp lệ.']);
    exit;
}

if ($email !== '') {
    $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    $emailCheck = Validator::checkRegex($email, $emailPattern, 'Định dạng email không hợp lệ.');
    if ($emailCheck !== true) {
        echo json_encode(['status' => 'error', 'message' => $emailCheck]);
        exit;
    }
}

if ($note !== '' && mb_strlen($note, 'UTF-8') > 1000) {
    echo json_encode(['status' => 'error', 'message' => 'Nội dung nhu cầu không được vượt quá 1000 ký tự.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO consultations (full_name, phone, email, note) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $fullName,
        $phone,
        $email !== '' ? $email : null,
        $note !== '' ? $note : null,
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Đăng ký tư vấn thành công. Trung tâm sẽ liên hệ với bạn sớm nhất có thể.',
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi Database: ' . $e->getMessage(),
    ]);
}
