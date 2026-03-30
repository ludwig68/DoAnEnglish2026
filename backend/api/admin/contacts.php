<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

JwtHelper::requireAuth('admin');

$method = $_SERVER['REQUEST_METHOD'];
$payload = json_decode(file_get_contents("php://input"), true) ?? [];

function contactsResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function validateContactPayload(array $data): array
{
    $fullName = trim((string) ($data['full_name'] ?? ''));
    $email = trim((string) ($data['email'] ?? ''));
    $message = trim((string) ($data['message'] ?? ''));
    $isRepliedRaw = $data['is_replied'] ?? 0;

    $nameCheck = Validator::checkStringLength($fullName, 2, 100);
    if ($nameCheck !== true) {
        return ['error' => 'Ho ten phai tu 2 den 100 ky tu.'];
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return ['error' => 'Email khong hop le.'];
    }

    $messageCheck = Validator::checkStringLength($message, 5, 5000);
    if ($messageCheck !== true) {
        return ['error' => 'Noi dung lien he phai tu 5 den 5000 ky tu.'];
    }

    $isReplied = filter_var($isRepliedRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($isReplied === null) {
        $normalized = (string) $isRepliedRaw;
        if (!in_array($normalized, ['0', '1', ''], true)) {
            return ['error' => 'Trang thai phan hoi khong hop le.'];
        }
        $isReplied = $normalized === '1';
    }

    return [
        'data' => [
            'full_name' => $fullName,
            'email' => $email,
            'message' => $message,
            'is_replied' => $isReplied ? 1 : 0,
        ],
    ];
}

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("
                SELECT *
                FROM contacts
                ORDER BY created_at DESC, id DESC
            ");
            contactsResponse(200, ['status' => 'success', 'data' => $stmt->fetchAll()]);

        case 'POST':
            $validated = validateContactPayload($payload);
            if (isset($validated['error'])) {
                contactsResponse(422, ['status' => 'error', 'message' => $validated['error']]);
            }

            $data = $validated['data'];
            $stmt = $pdo->prepare("
                INSERT INTO contacts (full_name, email, message, is_replied)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['full_name'],
                $data['email'],
                $data['message'],
                $data['is_replied'],
            ]);

            contactsResponse(200, ['status' => 'success', 'message' => 'Them lien he thanh cong.']);

        case 'PUT':
            $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);
            if ($id <= 0) {
                contactsResponse(422, ['status' => 'error', 'message' => 'ID lien he khong hop le.']);
            }

            $validated = validateContactPayload($payload);
            if (isset($validated['error'])) {
                contactsResponse(422, ['status' => 'error', 'message' => $validated['error']]);
            }

            $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE id = ?");
            $existsStmt->execute([$id]);
            if ((int) $existsStmt->fetchColumn() === 0) {
                contactsResponse(404, ['status' => 'error', 'message' => 'Lien he khong ton tai.']);
            }

            $data = $validated['data'];
            $stmt = $pdo->prepare("
                UPDATE contacts
                SET full_name = ?, email = ?, message = ?, is_replied = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['full_name'],
                $data['email'],
                $data['message'],
                $data['is_replied'],
                $id,
            ]);

            contactsResponse(200, ['status' => 'success', 'message' => 'Cap nhat lien he thanh cong.']);

        case 'DELETE':
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
            if ($id <= 0) {
                contactsResponse(422, ['status' => 'error', 'message' => 'ID lien he khong hop le.']);
            }

            $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE id = ?");
            $existsStmt->execute([$id]);
            if ((int) $existsStmt->fetchColumn() === 0) {
                contactsResponse(404, ['status' => 'error', 'message' => 'Lien he khong ton tai.']);
            }

            $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
            $stmt->execute([$id]);

            contactsResponse(200, ['status' => 'success', 'message' => 'Xoa lien he thanh cong.']);

        default:
            contactsResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
    }
} catch (PDOException $exception) {
    contactsResponse(500, ['status' => 'error', 'message' => 'Loi Database: ' . $exception->getMessage()]);
}
