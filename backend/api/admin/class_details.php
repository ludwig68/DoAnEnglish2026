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
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function validateGroupCapacity(string $value): array
{
    $required = Validator::isNotEmpty($value);
    if ($required !== true) {
        return ['error' => 'Vui lòng nhập sĩ số tối đa cho nhóm học.'];
    }

    $intCheck = Validator::checkIntOrDecimal($value, 'int');
    if ($intCheck !== true) {
        return ['error' => 'Sĩ số tối đa của nhóm học phải là số nguyên.'];
    }

    $positiveCheck = Validator::checkPositiveNegative((int) $value, 'positive');
    if ($positiveCheck !== true) {
        return ['error' => 'Sĩ số tối đa của nhóm học phải lớn hơn 0.'];
    }

    $rangeCheck = Validator::checkNumberRange((int) $value, 1, 500);
    if ($rangeCheck !== true) {
        return ['error' => 'Sĩ số tối đa của nhóm học phải nằm trong khoảng từ 1 đến 500.'];
    }

    return ['value' => (int) $value];
}

switch ($method) {
    case 'GET':
        $classId = isset($_GET['class_id']) ? (int) $_GET['class_id'] : 0;

        try {
            if ($classId > 0) {
                $stmt = $pdo->prepare("
                    SELECT
                        cd.id,
                        cd.class_id,
                        cd.detail_name,
                        cd.max_students,
                        cd.created_at,
                        (
                            SELECT COUNT(*)
                            FROM enrollments e
                            WHERE e.class_detail_id = cd.id
                              AND e.status = 'active'
                        ) AS current_students
                    FROM class_details cd
                    WHERE cd.class_id = ?
                    ORDER BY cd.created_at DESC, cd.id DESC
                ");
                $stmt->execute([$classId]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                jsonResponse(200, ['status' => 'success', 'data' => $data]);
            }

            $stmt = $pdo->query("
                SELECT
                    cd.id,
                    cd.class_id,
                    cd.detail_name,
                    cd.max_students,
                    cd.created_at,
                    (
                        SELECT COUNT(*)
                        FROM enrollments e
                        WHERE e.class_detail_id = cd.id
                          AND e.status = 'active'
                    ) AS current_students
                FROM class_details cd
                ORDER BY cd.class_id ASC, cd.created_at DESC, cd.id DESC
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonResponse(200, ['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ khi tải danh sách nhóm học.']);
        }
        break;

    case 'POST':
        $payload = json_decode(file_get_contents("php://input"), true) ?? [];
        $classId = isset($payload['class_id']) ? (int) $payload['class_id'] : 0;
        $detailName = trim((string) ($payload['detail_name'] ?? ''));
        $maxStudents = trim((string) ($payload['max_students'] ?? '20'));

        if ($classId <= 0) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Lớp học không hợp lệ.']);
        }

        $nameCheck = Validator::checkStringLength($detailName, 3, 255);
        if ($nameCheck !== true) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Tên nhóm phải từ 3 đến 255 ký tự.']);
        }

        $capacityCheck = validateGroupCapacity($maxStudents);
        if (isset($capacityCheck['error'])) {
            jsonResponse(422, ['status' => 'error', 'message' => $capacityCheck['error']]);
        }

        try {
            $classStmt = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE id = ?");
            $classStmt->execute([$classId]);
            if ((int) $classStmt->fetchColumn() === 0) {
                jsonResponse(404, ['status' => 'error', 'message' => 'Lớp học không tồn tại hoặc đã bị xóa.']);
            }

            $stmt = $pdo->prepare("
                INSERT INTO class_details (class_id, detail_name, max_students)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$classId, $detailName, $capacityCheck['value']]);

            jsonResponse(201, [
                'status' => 'success',
                'message' => 'Thêm nhóm lịch học thành công.',
                'data' => [
                    'id' => (int) $pdo->lastInsertId(),
                    'class_id' => $classId,
                    'detail_name' => $detailName,
                    'max_students' => $capacityCheck['value'],
                    'current_students' => 0,
                ],
            ]);
        } catch (PDOException $e) {
            jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ khi thêm dữ liệu.']);
        }
        break;

    case 'PUT':
        $payload = json_decode(file_get_contents("php://input"), true) ?? [];
        $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);
        $detailName = trim((string) ($payload['detail_name'] ?? ''));
        $maxStudents = trim((string) ($payload['max_students'] ?? ''));

        if ($id <= 0) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Mã nhóm không hợp lệ.']);
        }

        $nameCheck = Validator::checkStringLength($detailName, 3, 255);
        if ($nameCheck !== true) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Tên nhóm phải từ 3 đến 255 ký tự.']);
        }

        $capacityCheck = validateGroupCapacity($maxStudents);
        if (isset($capacityCheck['error'])) {
            jsonResponse(422, ['status' => 'error', 'message' => $capacityCheck['error']]);
        }

        try {
            $detailStmt = $pdo->prepare("
                SELECT
                    cd.class_id,
                    (
                        SELECT COUNT(*)
                        FROM enrollments e
                        WHERE e.class_detail_id = cd.id
                          AND e.status = 'active'
                    ) AS current_students
                FROM class_details cd
                WHERE cd.id = ?
            ");
            $detailStmt->execute([$id]);
            $detailData = $detailStmt->fetch(PDO::FETCH_ASSOC);

            if (!$detailData) {
                jsonResponse(404, ['status' => 'error', 'message' => 'Nhóm học không tồn tại hoặc đã bị xóa.']);
            }

            $currentStudents = (int) $detailData['current_students'];

            if ($capacityCheck['value'] < $currentStudents) {
                jsonResponse(422, ['status' => 'error', 'message' => "Sĩ số mới không được nhỏ hơn số học viên hiện tại của nhóm ({$currentStudents})."]);
            }

            $stmt = $pdo->prepare("
                UPDATE class_details
                SET detail_name = ?, max_students = ?
                WHERE id = ?
            ");
            $success = $stmt->execute([$detailName, $capacityCheck['value'], $id]);

            if ($stmt->rowCount() > 0 || $success) {
                jsonResponse(200, ['status' => 'success', 'message' => 'Cập nhật nhóm thành công.']);
            }

            jsonResponse(422, ['status' => 'error', 'message' => 'Không tìm thấy nhóm cần sửa.']);
        } catch (PDOException $e) {
            jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ khi sửa dữ liệu.']);
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Mã nhóm không hợp lệ.']);
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM class_details WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() > 0) {
                jsonResponse(200, ['status' => 'success', 'message' => 'Xóa nhóm lịch thành công.']);
            }

            jsonResponse(422, ['status' => 'error', 'message' => 'Không tìm thấy nhóm hoặc đã bị xóa.']);
        } catch (PDOException $e) {
            jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ: Có thể nhóm này đang chứa lịch đã học.']);
        }
        break;

    default:
        jsonResponse(405, ['status' => 'error', 'message' => 'Method not allowed']);
}
