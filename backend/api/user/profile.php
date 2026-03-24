<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

$method = $_SERVER['REQUEST_METHOD'];
$authUser = JwtHelper::requireAuth();
$userId = (int) ($authUser['sub'] ?? 0);

if (($authUser['role'] ?? '') === 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Tài khoản admin không dùng màn hình này.']);
    exit;
}

try {
    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT id, full_name, email, phone, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy thông tin người dùng.']);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'id' => (int) $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'phone' => $user['phone'] ?? '',
                'role' => $user['role'],
            ],
        ]);
        exit;
    }

    if ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);

        $fullName = trim((string) ($data['full_name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $newPassword = (string) ($data['password'] ?? '');

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

        if ($phone !== '') {
            $phoneCheck = Validator::checkNumberFormat($phone, '/^[0-9+\s]{9,15}$/');
            if ($phoneCheck !== true) {
                echo json_encode(['status' => 'error', 'message' => 'Số điện thoại không hợp lệ.']);
                exit;
            }
        }

        if ($newPassword !== '') {
            $passwordCheck = Validator::checkStringLength($newPassword, 6, 50);
            if ($passwordCheck !== true) {
                echo json_encode(['status' => 'error', 'message' => 'Mật khẩu mới: ' . $passwordCheck]);
                exit;
            }
        }

        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $stmtCheck->execute([$email, $userId]);
        if ($stmtCheck->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Email này đã được sử dụng bởi tài khoản khác.']);
            exit;
        }

        if ($newPassword !== '') {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
            $stmt->execute([$fullName, $email, $phone !== '' ? $phone : null, $passwordHash, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->execute([$fullName, $email, $phone !== '' ? $phone : null, $userId]);
        }

        $stmtUser = $pdo->prepare("SELECT id, full_name, email, phone, role FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $updatedUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'message' => 'Cập nhật thông tin thành công.',
            'data' => [
                'id' => (int) $updatedUser['id'],
                'full_name' => $updatedUser['full_name'],
                'email' => $updatedUser['email'],
                'phone' => $updatedUser['phone'] ?? '',
                'role' => $updatedUser['role'],
            ],
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi Database: ' . $e->getMessage(),
    ]);
}
