<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

JwtHelper::requireAuth('admin');

$method = $_SERVER['REQUEST_METHOD'];

function jsonResponse(int $statusCode, array $body): void
{
    http_response_code($statusCode);
    echo json_encode($body);
    exit;
}

switch ($method) {
    case 'GET':
        // Lấy danh sách nhóm theo class_id
        $classId = isset($_GET['class_id']) ? (int) $_GET['class_id'] : 0;
        
        try {
            if ($classId > 0) {
                // Lấy nhóm của 1 lớp
                $stmt = $pdo->prepare("
                    SELECT id, class_id, detail_name, created_at 
                    FROM class_details 
                    WHERE class_id = ? 
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$classId]);
                $data = $stmt->fetchAll();
                
                jsonResponse(200, ['status' => 'success', 'data' => $data]);
            } else {
                // Lấy tất cả (không bắt buộc, nhưng để phòng hờ)
                $stmt = $pdo->query("
                    SELECT id, class_id, detail_name, created_at 
                    FROM class_details 
                    ORDER BY class_id ASC, created_at DESC
                ");
                $data = $stmt->fetchAll();
                jsonResponse(200, ['status' => 'success', 'data' => $data]);
            }
        } catch (PDOException $e) {
            jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ: ' . escapeshellarg($e->getMessage())]);
        }
        break;

    case 'POST':
        // Thêm nhóm mới
        $payload = json_decode(file_get_contents("php://input"), true) ?? [];
        $classId = isset($payload['class_id']) ? (int) $payload['class_id'] : 0;
        $detailName = trim((string) ($payload['detail_name'] ?? ''));

        if ($classId <= 0) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Lớp học không hợp lệ.']);
        }
        if (mb_strlen($detailName) < 3 || mb_strlen($detailName) > 255) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Tên nhóm phải từ 3 đến 255 ký tự.']);
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO class_details (class_id, detail_name) VALUES (?, ?)");
            $stmt->execute([$classId, $detailName]);
            jsonResponse(201, [
                'status' => 'success', 
                'message' => 'Thêm nhóm lịch học thành công.',
                'data' => [
                    'id' => $pdo->lastInsertId(),
                    'class_id' => $classId,
                    'detail_name' => $detailName
                ]
            ]);
        } catch (PDOException $e) {
             jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ khi thêm dữ liệu.']);
        }
        break;

    case 'PUT':
        // Cập nhật tên nhóm
        $payload = json_decode(file_get_contents("php://input"), true) ?? [];
        $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);
        $detailName = trim((string) ($payload['detail_name'] ?? ''));

        if ($id <= 0) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Mã nhóm không hợp lệ.']);
        }
        if (mb_strlen($detailName) < 3 || mb_strlen($detailName) > 255) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Tên nhóm phải từ 3 đến 255 ký tự.']);
        }

        try {
            $stmt = $pdo->prepare("UPDATE class_details SET detail_name = ? WHERE id = ?");
            $success = $stmt->execute([$detailName, $id]);
            
            if ($stmt->rowCount() > 0 || $success) {
                jsonResponse(200, ['status' => 'success', 'message' => 'Cập nhật nhóm thành công.']);
            } else {
                jsonResponse(422, ['status' => 'error', 'message' => 'Không tìm thấy nhóm cần sửa.']);
            }
        } catch (PDOException $e) {
            jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ khi sửa dữ liệu.']);
        }
        break;

    case 'DELETE':
        // Xóa nhóm
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Mã nhóm không hợp lệ.']);
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM class_details WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() > 0) {
                jsonResponse(200, ['status' => 'success', 'message' => 'Xóa nhóm lịch thành công.']);
            } else {
                jsonResponse(422, ['status' => 'error', 'message' => 'Không tìm thấy nhóm hoặc đã bị xóa.']);
            }
        } catch (PDOException $e) {
             jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ: Có thể nhóm này đang chứa lịch đã học.']);
        }
        break;

    default:
        jsonResponse(405, ['status' => 'error', 'message' => 'Method not allowed']);
}
