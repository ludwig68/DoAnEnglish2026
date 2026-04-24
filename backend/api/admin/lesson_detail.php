<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';
require_once __DIR__ . '/../../utils/Validator.php';

JwtHelper::requireAuth('admin');

function lessonDetailResponse(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function loadScheduleContext(PDO $pdo, int $scheduleId): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            s.*,
            c.class_name,
            c.course_id,
            u.full_name AS teacher_name
        FROM (
            SELECT
                sch.id,
                sch.class_id,
                sch.lesson_id,
                sch.teacher_id,
                sch.study_date,
                sch.start_time,
                sch.end_time,
                sch.teaching_type,
                sch.room_info,
                sch.status,
                sch.note
            FROM schedules sch
            WHERE sch.id = ?
        ) s
        INNER JOIN classes c ON c.id = s.class_id
        LEFT JOIN users u ON u.id = s.teacher_id
        LIMIT 1
    ");
    $stmt->execute([$scheduleId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $scheduleId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($scheduleId <= 0) {
            lessonDetailResponse(422, ['status' => 'error', 'message' => 'Thiếu ID buổi học.']);
        }

        $schedule = loadScheduleContext($pdo, $scheduleId);
        if (!$schedule) {
            lessonDetailResponse(404, ['status' => 'error', 'message' => 'Không tìm thấy ca dạy này.']);
        }

        $stmtMaterials = $pdo->prepare("
            SELECT *
            FROM materials
            WHERE schedule_id = ?
            ORDER BY uploaded_at DESC, id DESC
        ");
        $stmtMaterials->execute([$scheduleId]);

        $stmtAssignments = $pdo->prepare("
            SELECT a.*, 
                   (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id) as sub_count,
                   (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id AND s.score IS NOT NULL) as graded_count
            FROM assignments a
            WHERE a.schedule_id = ?
            ORDER BY a.created_at DESC, a.id DESC
        ");
        $stmtAssignments->execute([$scheduleId]);

        // Fetch total student count for the class
        $stmtStats = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE class_id = ? AND status = 'active'");
        $stmtStats->execute([$schedule['class_id']]);
        $totalStudents = (int)$stmtStats->fetchColumn();

        lessonDetailResponse(200, [
            'status' => 'success',
            'schedule' => $schedule,
            'materials' => $stmtMaterials->fetchAll(PDO::FETCH_ASSOC),
            'assignments' => $stmtAssignments->fetchAll(PDO::FETCH_ASSOC),
            'total_students' => $totalStudents
        ]);
    }

    if ($method !== 'POST') {
        lessonDetailResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) {
        lessonDetailResponse(400, ['status' => 'error', 'message' => 'Dữ liệu gửi lên không hợp lệ.']);
    }

    $action = trim((string) ($data['action'] ?? ''));
    $scheduleId = isset($data['schedule_id']) ? (int) $data['schedule_id'] : 0;
    if ($scheduleId <= 0) {
        lessonDetailResponse(422, ['status' => 'error', 'message' => 'Thiếu schedule_id hợp lệ.']);
    }

    $schedule = loadScheduleContext($pdo, $scheduleId);
    if (!$schedule) {
        lessonDetailResponse(404, ['status' => 'error', 'message' => 'Không tìm thấy buổi học cần cập nhật.']);
    }

    if ($action === 'add_material') {
        $title = trim((string) ($data['title'] ?? ''));
        $fileUrl = trim((string) ($data['file_url'] ?? ''));
        $materialType = trim((string) ($data['material_type'] ?? 'document'));

        $checkTitle = Validator::isNotEmpty($title);
        if ($checkTitle !== true) {
            lessonDetailResponse(422, ['status' => 'error', 'message' => 'Tên tài liệu: ' . $checkTitle]);
        }

        $checkUrl = Validator::isNotEmpty($fileUrl);
        if ($checkUrl !== true) {
            lessonDetailResponse(422, ['status' => 'error', 'message' => 'Link liên kết: ' . $checkUrl]);
        }

        if (!filter_var($fileUrl, FILTER_VALIDATE_URL) && !str_starts_with($fileUrl, '/backend/uploads/')) {
            lessonDetailResponse(422, ['status' => 'error', 'message' => 'Link tài liệu không đúng định dạng hợp lệ.']);
        }

        if (!in_array($materialType, ['document', 'audio', 'video', 'link'], true)) {
            $materialType = 'document';
        }

        $stmt = $pdo->prepare("
            INSERT INTO materials (class_id, schedule_id, title, file_url, material_type)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int) $schedule['class_id'],
            $scheduleId,
            $title,
            $fileUrl,
            $materialType,
        ]);

        lessonDetailResponse(200, ['status' => 'success', 'message' => 'Tải tài liệu lên thành công.']);
    }

    if ($action === 'add_assignment') {
        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $deadline = trim((string) ($data['deadline'] ?? ''));

        $checkTitle = Validator::isNotEmpty($title);
        if ($checkTitle !== true) {
            lessonDetailResponse(422, ['status' => 'error', 'message' => 'Tiêu đề: ' . $checkTitle]);
        }

        $checkDescription = Validator::isNotEmpty($description);
        if ($checkDescription !== true) {
            lessonDetailResponse(422, ['status' => 'error', 'message' => 'Yêu cầu: ' . $checkDescription]);
        }

        $checkDeadline = Validator::isNotEmpty($deadline);
        if ($checkDeadline !== true) {
            lessonDetailResponse(422, ['status' => 'error', 'message' => 'Hạn chót: ' . $checkDeadline]);
        }

        $checkDate = Validator::checkFuturePastDate($deadline, 'future', 'Y-m-d H:i:s');
        if ($checkDate !== true) {
            lessonDetailResponse(422, ['status' => 'error', 'message' => 'Hạn chót: ' . $checkDate]);
        }

        $stmt = $pdo->prepare("
            INSERT INTO assignments (schedule_id, lesson_id, course_id, title, description, deadline, assignment_type)
            VALUES (?, ?, ?, ?, ?, ?, 'post_class')
        ");
        $stmt->execute([
            $scheduleId,
            $schedule['lesson_id'] !== null ? (int) $schedule['lesson_id'] : null,
            (int) $schedule['course_id'],
            $title,
            $description,
            $deadline,
        ]);

        lessonDetailResponse(200, ['status' => 'success', 'message' => 'Đã giao bài tập cho lớp.']);
    }

    lessonDetailResponse(422, ['status' => 'error', 'message' => 'Hành động không hợp lệ.']);
} catch (PDOException $e) {
    lessonDetailResponse(500, ['status' => 'error', 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
} catch (Throwable $e) {
    lessonDetailResponse(500, ['status' => 'error', 'message' => 'Lỗi PHP: ' . $e->getMessage()]);
}
