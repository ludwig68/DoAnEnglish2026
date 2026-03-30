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

function categoriesResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function validateCategoryPayload(array $data): array
{
    $name = trim((string) ($data['name'] ?? ''));
    $description = trim((string) ($data['description'] ?? ''));

    $nameCheck = Validator::checkStringLength($name, 3, 150);
    if ($nameCheck !== true) {
        return ['error' => 'Ten danh muc phai tu 3 den 150 ky tu.'];
    }

    if ($description !== '') {
        $descriptionCheck = Validator::checkStringLength($description, 3, 2000);
        if ($descriptionCheck !== true) {
            return ['error' => 'Mo ta danh muc phai tu 3 den 2000 ky tu neu co nhap.'];
        }
    }

    return [
        'data' => [
            'name' => $name,
            'description' => $description !== '' ? $description : null,
        ],
    ];
}

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("
                SELECT id, name, description, DATE_FORMAT(created_at, '%d/%m/%Y') AS created_at
                FROM categories
                ORDER BY id DESC
            ");
            categoriesResponse(200, ['status' => 'success', 'data' => $stmt->fetchAll()]);

        case 'POST':
            $validated = validateCategoryPayload($payload);
            if (isset($validated['error'])) {
                categoriesResponse(422, ['status' => 'error', 'message' => $validated['error']]);
            }

            $data = $validated['data'];
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$data['name'], $data['description']]);

            categoriesResponse(200, ['status' => 'success', 'message' => 'Them danh muc thanh cong.']);

        case 'PUT':
            $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);
            if ($id <= 0) {
                categoriesResponse(422, ['status' => 'error', 'message' => 'ID danh muc khong hop le.']);
            }

            $validated = validateCategoryPayload($payload);
            if (isset($validated['error'])) {
                categoriesResponse(422, ['status' => 'error', 'message' => $validated['error']]);
            }

            $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE id = ?");
            $existsStmt->execute([$id]);
            if ((int) $existsStmt->fetchColumn() === 0) {
                categoriesResponse(404, ['status' => 'error', 'message' => 'Danh muc khong ton tai.']);
            }

            $data = $validated['data'];
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['description'], $id]);

            categoriesResponse(200, ['status' => 'success', 'message' => 'Cap nhat danh muc thanh cong.']);

        case 'DELETE':
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
            if ($id <= 0) {
                categoriesResponse(422, ['status' => 'error', 'message' => 'ID danh muc khong hop le.']);
            }

            $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE id = ?");
            $existsStmt->execute([$id]);
            if ((int) $existsStmt->fetchColumn() === 0) {
                categoriesResponse(404, ['status' => 'error', 'message' => 'Danh muc khong ton tai.']);
            }

            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);

            categoriesResponse(200, ['status' => 'success', 'message' => 'Xoa danh muc thanh cong.']);

        default:
            categoriesResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
    }
} catch (PDOException $exception) {
    $message = $exception->getCode() === '23000'
        ? 'Khong the xoa danh muc nay vi dang co du lieu lien ket.'
        : 'Loi Database: ' . $exception->getMessage();

    categoriesResponse(500, ['status' => 'error', 'message' => $message]);
}
