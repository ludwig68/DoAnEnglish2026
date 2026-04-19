<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

JwtHelper::requireAuth('admin');

$method = $_SERVER['REQUEST_METHOD'];

function enrollmentResponse(int $code, array $body): void
{
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function loadClassDetail(PDO $pdo, int $classDetailId): ?array
{
    $stmt = $pdo->prepare("
        SELECT id, class_id, max_students
        FROM class_details
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$classDetailId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function loadClassDetailCapacity(PDO $pdo, int $classDetailId): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            cd.class_id,
            cd.max_students,
            COUNT(e.id) AS current_students
        FROM class_details cd
        LEFT JOIN enrollments e
            ON e.class_detail_id = cd.id
           AND e.status = 'active'
        WHERE cd.id = ?
        GROUP BY cd.id, cd.class_id, cd.max_students
    ");
    $stmt->execute([$classDetailId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

switch ($method) {
    case 'GET':
        try {
            $studentsStmt = $pdo->query("
                SELECT id, full_name, email, phone
                FROM users
                WHERE role = 'student' AND status = 'active'
                ORDER BY full_name ASC
            ");
            $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

            $coursesStmt = $pdo->query("
                SELECT id, title, level
                FROM courses
                ORDER BY title ASC
            ");
            $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

            $rowsStmt = $pdo->query("
                SELECT
                    cd.id AS detail_id,
                    cd.class_id,
                    cd.detail_name,
                    cd.max_students,
                    COUNT(DISTINCT e.id) AS current_students,
                    c.class_name,
                    c.start_date,
                    c.end_date,
                    co.id AS course_id,
                    co.title AS course_name,
                    co.level,
                    MAX(s.teaching_type) AS teaching_type,
                    MAX(s.start_time) AS start_time,
                    MAX(s.end_time) AS end_time
                FROM class_details cd
                INNER JOIN classes c ON c.id = cd.class_id
                LEFT JOIN courses co ON co.id = c.course_id
                LEFT JOIN enrollments e ON e.class_detail_id = cd.id AND e.status = 'active'
                LEFT JOIN schedules s ON s.class_detail_id = cd.id
                GROUP BY
                    cd.id,
                    cd.class_id,
                    cd.detail_name,
                    cd.max_students,
                    c.class_name,
                    c.start_date,
                    c.end_date,
                    co.id,
                    co.title,
                    co.level
                ORDER BY co.title ASC, c.id DESC, cd.id ASC
            ");
            $classRows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($classRows as &$row) {
                $row['max_students'] = (int) $row['max_students'];
                $row['current_students'] = (int) $row['current_students'];
                $row['course_id'] = (int) $row['course_id'];
                $row['class_id'] = (int) $row['class_id'];
                $row['detail_id'] = (int) $row['detail_id'];
            }
            unset($row);

            $enrollStmt = $pdo->query("
                SELECT
                    e.id,
                    e.status,
                    e.enrollment_date,
                    e.class_id,
                    e.class_detail_id,
                    u.full_name AS student_name,
                    u.email AS student_email,
                    c.class_name,
                    cd.detail_name,
                    cd.max_students,
                    COUNT(e2.id) AS current_students
                FROM enrollments e
                INNER JOIN users u ON u.id = e.student_id
                INNER JOIN classes c ON c.id = e.class_id
                LEFT JOIN class_details cd ON cd.id = e.class_detail_id
                LEFT JOIN enrollments e2
                    ON e2.class_detail_id = e.class_detail_id
                   AND e2.status = 'active'
                GROUP BY
                    e.id,
                    e.status,
                    e.enrollment_date,
                    e.class_id,
                    e.class_detail_id,
                    u.full_name,
                    u.email,
                    c.class_name,
                    cd.detail_name,
                    cd.max_students
                ORDER BY e.enrollment_date DESC
            ");
            $enrollments = $enrollStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($enrollments as &$row) {
                $row['max_students'] = (int) ($row['max_students'] ?? 0);
                $row['current_students'] = (int) ($row['current_students'] ?? 0);
            }
            unset($row);

            enrollmentResponse(200, [
                'status' => 'success',
                'data' => compact('students', 'courses', 'classRows', 'enrollments'),
            ]);
        } catch (PDOException $e) {
            enrollmentResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $studentId = isset($input['student_id']) ? (int) $input['student_id'] : 0;
        $classId = isset($input['class_id']) ? (int) $input['class_id'] : 0;
        $classDetailId = isset($input['class_detail_id']) ? (int) $input['class_detail_id'] : 0;

        if ($studentId <= 0 || $classId <= 0 || $classDetailId <= 0) {
            enrollmentResponse(422, ['status' => 'error', 'message' => 'Vui lòng chọn đầy đủ học viên, lớp học và ca học.']);
        }

        try {
            $chkUser = $pdo->prepare("
                SELECT id
                FROM users
                WHERE id = ? AND role = 'student' AND status = 'active'
            ");
            $chkUser->execute([$studentId]);
            if (!$chkUser->fetch()) {
                enrollmentResponse(422, ['status' => 'error', 'message' => 'Học viên không hợp lệ hoặc đã bị khóa.']);
            }

            $classDetail = loadClassDetail($pdo, $classDetailId);
            if (!$classDetail) {
                enrollmentResponse(422, ['status' => 'error', 'message' => 'Ca học không tồn tại.']);
            }
            if ((int) $classDetail['class_id'] !== $classId) {
                enrollmentResponse(422, ['status' => 'error', 'message' => 'Ca học không thuộc lớp đã chọn.']);
            }

            $chkDupe = $pdo->prepare("
                SELECT id
                FROM enrollments
                WHERE student_id = ? AND class_id = ?
                LIMIT 1
            ");
            $chkDupe->execute([$studentId, $classId]);
            if ($chkDupe->fetch()) {
                enrollmentResponse(422, ['status' => 'error', 'message' => 'Học viên này đã được ghi danh vào lớp.']);
            }

            $capacity = loadClassDetailCapacity($pdo, $classDetailId);
            if (!$capacity) {
                enrollmentResponse(422, ['status' => 'error', 'message' => 'Ca học không tồn tại.']);
            }

            $maxStudents = (int) $capacity['max_students'];
            $currentStudents = (int) $capacity['current_students'];
            if ($currentStudents >= $maxStudents) {
                enrollmentResponse(422, [
                    'status' => 'error',
                    'message' => "Ca học này đã đủ sĩ số ({$currentStudents}/{$maxStudents}). Vui lòng chọn ca khác.",
                ]);
            }

            $stmt = $pdo->prepare("
                INSERT INTO enrollments (student_id, class_id, class_detail_id, status)
                VALUES (?, ?, ?, 'active')
            ");
            $stmt->execute([$studentId, $classId, $classDetailId]);

            enrollmentResponse(201, ['status' => 'success', 'message' => 'Đã ghi danh học viên vào lớp thành công.']);
        } catch (PDOException $e) {
            enrollmentResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            enrollmentResponse(400, ['status' => 'error', 'message' => 'ID ghi danh không hợp lệ.']);
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                enrollmentResponse(200, ['status' => 'success', 'message' => 'Đã hủy ghi danh thành công.']);
            }

            enrollmentResponse(404, ['status' => 'error', 'message' => 'Không tìm thấy bản ghi ghi danh.']);
        } catch (PDOException $e) {
            enrollmentResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $newDetailId = isset($input['class_detail_id']) ? (int) $input['class_detail_id'] : 0;

        if ($id <= 0 || $newDetailId <= 0) {
            enrollmentResponse(422, ['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.']);
        }

        try {
            $currStmt = $pdo->prepare("SELECT * FROM enrollments WHERE id = ?");
            $currStmt->execute([$id]);
            $currentEnrollment = $currStmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentEnrollment) {
                enrollmentResponse(404, ['status' => 'error', 'message' => 'Không tìm thấy bản ghi ghi danh.']);
            }

            if ((int) $currentEnrollment['class_detail_id'] === $newDetailId) {
                enrollmentResponse(200, ['status' => 'success', 'message' => 'Chưa có thay đổi gì.']);
            }

            $capacity = loadClassDetailCapacity($pdo, $newDetailId);
            if (!$capacity) {
                enrollmentResponse(422, ['status' => 'error', 'message' => 'Ca học mới không tồn tại.']);
            }

            $newClassId = (int) $capacity['class_id'];
            $maxStudents = (int) $capacity['max_students'];
            $currentStudents = (int) $capacity['current_students'];

            if ($currentStudents >= $maxStudents) {
                enrollmentResponse(422, [
                    'status' => 'error',
                    'message' => "Ca học mới đã đủ sĩ số ({$currentStudents}/{$maxStudents}).",
                ]);
            }

            if ($newClassId !== (int) $currentEnrollment['class_id']) {
                $chkDupe = $pdo->prepare("
                    SELECT id
                    FROM enrollments
                    WHERE student_id = ? AND class_id = ? AND status = 'active' AND id <> ?
                    LIMIT 1
                ");
                $chkDupe->execute([(int) $currentEnrollment['student_id'], $newClassId, $id]);
                if ($chkDupe->fetch()) {
                    enrollmentResponse(422, ['status' => 'error', 'message' => 'Học viên này đã ghi danh vào một ca khác trong lớp đó.']);
                }
            }

            $stmt = $pdo->prepare("
                UPDATE enrollments
                SET class_id = ?, class_detail_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$newClassId, $newDetailId, $id]);

            if ($stmt->rowCount() > 0) {
                enrollmentResponse(200, ['status' => 'success', 'message' => 'Đã chuyển sang lớp hoặc ca mới thành công.']);
            }

            enrollmentResponse(404, ['status' => 'error', 'message' => 'Không tìm thấy thay đổi.']);
        } catch (PDOException $e) {
            enrollmentResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
        break;

    default:
        enrollmentResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}
