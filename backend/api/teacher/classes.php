<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['class_id'])) {
    try {
        $stmtTotal = $pdo->prepare("
            SELECT COUNT(DISTINCT e.student_id) AS total_students
            FROM classes c
            JOIN enrollments e ON e.class_id = c.id AND e.status = 'active'
            WHERE c.instructor_id = ?
        ");
        $stmtTotal->execute([$teacherId]);
        $totalStudents = (int) ($stmtTotal->fetchColumn() ?? 0);

        $stmtPending = $pdo->prepare("
            SELECT
                (
                    SELECT COUNT(*)
                    FROM submissions s
                    JOIN classes c1 ON c1.id = s.class_id
                    WHERE c1.instructor_id = ?
                      AND s.score IS NULL
                ) + (
                    SELECT COUNT(*)
                    FROM quiz_submissions qs
                    JOIN classes c2 ON c2.id = qs.class_id
                    WHERE c2.instructor_id = ?
                      AND qs.status = 'pending_grading'
                ) AS pending_total
        ");
        $stmtPending->execute([$teacherId, $teacherId]);
        $pendingGrading = (int) ($stmtPending->fetchColumn() ?? 0);

        $statusFilter = $_GET['status'] ?? '';
        $whereClauses = ['c.instructor_id = ?'];
        $params = [$teacherId];

        if ($statusFilter === 'active') {
            $whereClauses[] = "c.start_date <= CURRENT_DATE AND c.end_date >= CURRENT_DATE";
        } elseif ($statusFilter === 'upcoming') {
            $whereClauses[] = "c.start_date > CURRENT_DATE";
        } elseif ($statusFilter === 'completed') {
            $whereClauses[] = "c.end_date < CURRENT_DATE";
        }

        $where = implode(' AND ', $whereClauses);

        $stmtClasses = $pdo->prepare("
            SELECT
                c.id,
                c.class_name,
                c.start_date,
                c.end_date,
                CASE
                    WHEN c.start_date > CURRENT_DATE THEN 'upcoming'
                    WHEN c.end_date < CURRENT_DATE THEN 'completed'
                    ELSE 'active'
                END AS status,
                co.title AS course_title,
                co.level AS course_level,
                cat.name AS category_name,
                COUNT(DISTINCT e.student_id) AS student_count,
                (
                    SELECT COUNT(*)
                    FROM schedules s2
                    WHERE s2.class_id = c.id AND s2.study_date < CURRENT_DATE
                ) AS total_past_schedules,
                COALESCE((
                    SELECT ROUND(
                        SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) * 100.0 /
                        NULLIF(COUNT(ar.id), 0)
                    )
                    FROM attendance_records ar
                    JOIN schedules s3 ON ar.schedule_id = s3.id
                    WHERE s3.class_id = c.id
                ), 0) AS avg_attendance_pct
            FROM classes c
            JOIN courses co ON co.id = c.course_id
            LEFT JOIN categories cat ON cat.id = co.category_id
            LEFT JOIN enrollments e ON e.class_id = c.id AND e.status = 'active'
            WHERE {$where}
            GROUP BY
                c.id,
                c.class_name,
                c.start_date,
                c.end_date,
                co.title,
                co.level,
                cat.name
            ORDER BY c.id DESC
        ");
        $stmtClasses->execute($params);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        $avgAttendance = count($classes) > 0
            ? round(array_sum(array_column($classes, 'avg_attendance_pct')) / count($classes), 1)
            : 0;

        $formatted = array_map(static function (array $class): array {
            return [
                'id' => (int) $class['id'],
                'class_name' => $class['class_name'],
                'course_title' => $class['course_title'],
                'course_level' => $class['course_level'],
                'category_name' => $class['category_name'],
                'status' => $class['status'],
                'start_date' => $class['start_date'],
                'end_date' => $class['end_date'],
                'student_count' => (int) $class['student_count'],
                'attendance_pct' => (int) $class['avg_attendance_pct'],
                'total_past' => (int) $class['total_past_schedules'],
            ];
        }, $classes);

        echo json_encode([
            'status' => 'success',
            'stats' => [
                'total_students' => $totalStudents,
                'avg_attendance' => $avgAttendance,
                'pending_grading' => $pendingGrading,
            ],
            'data' => $formatted,
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['class_id'])) {
    $classId = (int) $_GET['class_id'];

    try {
        $stmtCheck = $pdo->prepare("
            SELECT id, class_name
            FROM classes
            WHERE id = ? AND instructor_id = ?
            LIMIT 1
        ");
        $stmtCheck->execute([$classId, $teacherId]);
        $class = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$class) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền truy cập lớp này.']);
            exit;
        }

        $stmtStudents = $pdo->prepare("
            SELECT
                u.id,
                u.full_name,
                u.email,
                e.status AS enrollment_status,
                COALESCE(
                    ROUND(
                        SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) * 100.0 /
                        NULLIF(COUNT(ar.id), 0)
                    ), 0
                ) AS attendance_pct,
                COALESCE(ROUND(AVG(sub.score), 1), NULL) AS avg_grade
            FROM enrollments e
            JOIN users u ON u.id = e.student_id
            LEFT JOIN attendance_records ar
                ON ar.student_id = u.id
               AND ar.schedule_id IN (SELECT id FROM schedules WHERE class_id = ?)
            LEFT JOIN submissions sub
                ON sub.student_id = u.id
               AND sub.class_id = ?
            WHERE e.class_id = ? AND e.status = 'active'
            GROUP BY u.id, u.full_name, u.email, e.status
            ORDER BY u.full_name ASC
        ");
        $stmtStudents->execute([$classId, $classId, $classId]);
        $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

        $stmtSchedules = $pdo->prepare("
            SELECT id, study_date, start_time, end_time, status, teaching_type, room_info
            FROM schedules
            WHERE class_id = ?
            ORDER BY study_date DESC
            LIMIT 10
        ");
        $stmtSchedules->execute([$classId]);
        $schedules = $stmtSchedules->fetchAll(PDO::FETCH_ASSOC);

        $formattedStudents = array_map(static function (array $student): array {
            $attendancePct = (int) $student['attendance_pct'];
            $status = $attendancePct >= 80 ? 'active' : ($attendancePct >= 60 ? 'warning' : 'at-risk');

            return [
                'id' => (int) $student['id'],
                'full_name' => $student['full_name'],
                'email' => $student['email'],
                'attendance_pct' => $attendancePct,
                'avg_grade' => $student['avg_grade'] !== null ? (float) $student['avg_grade'] : null,
                'status' => $status,
            ];
        }, $students);

        echo json_encode([
            'status' => 'success',
            'class_name' => $class['class_name'],
            'students' => $formattedStudents,
            'schedules' => array_map(static function (array $schedule): array {
                return [
                    'id' => (int) $schedule['id'],
                    'study_date' => $schedule['study_date'],
                    'start_time' => substr($schedule['start_time'], 0, 5),
                    'end_time' => substr($schedule['end_time'], 0, 5),
                    'status' => $schedule['status'],
                    'teaching_type' => $schedule['teaching_type'],
                    'room_info' => $schedule['room_info'],
                ];
            }, $schedules),
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
