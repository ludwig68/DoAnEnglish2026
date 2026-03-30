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

function learningPathsResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function validateLearningPathPayload(array $data): array
{
    $title = trim((string) ($data['title'] ?? ''));
    $description = trim((string) ($data['description'] ?? ''));
    $targetAudience = trim((string) ($data['target_audience'] ?? ''));

    $titleCheck = Validator::checkStringLength($title, 3, 255);
    if ($titleCheck !== true) {
        return ['error' => 'Ten lo trinh phai tu 3 den 255 ky tu.'];
    }

    $audienceCheck = Validator::checkStringLength($targetAudience, 3, 255);
    if ($audienceCheck !== true) {
        return ['error' => 'Doi tuong muc tieu phai tu 3 den 255 ky tu.'];
    }

    if ($description !== '') {
        $descriptionCheck = Validator::checkStringLength($description, 10, 5000);
        if ($descriptionCheck !== true) {
            return ['error' => 'Mo ta lo trinh phai tu 10 den 5000 ky tu neu co nhap.'];
        }
    }

    return [
        'data' => [
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'target_audience' => $targetAudience,
        ],
    ];
}

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("
                SELECT id, title, description, target_audience, DATE_FORMAT(created_at, '%d/%m/%Y') AS created_at
                FROM learning_paths
                ORDER BY id DESC
            ");
            learningPathsResponse(200, ['status' => 'success', 'data' => $stmt->fetchAll()]);

        case 'POST':
            $validated = validateLearningPathPayload($payload);
            if (isset($validated['error'])) {
                learningPathsResponse(422, ['status' => 'error', 'message' => $validated['error']]);
            }

            $data = $validated['data'];
            $stmt = $pdo->prepare("
                INSERT INTO learning_paths (title, description, target_audience)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['target_audience'],
            ]);

            learningPathsResponse(200, ['status' => 'success', 'message' => 'Them lo trinh hoc thanh cong.']);

        case 'PUT':
            $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);
            if ($id <= 0) {
                learningPathsResponse(422, ['status' => 'error', 'message' => 'ID lo trinh khong hop le.']);
            }

            $validated = validateLearningPathPayload($payload);
            if (isset($validated['error'])) {
                learningPathsResponse(422, ['status' => 'error', 'message' => $validated['error']]);
            }

            $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM learning_paths WHERE id = ?");
            $existsStmt->execute([$id]);
            if ((int) $existsStmt->fetchColumn() === 0) {
                learningPathsResponse(404, ['status' => 'error', 'message' => 'Lo trinh hoc khong ton tai.']);
            }

            $data = $validated['data'];
            $stmt = $pdo->prepare("
                UPDATE learning_paths
                SET title = ?, description = ?, target_audience = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['target_audience'],
                $id,
            ]);

            learningPathsResponse(200, ['status' => 'success', 'message' => 'Cap nhat lo trinh hoc thanh cong.']);

        case 'DELETE':
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
            if ($id <= 0) {
                learningPathsResponse(422, ['status' => 'error', 'message' => 'ID lo trinh khong hop le.']);
            }

            $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM learning_paths WHERE id = ?");
            $existsStmt->execute([$id]);
            if ((int) $existsStmt->fetchColumn() === 0) {
                learningPathsResponse(404, ['status' => 'error', 'message' => 'Lo trinh hoc khong ton tai.']);
            }

            $stmt = $pdo->prepare("DELETE FROM learning_paths WHERE id = ?");
            $stmt->execute([$id]);

            learningPathsResponse(200, ['status' => 'success', 'message' => 'Xoa lo trinh hoc thanh cong.']);

        default:
            learningPathsResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
    }
} catch (PDOException $exception) {
    $message = $exception->getCode() === '23000'
        ? 'Khong the xoa lo trinh nay vi dang co khoa hoc lien ket.'
        : 'Loi Database: ' . $exception->getMessage();

    learningPathsResponse(500, ['status' => 'error', 'message' => $message]);
}
