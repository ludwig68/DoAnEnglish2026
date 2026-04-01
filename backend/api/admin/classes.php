<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

$method = $_SERVER['REQUEST_METHOD'];
JwtHelper::requireAuth('admin');

function classJsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function fetchInstructorOptions(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT id, full_name
        FROM users
        WHERE role = 'instructor'
          AND status = 'active'
        ORDER BY full_name ASC, id ASC
    ");

    return $stmt->fetchAll();
}

function validateClassPayload(PDO $pdo, array $data): array
{
    $className = trim((string) ($data['class_name'] ?? ''));
    $courseId = trim((string) ($data['course_id'] ?? ''));
    $instructorId = trim((string) ($data['instructor_id'] ?? ''));
    $startDate = trim((string) ($data['start_date'] ?? ''));
    $endDate = trim((string) ($data['end_date'] ?? ''));

    $nameCheck = Validator::checkStringLength($className, 3, 150);
    if ($nameCheck !== true) {
        return ['error' => 'Tên lớp phải từ 3 đến 150 ký tự.'];
    }

    $courseRequired = Validator::isNotEmpty($courseId);
    if ($courseRequired !== true) {
        return ['error' => 'Vui lòng chọn khóa học.'];
    }

    $courseInt = Validator::checkIntOrDecimal($courseId, 'int');
    if ($courseInt !== true) {
        return ['error' => 'Mã khóa học phải là số nguyên.'];
    }

    $coursePositive = Validator::checkPositiveNegative((int) $courseId, 'positive');
    if ($coursePositive !== true) {
        return ['error' => 'Mã khóa học phải lớn hơn 0.'];
    }

    $courseStmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE id = ?");
    $courseStmt->execute([(int) $courseId]);
    if ((int) $courseStmt->fetchColumn() === 0) {
        return ['error' => 'Khóa học được chọn không tồn tại.'];
    }

    if ($instructorId !== '') {
        $instructorInt = Validator::checkIntOrDecimal($instructorId, 'int');
        if ($instructorInt !== true) {
            return ['error' => 'Mã giảng viên phải là số nguyên.'];
        }

        $instructorPositive = Validator::checkPositiveNegative((int) $instructorId, 'positive');
        if ($instructorPositive !== true) {
            return ['error' => 'Mã giảng viên phải lớn hơn 0.'];
        }

        $instructorStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM users
            WHERE id = ?
              AND role = 'instructor'
              AND status = 'active'
        ");
        $instructorStmt->execute([(int) $instructorId]);
        if ((int) $instructorStmt->fetchColumn() === 0) {
            return ['error' => 'Giảng viên được chọn không hợp lệ.'];
        }
    }

    $startDateCheck = Validator::isValidDate($startDate, 'Y-m-d');
    if ($startDateCheck !== true) {
        return ['error' => 'Ngày khai giảng không hợp lệ.'];
    }

    $endDateCheck = Validator::isValidDate($endDate, 'Y-m-d');
    if ($endDateCheck !== true) {
        return ['error' => 'Ngày kết thúc không hợp lệ.'];
    }

    $dateRangeCheck = Validator::checkStartEndDate($startDate, $endDate, 'Y-m-d');
    if ($dateRangeCheck !== true) {
        return ['error' => 'Ngày khai giảng không được lớn hơn ngày kết thúc.'];
    }

    return [
        'data' => [
            'class_name' => $className,
            'course_id' => (int) $courseId,
            'instructor_id' => $instructorId !== '' ? (int) $instructorId : null,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ],
    ];
}

switch ($method) {
    case 'GET':
        $stmt = $pdo->query("
            SELECT
                c.*,
                co.title AS course_name,
                u.full_name AS instructor_name
            FROM classes c
            LEFT JOIN courses co ON c.course_id = co.id
            LEFT JOIN users u ON c.instructor_id = u.id
            ORDER BY c.created_at DESC, c.id DESC
        ");
        $classes = $stmt->fetchAll();

        $courses = $pdo->query("SELECT id, title FROM courses ORDER BY title ASC, id ASC")->fetchAll();
        $instructors = fetchInstructorOptions($pdo);

        classJsonResponse(200, [
            'status' => 'success',
            'data' => $classes,
            'courses' => $courses,
            'instructors' => $instructors,
        ]);
        break;

    case 'POST':
        $payload = json_decode(file_get_contents("php://input"), true) ?? [];
        $validated = validateClassPayload($pdo, $payload);
        if (isset($validated['error'])) {
            classJsonResponse(422, ['status' => 'error', 'message' => $validated['error']]);
        }

        $data = $validated['data'];
        $stmt = $pdo->prepare("
            INSERT INTO classes (course_id, instructor_id, class_name, start_date, end_date)
            VALUES (?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $data['course_id'],
            $data['instructor_id'],
            $data['class_name'],
            $data['start_date'],
            $data['end_date'],
        ]);

        if (!$success) {
            classJsonResponse(500, ['status' => 'error', 'message' => 'Lỗi CSDL khi thêm lớp học.']);
        }

        classJsonResponse(200, ['status' => 'success', 'message' => 'Tạo lớp học mới thành công.']);
        break;

    case 'PUT':
        $payload = json_decode(file_get_contents("php://input"), true) ?? [];
        $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);
        if ($id <= 0) {
            classJsonResponse(422, ['status' => 'error', 'message' => 'ID lớp học không hợp lệ.']);
        }

        $validated = validateClassPayload($pdo, $payload);
        if (isset($validated['error'])) {
            classJsonResponse(422, ['status' => 'error', 'message' => $validated['error']]);
        }

        $data = $validated['data'];
        $stmt = $pdo->prepare("
            UPDATE classes
            SET course_id = ?, instructor_id = ?, class_name = ?, start_date = ?, end_date = ?
            WHERE id = ?
        ");

        $success = $stmt->execute([
            $data['course_id'],
            $data['instructor_id'],
            $data['class_name'],
            $data['start_date'],
            $data['end_date'],
            $id,
        ]);

        if (!$success) {
            classJsonResponse(500, ['status' => 'error', 'message' => 'Lỗi cập nhật CSDL.']);
        }

        classJsonResponse(200, ['status' => 'success', 'message' => 'Cập nhật thông tin lớp thành công.']);
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $idCheck = Validator::checkPositiveNegative($id, 'positive');
        if ($idCheck !== true) {
            classJsonResponse(422, ['status' => 'error', 'message' => 'ID lớp học không hợp lệ.']);
        }

        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $success = $stmt->execute([$id]);

        if (!$success) {
            classJsonResponse(500, ['status' => 'error', 'message' => 'Lỗi xóa lớp học.']);
        }

        classJsonResponse(200, ['status' => 'success', 'message' => 'Đã xóa lớp học thành công.']);
        break;

    default:
        classJsonResponse(405, ['status' => 'error', 'message' => 'Method not allowed.']);
}
