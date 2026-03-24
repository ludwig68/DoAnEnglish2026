<?php
// backend/api/admin/users.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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
$data = json_decode(file_get_contents("php://input"), true);
$authUser = JwtHelper::requireAuth('admin');

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("SELECT id, full_name, email, role, status, DATE_FORMAT(created_at, '%d/%m/%Y') as created_at FROM users ORDER BY id DESC");
            $users = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $users]);
            break;

        case 'POST':
            $full_name = $data['full_name'] ?? '';
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $role = $data['role'] ?? 'student';
            $status = $data['status'] ?? 'active';

            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmtCheck->execute([$email]);
            if ($stmtCheck->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Email đã tồn tại!']);
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $hash, $role, $status]);

            echo json_encode(['status' => 'success', 'message' => 'Tạo tài khoản thành công!']);
            break;

        case 'PUT':
            $id = $data['id'] ?? 0;
            $full_name = $data['full_name'] ?? '';
            $role = $data['role'] ?? 'student';
            $status = $data['status'] ?? 'active';

            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, password = ?, role = ?, status = ? WHERE id = ?");
                $stmt->execute([$full_name, $hash, $role, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, role = ?, status = ? WHERE id = ?");
                $stmt->execute([$full_name, $role, $status, $id]);
            }

            echo json_encode(['status' => 'success', 'message' => 'Cập nhật thông tin thành công!']);
            break;

        case 'DELETE':
            $id = (int) ($_GET['id'] ?? 0);

            if ($id === (int) ($authUser['sub'] ?? 0)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Không thể tự xóa tài khoản đang đăng nhập.']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['status' => 'success', 'message' => 'Đã xóa tài khoản!']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Lỗi Database: ' . $e->getMessage()]);
}
?>
