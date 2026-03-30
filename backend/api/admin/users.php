<?php

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
$payload = json_decode(file_get_contents("php://input"), true) ?? [];
$authUser = JwtHelper::requireAuth('admin');

function usersResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function validateUserPayload(PDO $pdo, array $data, bool $isCreate, int $excludeId = 0): array
{
    $fullName = trim((string) ($data['full_name'] ?? ''));
    $email = trim((string) ($data['email'] ?? ''));
    $password = (string) ($data['password'] ?? '');
    $role = trim((string) ($data['role'] ?? 'student'));
    $status = trim((string) ($data['status'] ?? 'active'));

    $nameCheck = Validator::checkStringLength($fullName, 2, 100);
    if ($nameCheck !== true) {
        return ['error' => 'Ho ten phai tu 2 den 100 ky tu.'];
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return ['error' => 'Email khong hop le.'];
    }

    $allowedRoles = ['student', 'instructor', 'admin'];
    if (!in_array($role, $allowedRoles, true)) {
        return ['error' => 'Vai tro khong hop le.'];
    }

    $allowedStatuses = ['active', 'blocked'];
    if (!in_array($status, $allowedStatuses, true)) {
        return ['error' => 'Trang thai khong hop le.'];
    }

    if ($isCreate || $password !== '') {
        $passwordCheck = Validator::checkStringLength($password, 6, 255);
        if ($passwordCheck !== true) {
            return ['error' => 'Mat khau phai tu 6 den 255 ky tu.'];
        }
    }

    $duplicateStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE email = ?
          AND id <> ?
    ");
    $duplicateStmt->execute([$email, $excludeId]);
    if ((int) $duplicateStmt->fetchColumn() > 0) {
        return ['error' => 'Email da ton tai.'];
    }

    return [
        'data' => [
            'full_name' => $fullName,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'status' => $status,
        ],
    ];
}

function countActiveAdmins(PDO $pdo, ?int $excludeId = null): int
{
    if ($excludeId === null) {
        $stmt = $pdo->query("
            SELECT COUNT(*)
            FROM users
            WHERE role = 'admin' AND status = 'active'
        ");

        return (int) $stmt->fetchColumn();
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE role = 'admin'
          AND status = 'active'
          AND id <> ?
    ");
    $stmt->execute([$excludeId]);

    return (int) $stmt->fetchColumn();
}

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("
                SELECT id, full_name, email, role, status, DATE_FORMAT(created_at, '%d/%m/%Y') AS created_at
                FROM users
                ORDER BY id DESC
            ");
            usersResponse(200, ['status' => 'success', 'data' => $stmt->fetchAll()]);

        case 'POST':
            $validated = validateUserPayload($pdo, $payload, true);
            if (isset($validated['error'])) {
                usersResponse(422, ['status' => 'error', 'message' => $validated['error']]);
            }

            $data = $validated['data'];
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password, role, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['full_name'],
                $data['email'],
                $hash,
                $data['role'],
                $data['status'],
            ]);

            usersResponse(200, ['status' => 'success', 'message' => 'Tao tai khoan thanh cong.']);

        case 'PUT':
            $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);
            if ($id <= 0) {
                usersResponse(422, ['status' => 'error', 'message' => 'ID nguoi dung khong hop le.']);
            }

            $userStmt = $pdo->prepare("
                SELECT id, role, status
                FROM users
                WHERE id = ?
                LIMIT 1
            ");
            $userStmt->execute([$id]);
            $existingUser = $userStmt->fetch();
            if (!$existingUser) {
                usersResponse(404, ['status' => 'error', 'message' => 'Nguoi dung khong ton tai.']);
            }

            $validated = validateUserPayload($pdo, $payload, false, $id);
            if (isset($validated['error'])) {
                usersResponse(422, ['status' => 'error', 'message' => $validated['error']]);
            }

            $data = $validated['data'];
            $isSelf = $id === (int) ($authUser['sub'] ?? 0);

            if (
                $existingUser['role'] === 'admin'
                && $existingUser['status'] === 'active'
                && ($data['role'] !== 'admin' || $data['status'] !== 'active')
                && countActiveAdmins($pdo, $id) === 0
            ) {
                usersResponse(422, ['status' => 'error', 'message' => 'He thong phai con it nhat 1 admin dang hoat dong.']);
            }

            if ($isSelf && ($data['role'] !== 'admin' || $data['status'] !== 'active')) {
                usersResponse(422, ['status' => 'error', 'message' => 'Khong the tu ha quyen hoac tu khoa tai khoan dang dang nhap.']);
            }

            if ($data['password'] !== '') {
                $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users
                    SET full_name = ?, email = ?, password = ?, role = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $data['full_name'],
                    $data['email'],
                    $hash,
                    $data['role'],
                    $data['status'],
                    $id,
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users
                    SET full_name = ?, email = ?, role = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $data['full_name'],
                    $data['email'],
                    $data['role'],
                    $data['status'],
                    $id,
                ]);
            }

            usersResponse(200, ['status' => 'success', 'message' => 'Cap nhat thong tin thanh cong.']);

        case 'DELETE':
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
            if ($id <= 0) {
                usersResponse(422, ['status' => 'error', 'message' => 'ID nguoi dung khong hop le.']);
            }

            if ($id === (int) ($authUser['sub'] ?? 0)) {
                usersResponse(422, ['status' => 'error', 'message' => 'Khong the tu xoa tai khoan dang dang nhap.']);
            }

            $userStmt = $pdo->prepare("
                SELECT id, role, status
                FROM users
                WHERE id = ?
                LIMIT 1
            ");
            $userStmt->execute([$id]);
            $existingUser = $userStmt->fetch();
            if (!$existingUser) {
                usersResponse(404, ['status' => 'error', 'message' => 'Nguoi dung khong ton tai.']);
            }

            if (
                $existingUser['role'] === 'admin'
                && $existingUser['status'] === 'active'
                && countActiveAdmins($pdo, $id) === 0
            ) {
                usersResponse(422, ['status' => 'error', 'message' => 'Khong the xoa admin dang hoat dong cuoi cung.']);
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            usersResponse(200, ['status' => 'success', 'message' => 'Da xoa tai khoan.']);

        default:
            usersResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
    }
} catch (PDOException $exception) {
    usersResponse(500, ['status' => 'error', 'message' => 'Loi Database: ' . $exception->getMessage()]);
}
