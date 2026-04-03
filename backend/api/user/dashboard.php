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
        'enrolledCourses' => array_map(function ($course) {
            return [
                'id' => (int) $course['id'],
                'title' => $course['title'],
                'level' => $course['level'],
                'image_url' => resolveImageUrl($course['image_url']),
                'class_name' => $course['class_name'],
                'start_date' => $course['start_date'],
                'end_date' => $course['end_date'],
                'status' => $course['status'],
            ];
        }, $enrolledCourses),
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
