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

function websiteContentsResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function normalizeWebsiteContentType(string $type): string
{
    return $type === 'link' ? 'text' : $type;
}

function validateWebsiteContentPayload(PDO $pdo, array $data, int $excludeId = 0): array
{
    $sectionKey = trim((string) ($data['section_key'] ?? ''));
    $contentType = normalizeWebsiteContentType(trim((string) ($data['content_type'] ?? 'text')));
    $contentValue = trim((string) ($data['content_value'] ?? ''));

    $sectionCheck = Validator::checkRegex(
        $sectionKey,
        '/^[a-z0-9_]{3,100}$/',
        'Section key chi duoc gom chu thuong, so va dau gach duoi, tu 3 den 100 ky tu.'
    );
    if ($sectionCheck !== true) {
        return ['error' => $sectionCheck];
    }

    $allowedTypes = ['text', 'image', 'html'];
    if (!in_array($contentType, $allowedTypes, true)) {
        return ['error' => 'Loai noi dung khong hop le.'];
    }

    $valueCheck = Validator::checkStringLength($contentValue, 1, 20000);
    if ($valueCheck !== true) {
        return ['error' => 'Noi dung phai tu 1 den 20000 ky tu.'];
    }

    if ($contentType === 'image') {
        $imageCheck = Validator::checkRegex($contentValue, '/^https?:\/\/\S+$/i', 'URL anh khong hop le.');
        if ($imageCheck !== true) {
            return ['error' => $imageCheck];
        }
    }

    $duplicateStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM website_contents
        WHERE section_key = ?
          AND id <> ?
    ");
    $duplicateStmt->execute([$sectionKey, $excludeId]);
    if ((int) $duplicateStmt->fetchColumn() > 0) {
        return ['error' => 'Section key nay da ton tai.'];
    }

    return [
        'data' => [
            'section_key' => $sectionKey,
            'content_type' => $contentType,
            'content_value' => $contentValue,
        ],
    ];
}

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("
                SELECT id, section_key, content_type, content_value, updated_at
                FROM website_contents
                ORDER BY section_key ASC, id ASC
            ");
            websiteContentsResponse(200, ['status' => 'success', 'data' => $stmt->fetchAll()]);

        case 'POST':
            $validated = validateWebsiteContentPayload($pdo, $payload);
            if (isset($validated['error'])) {
                websiteContentsResponse(422, ['status' => 'error', 'message' => $validated['error']]);
            }

            $data = $validated['data'];
            $stmt = $pdo->prepare("
                INSERT INTO website_contents (section_key, content_type, content_value)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $data['section_key'],
                $data['content_type'],
                $data['content_value'],
            ]);

            websiteContentsResponse(200, ['status' => 'success', 'message' => 'Them noi dung website thanh cong.']);

        case 'PUT':
            $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);
            if ($id <= 0) {
                websiteContentsResponse(422, ['status' => 'error', 'message' => 'ID noi dung khong hop le.']);
            }

            $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM website_contents WHERE id = ?");
            $existsStmt->execute([$id]);
            if ((int) $existsStmt->fetchColumn() === 0) {
                websiteContentsResponse(404, ['status' => 'error', 'message' => 'Noi dung website khong ton tai.']);
            }

            $validated = validateWebsiteContentPayload($pdo, $payload, $id);
            if (isset($validated['error'])) {
                websiteContentsResponse(422, ['status' => 'error', 'message' => $validated['error']]);
            }

            $data = $validated['data'];
            $stmt = $pdo->prepare("
                UPDATE website_contents
                SET section_key = ?, content_type = ?, content_value = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([
                $data['section_key'],
                $data['content_type'],
                $data['content_value'],
                $id,
            ]);

            websiteContentsResponse(200, ['status' => 'success', 'message' => 'Cap nhat noi dung website thanh cong.']);

        case 'DELETE':
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
            if ($id <= 0) {
                websiteContentsResponse(422, ['status' => 'error', 'message' => 'ID noi dung khong hop le.']);
            }

            $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM website_contents WHERE id = ?");
            $existsStmt->execute([$id]);
            if ((int) $existsStmt->fetchColumn() === 0) {
                websiteContentsResponse(404, ['status' => 'error', 'message' => 'Noi dung website khong ton tai.']);
            }

            $stmt = $pdo->prepare("DELETE FROM website_contents WHERE id = ?");
            $stmt->execute([$id]);

            websiteContentsResponse(200, ['status' => 'success', 'message' => 'Xoa noi dung website thanh cong.']);

        default:
            websiteContentsResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
    }
} catch (PDOException $exception) {
    websiteContentsResponse(500, ['status' => 'error', 'message' => 'Loi Database: ' . $exception->getMessage()]);
}
