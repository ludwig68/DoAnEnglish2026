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
require_once __DIR__ . '/../../utils/ImageHelper.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$authUser = JwtHelper::requireAuth();

if (($authUser['role'] ?? '') === 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Tài khoản admin không dùng dashboard học viên.']);
    exit;
}

$studentId = (int) ($authUser['sub'] ?? 0);

try {
    $stmtStats = $pdo->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN e.status = 'active' THEN 1 ELSE 0 END), 0) AS active_courses,
            COALESCE(SUM(CASE WHEN e.status = 'completed' THEN 1 ELSE 0 END), 0) AS completed_courses,
            (
                SELECT COUNT(*)
                FROM submissions s
                WHERE s.student_id = :student_id
            ) AS submitted_assignments,
            (
                SELECT AVG(s.score)
                FROM submissions s
                WHERE s.student_id = :student_id_avg
                    AND s.score IS NOT NULL
            ) AS avg_score
        FROM enrollments e
        WHERE e.student_id = :student_id_enrollment
    ");
    $stmtStats->execute([
        'student_id' => $studentId,
        'student_id_avg' => $studentId,
        'student_id_enrollment' => $studentId,
    ]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: [];

    $stmtCourses = $pdo->prepare("
        SELECT
            c.id,
            c.title,
            c.level,
            c.image_url,
            cl.start_date,
            cl.end_date,
            cl.class_name,
            e.status
        FROM enrollments e
        INNER JOIN classes cl ON cl.id = e.class_id
        INNER JOIN courses c ON c.id = cl.course_id
        WHERE e.student_id = ?
        ORDER BY e.enrollment_date DESC, e.id DESC
        LIMIT 5
    ");
    $stmtCourses->execute([$studentId]);
    $enrolledCourses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

    $stmtProgress = $pdo->prepare("
        SELECT
            COUNT(*) AS completed_lessons,
            COALESCE(AVG(up.quiz_score), 0) AS avg_quiz_score
        FROM user_progress up
        WHERE up.student_id = ?
            AND (
                up.is_flashcard_completed = 1
                OR up.quiz_score IS NOT NULL
            )
    ");
    $stmtProgress->execute([$studentId]);
    $progress = $stmtProgress->fetch(PDO::FETCH_ASSOC) ?: [];

    // LẤY LỊCH HỌC SẮP TỚI (Dữ liệu thật)
    $stmtSchedules = $pdo->prepare("
        SELECT 
            s.id,
            s.study_date,
            s.start_time,
            s.end_time,
            s.teaching_type,
            s.room_info,
            c.class_name,
            co.title as course_title,
            u.full_name as teacher_name,
            cd.detail_name as lesson_title
        FROM schedules s
        INNER JOIN classes c ON c.id = s.class_id
        INNER JOIN courses co ON co.id = c.course_id
        INNER JOIN enrollments e ON e.class_id = c.id
        LEFT JOIN class_details cd ON cd.id = s.class_detail_id
        LEFT JOIN users u ON u.id = s.teacher_id
        WHERE e.student_id = ? 
          AND s.study_date >= CURRENT_DATE
        ORDER BY s.study_date ASC, s.start_time ASC
        LIMIT 3
    ");
    $stmtSchedules->execute([$studentId]);
    $schedules = $stmtSchedules->fetchAll(PDO::FETCH_ASSOC);

    // LẤY BẢNG XẾP HẠNG (Leaderboard - Dữ liệu thật)
    $stmtLeaderboard = $pdo->query("
        SELECT 
            u.id, 
            u.full_name, 
            u.email,
            COALESCE(SUM(up.quiz_score), 0) as total_points
        FROM users u
        INNER JOIN user_progress up ON up.student_id = u.id
        WHERE u.role = 'student'
        GROUP BY u.id, u.full_name, u.email
        ORDER BY total_points DESC
        LIMIT 5
    ");
    $leaderboard = $stmtLeaderboard->fetchAll(PDO::FETCH_ASSOC);

    $payload = [
        'user' => [
            'id' => $studentId,
            'full_name' => $authUser['full_name'] ?? 'Học viên',
            'email' => $authUser['email'] ?? '',
            'role' => $authUser['role'] ?? 'student',
        ],
        'stats' => [
            'activeCourses' => (int) ($stats['active_courses'] ?? 0),
            'completedCourses' => (int) ($stats['completed_courses'] ?? 0),
            'submittedAssignments' => (int) ($stats['submitted_assignments'] ?? 0),
            'avgScore' => round((float) ($stats['avg_score'] ?? 0), 1),
            'completedLessons' => (int) ($progress['completed_lessons'] ?? 0),
            'avgQuizScore' => round((float) ($progress['avg_quiz_score'] ?? 0), 1),
        ],
        'enrolledCourses' => array_map(function ($course) use ($pdo, $studentId) {
            // Tính completion_percent: số quiz đã submitted / tổng số quiz trong khóa
            $completionPercent = 0;
            try {
                $stmtTotal = $pdo->prepare("
                    SELECT COUNT(q.id) as total
                    FROM quizzes q
                    INNER JOIN lessons l ON l.id = q.lesson_id
                    WHERE l.course_id = ?
                ");
                $stmtTotal->execute([(int)$course['id']]);
                $totalQuizzes = (int) ($stmtTotal->fetchColumn() ?: 0);

                if ($totalQuizzes > 0) {
                    $stmtDone = $pdo->prepare("
                        SELECT COUNT(DISTINCT qs.quiz_id) as done
                        FROM quiz_submissions qs
                        INNER JOIN quizzes q ON q.id = qs.quiz_id
                        INNER JOIN lessons l ON l.id = q.lesson_id
                        WHERE l.course_id = ? AND qs.student_id = ? AND qs.score IS NOT NULL
                    ");
                    $stmtDone->execute([(int)$course['id'], $studentId]);
                    $doneCount = (int) ($stmtDone->fetchColumn() ?: 0);
                    $completionPercent = round(($doneCount / $totalQuizzes) * 100);
                }
            } catch (PDOException $e) {
                // quiz_submissions table may not exist yet
                $completionPercent = 0;
            }

            return [
                'id' => (int) $course['id'],
                'title' => $course['title'],
                'level' => $course['level'],
                'image_url' => resolveImageUrl($course['image_url']),
                'class_name' => $course['class_name'],
                'start_date' => $course['start_date'],
                'end_date' => $course['end_date'],
                'status' => $course['status'],
                'completion_percent' => $completionPercent,
            ];
        }, $enrolledCourses),
        'upcomingSchedules' => array_map(function ($sch) {
            return [
                'id' => (int) $sch['id'],
                'study_date' => $sch['study_date'],
                'start_time' => substr($sch['start_time'], 0, 5),
                'end_time' => substr($sch['end_time'], 0, 5),
                'teaching_type' => $sch['teaching_type'],
                'room_info' => $sch['room_info'],
                'class_name' => $sch['class_name'],
                'course_title' => $sch['course_title'],
                'teacher_name' => $sch['teacher_name'] ?? 'Giảng viên',
            ];
        }, $schedules),
        'leaderboard' => array_map(function ($entry) {
            return [
                'id' => (int) $entry['id'],
                'full_name' => $entry['full_name'],
                'total_points' => (int) $entry['total_points'],
                'is_current' => false, // Will be set in frontend
            ];
        }, $leaderboard),
    ];

    echo json_encode([
        'status' => 'success',
        'data' => $payload,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi Database: ' . $e->getMessage(),
    ]);
}
?>
