<?php
// backend/api/auth/login.php

// 1. Cấu hình Header để Vue.js có thể gọi API (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Xử lý request OPTIONS (Preflight) của trình duyệt
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';

// Chỉ chấp nhận phương thức POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Lấy dữ liệu JSON từ phía Frontend Vue.js gửi lên
    $data = json_decode(file_get_contents("php://input"), true);
    
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    // =========================================================
    // BƯỚC 1: VALIDATION (BẮT BUỘC THEO TÀI LIỆU)
    // =========================================================

    // 1.1 Kiểm tra Email bằng Regex (Yêu cầu: Format Check - Regex) 
    $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    $emailCheck = Validator::checkRegex($email, $emailPattern, "Định dạng email không hợp lệ.");

    // 1.2 Kiểm tra Mật khẩu (Yêu cầu: Min/Max Length Check) 
    // Giả sử yêu cầu mật khẩu từ 6 đến 20 ký tự
    $passCheck = Validator::checkStringLength($password, 6, 20);

    // Nếu có lỗi Validation, trả về thông báo cho Frontend ngay lập tức
    if ($emailCheck !== true) {
        echo json_encode(['status' => 'error', 'message' => $emailCheck]);
        exit;
    }
    if ($passCheck !== true) {
        echo json_encode(['status' => 'error', 'message' => "Mật khẩu: " . $passCheck]);
        exit;
    }

    // =========================================================
    // BƯỚC 2: KIỂM TRA TÀI KHOẢN TRONG DATABASE
    // =========================================================
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Kiểm tra người dùng tồn tại và khớp mật khẩu (đã mã hóa)
        if ($user && password_verify($password, $user['password'])) {
            
            // Đăng nhập thành công, trả về thông tin cơ bản (không trả về password)
            echo json_encode([
                'status' => 'success',
                'message' => 'Đăng nhập thành công!',
                'user' => [
                    'id' => $user['id'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            // Sai thông tin
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Email hoặc mật khẩu không chính xác.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }

} else {
    // Phương thức không được phép
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}
?>