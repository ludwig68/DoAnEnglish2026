<?php
// backend/api/auth/register.php

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $full_name        = $data['full_name'] ?? '';
    $email            = $data['email'] ?? '';
    $password         = $data['password'] ?? '';
    $password_confirm = $data['password_confirm'] ?? '';

    // =========================================================
    // 1. VALIDATION CƠ BẢN (DÙNG CLASS VALIDATOR)
    // =========================================================
    
    // Kiểm tra Họ tên: Dài từ 2 đến 50 ký tự
    $nameCheck = Validator::checkStringLength($full_name, 2, 50);
    if ($nameCheck !== true) {
        echo json_encode(['status' => 'error', 'message' => "Họ tên: " . $nameCheck]);
        exit;
    }

    // Kiểm tra Email Regex
    $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    $emailCheck = Validator::checkRegex($email, $emailPattern, "Định dạng email không hợp lệ.");
    if ($emailCheck !== true) {
        echo json_encode(['status' => 'error', 'message' => $emailCheck]);
        exit;
    }

    // Kiểm tra Mật khẩu: Từ 6 đến 20 ký tự
    $passCheck = Validator::checkStringLength($password, 6, 20);
    if ($passCheck !== true) {
        echo json_encode(['status' => 'error', 'message' => "Mật khẩu: " . $passCheck]);
        exit;
    }

    // Kiểm tra 2 mật khẩu khớp nhau
    if ($password !== $password_confirm) {
        echo json_encode(['status' => 'error', 'message' => 'Mật khẩu xác nhận không khớp.']);
        exit;
    }

    try {
        // =========================================================
        // 2. KIỂM TRA EMAIL ĐÃ TỒN TẠI CHƯA
        // =========================================================
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmtCheck->execute([$email]);
        
        if ($stmtCheck->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Email này đã được đăng ký.']);
            exit;
        }

        // =========================================================
        // 3. MÃ HÓA VÀ THÊM VÀO DATABASE
        // =========================================================
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Mặc định tài khoản mới tạo sẽ có role là 'student' (hoặc 'user' tùy bạn thiết kế DB)
        $stmtInsert = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'student')");
        $stmtInsert->execute([$full_name, $email, $hash]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Đăng ký tài khoản thành công!'
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Lỗi Database: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}
?>