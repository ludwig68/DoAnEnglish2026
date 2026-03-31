<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

JwtHelper::requireAuth('admin');

$method = $_SERVER['REQUEST_METHOD'];

function jsonResponse(int $code, array $body): void {
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

switch ($method) {

    // ─── GET: Dữ liệu form + bảng chọn lớp + danh sách ghi danh ───
    case 'GET':
        try {
            // 1. Học viên đang hoạt động
            $studentsStmt = $pdo->query("
                SELECT id, full_name, email, phone
                FROM users
                WHERE role = 'student' AND status = 'active'
                ORDER BY full_name ASC
            ");
            $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Khóa học (để filter)
            $coursesStmt = $pdo->query("
                SELECT id, title, level FROM courses ORDER BY title ASC
            ");
            $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Rows cho bảng chọn lớp — mỗi row = 1 class_detail
            //    JOIN: classes → courses, enrollments (đếm), schedules (lấy giờ/hình thức đại diện)
            $rowsStmt = $pdo->query("
                SELECT
                    cd.id          AS detail_id,
                    cd.class_id,
                    cd.detail_name,
                    cd.max_students,
                    COUNT(DISTINCT e.id) AS current_students,
                    c.class_name,
                    c.start_date,
                    c.end_date,
                    co.id          AS course_id,
                    co.title       AS course_name,
                    co.level,
                    MAX(s.teaching_type) AS teaching_type,
                    MAX(s.start_time)    AS start_time,
                    MAX(s.end_time)      AS end_time
                FROM class_details cd
                JOIN   classes     c  ON c.id  = cd.class_id
                LEFT JOIN courses  co ON co.id = c.course_id
                LEFT JOIN enrollments e  ON e.class_detail_id = cd.id AND e.status = 'active'
                LEFT JOIN schedules   s  ON s.class_detail_id = cd.id
                GROUP BY cd.id
                ORDER BY co.title ASC, c.id DESC, cd.id ASC
            ");
            $classRows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($classRows as &$row) {
                $row['max_students']     = (int)$row['max_students'];
                $row['current_students'] = (int)$row['current_students'];
                $row['course_id']        = (int)$row['course_id'];
                $row['class_id']         = (int)$row['class_id'];
                $row['detail_id']        = (int)$row['detail_id'];
            }
            unset($row);

            // 4. Danh sách ghi danh đã có (bảng phía dưới)
            $enrollStmt = $pdo->query("
                SELECT
                    e.id, e.status, e.enrollment_date, e.class_id, e.class_detail_id,
                    u.full_name  AS student_name,
                    u.email      AS student_email,
                    c.class_name,
                    cd.detail_name,
                    cd.max_students,
                    COUNT(e2.id) AS current_students
                FROM enrollments e
                JOIN  users          u   ON u.id  = e.student_id
                JOIN  classes        c   ON c.id  = e.class_id
                LEFT JOIN class_details cd ON cd.id = e.class_detail_id
                LEFT JOIN enrollments   e2 ON e2.class_detail_id = e.class_detail_id
                                          AND e2.status = 'active'
                GROUP BY e.id
                ORDER BY e.enrollment_date DESC
            ");
            $enrollments = $enrollStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($enrollments as &$row) {
                $row['max_students']     = (int)($row['max_students'] ?? 0);
                $row['current_students'] = (int)($row['current_students'] ?? 0);
            }
            unset($row);

            jsonResponse(200, [
                'status' => 'success',
                'data'   => compact('students', 'courses', 'classRows', 'enrollments')
            ]);

        } catch (PDOException $e) {
            jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
        break;

    // ─────────────────────────────────────────────────────────────
    // POST — Ghi danh học viên
    // ─────────────────────────────────────────────────────────────
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $studentId     = isset($input['student_id'])      ? (int)$input['student_id']      : 0;
        $classId       = isset($input['class_id'])        ? (int)$input['class_id']        : 0;
        $classDetailId = isset($input['class_detail_id']) ? (int)$input['class_detail_id'] : 0;

        // Validate bắt buộc
        if ($studentId <= 0 || $classId <= 0 || $classDetailId <= 0) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Vui lòng chọn đầy đủ Học viên, Lớp học và Ca học.']);
        }

        try {
            // Kiểm tra học viên hợp lệ
            $chkUser = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'student' AND status = 'active'");
            $chkUser->execute([$studentId]);
            if ($chkUser->rowCount() === 0) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Học viên không hợp lệ hoặc đã bị khóa.']);
            }

            // Kiểm tra trùng lặp — 1 học viên không được ghi danh 2 lần vào cùng 1 lớp
            $chkDupe = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND class_id = ?");
            $chkDupe->execute([$studentId, $classId]);
            if ($chkDupe->rowCount() > 0) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Học viên này đã được ghi danh vào lớp.']);
            }

            // Kiểm tra sức chứa của ca học
            $capStmt = $pdo->prepare("
                SELECT
                    cd.max_students,
                    COUNT(e.id) AS current_students
                FROM class_details cd
                LEFT JOIN enrollments e
                    ON e.class_detail_id = cd.id AND e.status = 'active'
                WHERE cd.id = ?
                GROUP BY cd.id
            ");
            $capStmt->execute([$classDetailId]);
            $cap = $capStmt->fetch(PDO::FETCH_ASSOC);

            if (!$cap) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Ca học không tồn tại.']);
            }

            $maxStudents     = (int) $cap['max_students'];
            $currentStudents = (int) $cap['current_students'];

            if ($currentStudents >= $maxStudents) {
                jsonResponse(422, [
                    'status'  => 'error',
                    'message' => "Ca học này đã đủ sĩ số ({$currentStudents}/{$maxStudents}). Vui lòng chọn ca khác."
                ]);
            }

            // Thực hiện INSERT
            $ins = $pdo->prepare("
                INSERT INTO enrollments (student_id, class_id, class_detail_id, status)
                VALUES (?, ?, ?, 'active')
            ");
            $ins->execute([$studentId, $classId, $classDetailId]);

            jsonResponse(201, ['status' => 'success', 'message' => 'Đã ghi danh học viên vào lớp thành công.']);

        } catch (PDOException $e) {
            jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
        break;

    // ─────────────────────────────────────────────────────────────
    // DELETE — Hủy ghi danh
    // ─────────────────────────────────────────────────────────────
    case 'DELETE':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) jsonResponse(400, ['status' => 'error', 'message' => 'ID ghi danh không hợp lệ.']);

        try {
            $del = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
            $del->execute([$id]);
            if ($del->rowCount() > 0) {
                jsonResponse(200, ['status' => 'success', 'message' => 'Đã hủy ghi danh thành công.']);
            } else {
                jsonResponse(404, ['status' => 'error', 'message' => 'Không tìm thấy bản ghi ghi danh.']);
            }
        } catch (PDOException $e) {
            jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
        break;

    // ─────────────────────────────────────────────────────────────
    // PUT — Đổi lớp / Đổi ca học
    // ─────────────────────────────────────────────────────────────
    case 'PUT':
        $id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $newDetailId = isset($input['class_detail_id']) ? (int)$input['class_detail_id'] : 0;

        if ($id <= 0 || $newDetailId <= 0) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.']);
        }

        try {
            // Bước 1: Lấy bản ghi hiện tại
            $currStmt = $pdo->prepare("SELECT * FROM enrollments WHERE id = ?");
            $currStmt->execute([$id]);
            $currentEnrollment = $currStmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentEnrollment) {
                jsonResponse(404, ['status' => 'error', 'message' => 'Không tìm thấy bản ghi ghi danh.']);
            }

            // Nếu không thay đổi ca học
            if ($currentEnrollment['class_detail_id'] == $newDetailId) {
                jsonResponse(200, ['status' => 'success', 'message' => 'Chưa có thay đổi gì.']);
            }

            // Bước 2: Kiểm tra sĩ số ca học mới và lấy class_id của nó
            $capStmt = $pdo->prepare("
                SELECT cd.class_id, cd.max_students, COUNT(e.id) AS current_students
                FROM class_details cd
                LEFT JOIN enrollments e ON e.class_detail_id = cd.id AND e.status = 'active'
                WHERE cd.id = ? GROUP BY cd.id
            ");
            $capStmt->execute([$newDetailId]);
            $cap = $capStmt->fetch(PDO::FETCH_ASSOC);

            if (!$cap) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Ca học mới không tồn tại.']);
            }

            $newClassId      = $cap['class_id'];
            $maxStudents     = (int) $cap['max_students'];
            $currentStudents = (int) $cap['current_students'];

            if ($currentStudents >= $maxStudents) {
                jsonResponse(422, [
                    'status'  => 'error',
                    'message' => "Ca học mới đã đủ sĩ số ({$currentStudents}/{$maxStudents})."
                ]);
            }

            // Kiểm tra trùng lặp nếu ca học mới thuộc một lớp hoàn toàn khác
            // (Đảm bảo 1 học viên không ghi danh 2 ca khác nhau của cùng 1 lớp mới)
            if ($newClassId != $currentEnrollment['class_id']) {
                $chkDupe = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND class_id = ? AND status = 'active' AND id != ?");
                $chkDupe->execute([$currentEnrollment['student_id'], $newClassId, $id]);
                if ($chkDupe->rowCount() > 0) {
                    jsonResponse(422, ['status' => 'error', 'message' => 'Học viên này đã ghi danh vào một ca khác trong lớp đó.']);
                }
            }

            // Bước 3: Cập nhật
            $stmt = $pdo->prepare("UPDATE enrollments SET class_id = ?, class_detail_id = ? WHERE id = ?");
            $stmt->execute([$newClassId, $newDetailId, $id]);
            
            if ($stmt->rowCount() > 0) {
                jsonResponse(200, ['status' => 'success', 'message' => 'Đã chuyển sang lớp/ca mới thành công.']);
            } else {
                jsonResponse(404, ['status' => 'error', 'message' => 'Không tìm thấy thay đổi.']);
            }
        } catch (PDOException $e) {
            jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
        break;

    default:
        jsonResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}
