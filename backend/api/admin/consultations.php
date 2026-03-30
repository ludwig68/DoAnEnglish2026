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

function consultationsResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function validateConsultationPayload(array $data): array
{
    $fullName = trim((string) ($data['full_name'] ?? ''));
    $phone = trim((string) ($data['phone'] ?? ''));
    $email = trim((string) ($data['email'] ?? ''));
    $note = trim((string) ($data['note'] ?? ''));
    $status = trim((string) ($data['status'] ?? 'pending'));

    $nameCheck = Validator::checkStringLength($fullName, 2, 100);
    if ($nameCheck !== true) {
        return ['error' => 'Ho ten phai tu 2 den 100 ky tu.'];
    }

    $phoneCheck = Validator::checkRegex($phone, '/^[0-9+\s().-]{8,20}$/', 'So dien thoai khong hop le.');
    if ($phoneCheck !== true) {
        return ['error' => $phoneCheck];
    }

    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return ['error' => 'Email khong hop le.'];
    }

    if ($note !== '') {
        $noteCheck = Validator::checkStringLength($note, 3, 2000);
        if ($noteCheck !== true) {
            return ['error' => 'Ghi chu phai tu 3 den 2000 ky tu neu co nhap.'];
        }
    }

    $allowedStatuses = ['pending', 'contacted', 'resolved'];
    if (!in_array($status, $allowedStatuses, true)) {
        return ['error' => 'Trang thai tu van khong hop le.'];
    }

    return [
        'data' => [
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email !== '' ? $email : null,
            'note' => $note !== '' ? $note : null,
            'status' => $status,
        ],
    ];
}

try {
    switch ($method) {
        case 'GET':
            $stmt = $pdo->query("
                SELECT *
                FROM consultations
                ORDER BY created_at DESC, id DESC
            ");
            consultationsResponse(200, ['status' => 'success', 'data' => $stmt->fetchAll()]);

        case 'POST':
            $validated = validateConsultationPayload($payload);
            if (isset($validated['error'])) {
                consultationsResponse(422, ['status' => 'error', 'message' => $validated['error']]);
            }

            $data = $validated['data'];
            $stmt = $pdo->prepare("
                INSERT INTO consultations (full_name, phone, email, note, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['full_name'],
                $data['phone'],
                $data['email'],
                $data['note'],
                $data['status'],
            ]);

            consultationsResponse(200, ['status' => 'success', 'message' => 'Them yeu cau tu van thanh cong.']);

        case 'PUT':
            $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);
            if ($id <= 0) {
                consultationsResponse(422, ['status' => 'error', 'message' => 'ID yeu cau tu van khong hop le.']);
            }

            $validated = validateConsultationPayload($payload);
            if (isset($validated['error'])) {
                consultationsResponse(422, ['status' => 'error', 'message' => $validated['error']]);
            }

            $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM consultations WHERE id = ?");
            $existsStmt->execute([$id]);
            if ((int) $existsStmt->fetchColumn() === 0) {
                consultationsResponse(404, ['status' => 'error', 'message' => 'Yeu cau tu van khong ton tai.']);
            }

            $data = $validated['data'];
            $stmt = $pdo->prepare("
                UPDATE consultations
                SET full_name = ?, phone = ?, email = ?, note = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['full_name'],
                $data['phone'],
                $data['email'],
                $data['note'],
                $data['status'],
                $id,
            ]);

            consultationsResponse(200, ['status' => 'success', 'message' => 'Cap nhat yeu cau tu van thanh cong.']);

        case 'DELETE':
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
            if ($id <= 0) {
                consultationsResponse(422, ['status' => 'error', 'message' => 'ID yeu cau tu van khong hop le.']);
            }

            $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM consultations WHERE id = ?");
            $existsStmt->execute([$id]);
            if ((int) $existsStmt->fetchColumn() === 0) {
                consultationsResponse(404, ['status' => 'error', 'message' => 'Yeu cau tu van khong ton tai.']);
            }

            $stmt = $pdo->prepare("DELETE FROM consultations WHERE id = ?");
            $stmt->execute([$id]);

            consultationsResponse(200, ['status' => 'success', 'message' => 'Xoa yeu cau tu van thanh cong.']);

        default:
            consultationsResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
    }
} catch (PDOException $exception) {
    consultationsResponse(500, ['status' => 'error', 'message' => 'Loi Database: ' . $exception->getMessage()]);
}
