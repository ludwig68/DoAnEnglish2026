<?php
// backend/api/auth/login.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    $emailCheck = Validator::checkRegex($email, $emailPattern, "Định dạng email không hợp lệ.");
    $passCheck = Validator::checkStringLength($password, 6, 20);

    if ($emailCheck !== true) {
        echo json_encode(['status' => 'error', 'message' => $emailCheck]);
        exit;
    }

    if ($passCheck !== true) {
        echo json_encode(['status' => 'error', 'message' => "Mật khẩu: " . $passCheck]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if (($user['status'] ?? 'active') !== 'active') {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Tài khoản của bạn hiện đang bị khóa.']);
                exit;
            }

            $userPayload = [
                'id' => (int) $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'status' => $user['status'],
            ];

            $token = JwtHelper::generate([
                'sub' => (int) $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $user['full_name'],
            ]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Đăng nhập thành công!',
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 60 * 60 * 24,
                'user' => $userPayload
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Email hoặc mật khẩu không chính xác.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}
?>
