<?php
/**
 * backend/api/teacher/attendance.php
 * GET  -> Lấy thông tin buổi học và danh sách điểm danh.
 * POST -> Lưu hoặc cập nhật dữ liệu điểm danh.
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

$authUser = JwtHelper::requireAuth();
if (($authUser['role'] ?? '') !== 'instructor') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

$teacherId = (int) ($authUser['sub'] ?? 0);

function attendanceResponse(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function loadTeacherScheduleContext(PDO $pdo, int $scheduleId, int $teacherId): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            s.id,
            s.class_id,
            s.study_date,
            s.start_time,
            s.end_time,
            s.room_info,
            s.teaching_type,
            s.note AS session_note,
            s.attendance_checked,
            s.status,
            c.class_name,
            co.title AS course_title,
            l.title AS lesson_title,
            COALESCE(u_sub.full_name, u_main.full_name) AS teacher_name
        FROM schedules s
        INNER JOIN classes c ON s.class_id = c.id
        INNER JOIN courses co ON c.course_id = co.id
        LEFT JOIN lessons l ON s.lesson_id = l.id
        LEFT JOIN users u_sub ON s.teacher_id = u_sub.id
        LEFT JOIN users u_main ON c.instructor_id = u_main.id
        WHERE s.id = ?
          AND (c.instructor_id = ? OR s.teacher_id = ?)
        LIMIT 1
    ");
    $stmt->execute([$scheduleId, $teacherId, $teacherId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function loadAllowedAttendanceStudents(PDO $pdo, int $classId, int $scheduleId): array
{
    $stmtEnroll = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, 'regular' AS student_type
        FROM enrollments e
        INNER JOIN users u ON e.student_id = u.id
        WHERE e.class_id = ? AND e.status = 'active'
    ");
    $stmtEnroll->execute([$classId]);
    $regularStudents = $stmtEnroll->fetchAll(PDO::FETCH_ASSOC);

    $stmtMakeup = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, 'makeup' AS student_type
        FROM makeup_registrations mr
        INNER JOIN users u ON mr.student_id = u.id
        WHERE mr.target_schedule_id = ?
          AND mr.status IN ('registered', 'approved', 'attended')
          AND u.id NOT IN (
              SELECT student_id
              FROM enrollments
              WHERE class_id = ? AND status = 'active'
          )
    ");
    $stmtMakeup->execute([$scheduleId, $classId]);
    $makeupStudents = $stmtMakeup->fetchAll(PDO::FETCH_ASSOC);

    $allStudents = array_merge($regularStudents, $makeupStudents);

    return [
        'regular' => $regularStudents,
        'makeup' => $makeupStudents,
        'all' => $allStudents,
        'allowed_ids' => array_map('intval', array_column($allStudents, 'id')),
        'regular_ids' => array_map('intval', array_column($regularStudents, 'id')),
    ];
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['list_schedules'])) {
        $dateFilter = $_GET['date'] ?? date('Y-m-d');

        $stmtList = $pdo->prepare("
            SELECT
                s.id,
                s.study_date,
                SUBSTRING(s.start_time, 1, 5) AS start_time,
                SUBSTRING(s.end_time, 1, 5) AS end_time,
                s.room_info,
                s.teaching_type,
                s.attendance_checked,
                c.id AS class_id,
                c.class_name,
                co.title AS course_title,
                cat.name AS category_name,
                l.title AS lesson_title,
                (
                    SELECT COUNT(*)
                    FROM enrollments e
                    WHERE e.class_id = c.id AND e.status = 'active'
                ) AS student_count
            FROM schedules s
            INNER JOIN classes c ON s.class_id = c.id
            INNER JOIN courses co ON c.course_id = co.id
            LEFT JOIN categories cat ON cat.id = co.category_id
            LEFT JOIN lessons l ON s.lesson_id = l.id
            WHERE (c.instructor_id = ? OR s.teacher_id = ?)
              AND s.study_date = ?
              AND s.status <> 'canceled'
            ORDER BY s.start_time ASC
        ");
        $stmtList->execute([$teacherId, $teacherId, $dateFilter]);
        $schedules = $stmtList->fetchAll(PDO::FETCH_ASSOC);

        attendanceResponse(200, [
            'status' => 'success',
            'data' => array_map(static function (array $row): array {
                return [
                    'id' => (int) $row['id'],
                    'study_date' => $row['study_date'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'room_info' => $row['room_info'],
                    'teaching_type' => $row['teaching_type'],
                    'attendance_checked' => (bool) $row['attendance_checked'],
                    'class_id' => (int) $row['class_id'],
                    'class_name' => $row['class_name'],
                    'course_title' => $row['course_title'],
                    'category_name' => $row['category_name'],
                    'lesson_title' => $row['lesson_title'],
                    'student_count' => (int) $row['student_count'],
                ];
            }, $schedules),
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $scheduleId = isset($_GET['schedule_id']) ? (int) $_GET['schedule_id'] : 0;
        if ($scheduleId <= 0) {
            $stmtSuggest = $pdo->prepare("
                SELECT s.id
                FROM schedules s
                INNER JOIN classes c ON s.class_id = c.id
                WHERE (c.instructor_id = ? OR s.teacher_id = ?)
                  AND s.study_date >= CURRENT_DATE
                ORDER BY s.study_date ASC, s.start_time ASC
                LIMIT 1
            ");
            $stmtSuggest->execute([$teacherId, $teacherId]);
            $scheduleId = (int) $stmtSuggest->fetchColumn();

            if ($scheduleId <= 0) {
                attendanceResponse(404, ['status' => 'error', 'message' => 'Không tìm thấy ca học nào để điểm danh.']);
            }
        }

        $session = loadTeacherScheduleContext($pdo, $scheduleId, $teacherId);
        if (!$session) {
            attendanceResponse(404, ['status' => 'error', 'message' => 'Không tìm thấy buổi học hoặc bạn không có quyền.']);
        }

        $studentsInfo = loadAllowedAttendanceStudents($pdo, (int) $session['class_id'], $scheduleId);
        $allStudents = $studentsInfo['all'];

        $stmtAtt = $pdo->prepare("
            SELECT student_id, status, note
            FROM attendance_records
            WHERE schedule_id = ?
        ");
        $stmtAtt->execute([$scheduleId]);
        $records = $stmtAtt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        $stmtLeave = $pdo->prepare("
            SELECT student_id, reason
            FROM leave_requests
            WHERE class_id = ?
              AND status = 'approved'
              AND ? BETWEEN start_date AND end_date
        ");
        $stmtLeave->execute([(int) $session['class_id'], $session['study_date']]);
        $leaveRequests = $stmtLeave->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        $formattedStudents = [];
        foreach ($allStudents as $student) {
            $studentId = (int) $student['id'];

            $stmtStats = $pdo->prepare("
                SELECT
                    COUNT(*) AS total_past,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) AS present_count
                FROM schedules sch
                LEFT JOIN attendance_records ar
                    ON ar.schedule_id = sch.id
                   AND ar.student_id = ?
                WHERE sch.class_id = ?
                  AND sch.study_date < CURRENT_DATE
                  AND sch.status <> 'canceled'
            ");
            $stmtStats->execute([$studentId, (int) $session['class_id']]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: ['total_past' => 0, 'present_count' => 0];

            $totalPast = (int) $stats['total_past'];
            $presentCount = (int) $stats['present_count'];
            $attendancePct = $totalPast > 0 ? round(($presentCount / $totalPast) * 100) : 100;

            $currentStatus = $records[$studentId]['status'] ?? 'unmarked';
            $currentNote = $records[$studentId]['note'] ?? '';

            if ($currentStatus === 'unmarked' && isset($leaveRequests[$studentId])) {
                $currentStatus = 'excused';
                if ($currentNote === '') {
                    $currentNote = 'Đã xin phép: ' . $leaveRequests[$studentId]['reason'];
                }
            }

            $formattedStudents[] = [
                'id' => $studentId,
                'full_name' => $student['full_name'],
                'email' => $student['email'],
                'student_type' => $student['student_type'],
                'attendance_pct' => $attendancePct,
                'current_status' => $currentStatus,
                'current_note' => $currentNote,
            ];
        }

        attendanceResponse(200, [
            'status' => 'success',
            'data' => [
                'session' => $session,
                'students' => $formattedStudents,
            ],
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        attendanceResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        attendanceResponse(400, ['status' => 'error', 'message' => 'Dữ liệu gửi lên không hợp lệ.']);
    }

    $scheduleId = isset($input['schedule_id']) ? (int) $input['schedule_id'] : 0;
    $records = is_array($input['records'] ?? null) ? $input['records'] : [];
    $sessionNote = isset($input['session_note']) ? trim((string) $input['session_note']) : null;

    if ($scheduleId <= 0) {
        attendanceResponse(422, ['status' => 'error', 'message' => 'Thiếu ID buổi học.']);
    }

    $session = loadTeacherScheduleContext($pdo, $scheduleId, $teacherId);
    if (!$session) {
        attendanceResponse(403, ['status' => 'error', 'message' => 'Bạn không có quyền cập nhật điểm danh cho buổi học này.']);
    }
    if (($session['status'] ?? '') === 'canceled') {
        attendanceResponse(422, ['status' => 'error', 'message' => 'Không thể điểm danh cho buổi học đã bị hủy.']);
    }

    $studentsInfo = loadAllowedAttendanceStudents($pdo, (int) $session['class_id'], $scheduleId);
    $allowedIds = array_flip($studentsInfo['allowed_ids']);
    $regularIds = array_flip($studentsInfo['regular_ids']);
    $invalidStudentIds = [];
    $normalizedRecords = [];

    foreach ($records as $record) {
        $studentId = isset($record['student_id']) ? (int) $record['student_id'] : 0;
        $status = trim((string) ($record['status'] ?? ''));
        $note = trim((string) ($record['note'] ?? ''));

        if ($studentId <= 0 || !in_array($status, ['present', 'absent', 'late', 'excused'], true)) {
            continue;
        }

        if (!isset($allowedIds[$studentId])) {
            $invalidStudentIds[] = $studentId;
            continue;
        }

        $normalizedRecords[$studentId] = [
            'student_id' => $studentId,
            'status' => $status,
            'note' => $note,
        ];
    }

    if (!empty($invalidStudentIds)) {
        attendanceResponse(422, [
            'status' => 'error',
            'message' => 'Danh sách điểm danh chứa học viên không thuộc buổi học này.',
            'invalid_student_ids' => array_values(array_unique($invalidStudentIds)),
        ]);
    }

    $pdo->beginTransaction();
    try {
        $stmtUpdateSchedule = $pdo->prepare("
            UPDATE schedules
            SET note = ?, attendance_checked = 1, status = 'completed'
            WHERE id = ?
        ");
        $stmtUpdateSchedule->execute([$sessionNote, $scheduleId]);

        $stmtUpsert = $pdo->prepare("
            INSERT INTO attendance_records (schedule_id, student_id, status, note, created_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                note = VALUES(note)
        ");

        $stmtCountAbsence = $pdo->prepare("
            SELECT COUNT(*)
            FROM attendance_records ar
            INNER JOIN schedules s ON ar.schedule_id = s.id
            WHERE ar.student_id = ?
              AND s.class_id = ?
              AND ar.status = 'absent'
        ");

        $stmtCheckWarn = $pdo->prepare("
            SELECT id
            FROM academic_warnings
            WHERE student_id = ? AND class_id = ? AND trigger_count = ?
            LIMIT 1
        ");

        $stmtInsertWarn = $pdo->prepare("
            INSERT INTO academic_warnings (student_id, class_id, schedule_id, trigger_count)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($normalizedRecords as $record) {
            $studentId = $record['student_id'];
            $status = $record['status'];
            $note = $record['note'];

            $stmtUpsert->execute([$scheduleId, $studentId, $status, $note]);

            if ($status === 'absent' && isset($regularIds[$studentId])) {
                $stmtCountAbsence->execute([$studentId, (int) $session['class_id']]);
                $absenceCount = (int) $stmtCountAbsence->fetchColumn();

                if ($absenceCount >= 3) {
                    $stmtCheckWarn->execute([$studentId, (int) $session['class_id'], $absenceCount]);
                    if (!$stmtCheckWarn->fetch()) {
                        $stmtInsertWarn->execute([$studentId, (int) $session['class_id'], $scheduleId, $absenceCount]);
                    }
                }
            }
        }

        $pdo->commit();
        attendanceResponse(200, ['status' => 'success', 'message' => 'Điểm danh đã được lưu thành công.']);
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
} catch (Throwable $e) {
    attendanceResponse(500, ['status' => 'error', 'message' => $e->getMessage()]);
}
