<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

$authUser = JwtHelper::requireAuth();
if (($authUser['role'] ?? '') !== 'instructor') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}
$teacherId = (int)($authUser['sub'] ?? 0);

// ── GET: Danh sách lớp + Stats tổng quan ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['class_id'])) {
    try {
        // 1. Stats tổng quan
        $stmtTotal = $pdo->prepare("
            SELECT COUNT(DISTINCT e.student_id) AS total_students
            FROM classes c
            JOIN enrollments e ON e.class_id = c.id AND e.status = 'active'
            WHERE c.instructor_id = ?
        ");
        $stmtTotal->execute([$teacherId]);
        $totalStudents = (int)($stmtTotal->fetchColumn() ?? 0);

        $stmtPending = $pdo->prepare("
            SELECT COUNT(*) FROM submissions s
            JOIN assignments a ON a.id = s.assignment_id
            JOIN courses co ON co.id = a.course_id
            JOIN classes c ON c.course_id = co.id
            WHERE c.instructor_id = ? AND s.score IS NULL
        ");
        $stmtPending->execute([$teacherId]);
        $pendingGrading = (int)($stmtPending->fetchColumn() ?? 0);

        // 2. Danh sách lớp học
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
                c.id, c.class_name, c.start_date, c.end_date,
                CASE
                    WHEN c.start_date > CURRENT_DATE THEN 'upcoming'
                    WHEN c.end_date < CURRENT_DATE THEN 'completed'
                    ELSE 'active'
                END AS status,
                co.title AS course_title, co.level AS course_level,
                cat.name AS category_name,
                COUNT(DISTINCT e.student_id) AS student_count,
                (
                    SELECT COUNT(*) FROM schedules s2
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
            WHERE $where
            GROUP BY c.id, c.class_name, c.start_date, c.end_date,
                     co.title, co.level, cat.name
            ORDER BY c.id DESC
        ");
        $stmtClasses->execute($params);
        $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        // Tính avg attendance tổng
        $avgAttendance = count($classes) > 0
            ? round(array_sum(array_column($classes, 'avg_attendance_pct')) / count($classes), 1)
            : 0;

        $formatted = array_map(function($c) {
            return [
                'id'              => $c['id'],
                'class_name'      => $c['class_name'],
                'course_title'    => $c['course_title'],
                'course_level'    => $c['course_level'],
                'category_name'   => $c['category_name'],
                'status'          => $c['status'],
                'start_date'      => $c['start_date'],
                'end_date'        => $c['end_date'],
                'student_count'   => (int)$c['student_count'],
                'attendance_pct'  => (int)$c['avg_attendance_pct'],
                'total_past'      => (int)$c['total_past_schedules'],
            ];
        }, $classes);

        echo json_encode([
            'status' => 'success',
            'stats'  => [
                'total_students'   => $totalStudents,
                'avg_attendance'   => $avgAttendance,
                'pending_grading'  => $pendingGrading,
            ],
            'data' => $formatted
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// ── GET: Chi tiết 1 lớp (students, schedules) ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['class_id'])) {
    $classId = (int)$_GET['class_id'];
    try {
        // Kiểm tra quyền
        $stmtCheck = $pdo->prepare("SELECT id, class_name FROM classes WHERE id = ? AND instructor_id = ?");
        $stmtCheck->execute([$classId, $teacherId]);
        $class = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if (!$class) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền truy cập lớp này.']);
            exit;
        }

        // Danh sách học viên + tỉ lệ điểm danh + điểm trung bình
        $stmtStudents = $pdo->prepare("
            SELECT
                u.id, u.full_name, u.email,
                e.status AS enrollment_status,
                COALESCE(
                    ROUND(
                        SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) * 100.0 /
                        NULLIF(COUNT(ar.id), 0)
                    ), 0
                ) AS attendance_pct,
                COALESCE(
                    ROUND(
                        AVG(sub.score), 1
                    ), NULL
                ) AS avg_grade
            FROM enrollments e
            JOIN users u ON u.id = e.student_id
            LEFT JOIN attendance_records ar ON ar.student_id = u.id
                AND ar.schedule_id IN (SELECT id FROM schedules WHERE class_id = ?)
            LEFT JOIN submissions sub ON sub.student_id = u.id
                AND sub.assignment_id IN (
                    SELECT a.id FROM assignments a
                    JOIN courses co ON co.id = a.course_id
                    JOIN classes cl ON cl.course_id = co.id
                    WHERE cl.id = ?
                )
            WHERE e.class_id = ? AND e.status = 'active'
            GROUP BY u.id, u.full_name, u.email, e.status
            ORDER BY u.full_name ASC
        ");
        $stmtStudents->execute([$classId, $classId, $classId]);
        $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

        // Sắp xếp lịch học gần nhất
        $stmtSchedules = $pdo->prepare("
            SELECT s.id, s.study_date, s.start_time, s.end_time, s.status, s.teaching_type, s.room_info
            FROM schedules s
            WHERE s.class_id = ?
            ORDER BY s.study_date DESC
            LIMIT 10
        ");
        $stmtSchedules->execute([$classId]);
        $schedules = $stmtSchedules->fetchAll(PDO::FETCH_ASSOC);

        $formattedStudents = array_map(function($s) {
            $pct = (int)$s['attendance_pct'];
            $status = $pct >= 80 ? 'active' : ($pct >= 60 ? 'warning' : 'at-risk');
            return [
                'id'          => $s['id'],
                'full_name'   => $s['full_name'],
                'email'       => $s['email'],
                'attendance_pct' => $pct,
                'avg_grade'   => $s['avg_grade'],
                'status'      => $status,
            ];
        }, $students);

        echo json_encode([
            'status'    => 'success',
            'class_name' => $class['class_name'],
            'students'  => $formattedStudents,
            'schedules' => array_map(function($s) {
                return [
                    'id'           => $s['id'],
                    'study_date'   => $s['study_date'],
                    'start_time'   => substr($s['start_time'], 0, 5),
                    'end_time'     => substr($s['end_time'], 0, 5),
                    'status'       => $s['status'],
                    'teaching_type' => $s['teaching_type'],
                    'room_info'    => $s['room_info'],
                ];
            }, $schedules)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
