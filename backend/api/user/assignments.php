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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$authUser = JwtHelper::requireAuth();
if (($authUser['role'] ?? '') !== 'student') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Chỉ học viên mới có quyền truy cập trang này.']);
    exit;
}

$studentId = (int) ($authUser['sub'] ?? 0);

try {
    $stmtCourses = $pdo->prepare("
        SELECT
            c.id AS course_id,
            c.title AS course_title,
            c.level,
            cl.id AS class_id,
            e.class_detail_id,
            cl.class_name,
            cd.detail_name AS shift_name,
            e.status AS enrollment_status
        FROM enrollments e
        INNER JOIN classes cl ON cl.id = e.class_id
        INNER JOIN courses c ON c.id = cl.course_id
        LEFT JOIN class_details cd ON cd.id = e.class_detail_id
        WHERE e.student_id = ?
        ORDER BY e.enrollment_date DESC
    ");
    $stmtCourses->execute([$studentId]);
    $enrolledCourses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

    $result = [];

    foreach ($enrolledCourses as $course) {
        $courseId = (int) $course['course_id'];
        $classId = (int) $course['class_id'];
        $classDetailId = $course['class_detail_id'] ? (int) $course['class_detail_id'] : null;

        $stmtSchedules = $pdo->prepare("
            SELECT study_date
            FROM schedules
            WHERE class_id = ?
              AND (class_detail_id = ? OR class_detail_id IS NULL)
            ORDER BY study_date ASC
        ");
        $stmtSchedules->execute([$classId, $classDetailId]);
        $scheduleDates = $stmtSchedules->fetchAll(PDO::FETCH_COLUMN);

        $stmtLessons = $pdo->prepare("
            SELECT id, title, order_number
            FROM lessons
            WHERE course_id = ?
            ORDER BY order_number ASC, id ASC
        ");
        $stmtLessons->execute([$courseId]);
        $lessons = $stmtLessons->fetchAll(PDO::FETCH_ASSOC);

        $lessonsData = [];
        $previousCompleted = true;

        foreach ($lessons as $lesson) {
            $lessonId = (int) $lesson['id'];
            $orderNumber = (int) $lesson['order_number'];

            if (!empty($scheduleDates)) {
                if (isset($scheduleDates[$orderNumber])) {
                    $calculatedDeadline = $scheduleDates[$orderNumber] . ' 00:00:00';
                } else {
                    $lastDate = end($scheduleDates);
                    $calculatedDeadline = date('Y-m-d', strtotime($lastDate . ' +2 days')) . ' 00:00:00';
                }
            } else {
                $calculatedDeadline = date('Y-m-d', strtotime('+7 days')) . ' 00:00:00';
            }

            $stmtQuizzes = $pdo->prepare("
                SELECT
                    q.id,
                    q.title,
                    q.description,
                    q.category,
                    q.total_points,
                    q.created_at,
                    COUNT(qq.id) AS question_count,
                    MIN(qq.question_type) AS question_type
                FROM quizzes q
                LEFT JOIN questions qq ON qq.quiz_id = q.id
                WHERE q.lesson_id = ?
                GROUP BY q.id, q.title, q.description, q.category, q.total_points, q.created_at
                ORDER BY q.id ASC
            ");
            $stmtQuizzes->execute([$lessonId]);
            $assignments = $stmtQuizzes->fetchAll(PDO::FETCH_ASSOC);

            $assignmentCount = count($assignments);
            $completedCount = 0;
            $assignmentsData = [];

            foreach ($assignments as $assignment) {
                $assignmentId = (int) $assignment['id'];
                $status = 'pending';
                $score = null;

                $stmtSubmission = $pdo->prepare("
                    SELECT score, status, submitted_at
                    FROM quiz_submissions
                    WHERE quiz_id = ? AND student_id = ?
                    ORDER BY (class_id = ?) DESC, submitted_at DESC
                    LIMIT 1
                ");
                $stmtSubmission->execute([$assignmentId, $studentId, $classId]);
                $submission = $stmtSubmission->fetch(PDO::FETCH_ASSOC);

                if ($submission) {
                    $score = $submission['score'] !== null ? (float) $submission['score'] : null;
                    $status = $submission['status'] ?? 'completed';
                    if ($status === 'completed' || $status === 'pending_grading') {
                        $completedCount++;
                    }
                }

                $typeMap = [
                    'grammar' => 'pre_class',
                    'vocabulary' => 'pre_class',
                    'reading' => 'post_class',
                    'listening' => 'post_class',
                    'speaking' => 'post_class',
                    'writing' => 'post_class',
                    'summary' => 'general',
                ];

                $assignmentsData[] = [
                    'id' => $assignmentId,
                    'title' => $assignment['title'],
                    'description' => $assignment['description'] ?? '',
                    'type' => $typeMap[$assignment['category']] ?? 'general',
                    'category' => $assignment['category'],
                    'total_points' => (int) $assignment['total_points'],
                    'question_count' => (int) $assignment['question_count'],
                    'question_type' => $assignment['question_type'] ?? 'multiple_choice',
                    'deadline' => $calculatedDeadline,
                    'status' => $status,
                    'score' => $score,
                ];
            }

            $stmtWrittenAssignments = $pdo->prepare("
                SELECT id, title, description, deadline, assignment_type
                FROM assignments
                WHERE lesson_id = ?
                ORDER BY id ASC
            ");
            $stmtWrittenAssignments->execute([$lessonId]);
            $writtenAssignments = $stmtWrittenAssignments->fetchAll(PDO::FETCH_ASSOC);

            foreach ($writtenAssignments as $writtenAssignment) {
                $assignmentId = (int) $writtenAssignment['id'];
                $assignmentCount++;

                $status = 'in_progress';
                $score = null;

                $stmtWrittenSubmission = $pdo->prepare("
                    SELECT score, submitted_at
                    FROM submissions
                    WHERE assignment_id = ? AND student_id = ? AND class_id = ?
                    ORDER BY submitted_at DESC
                    LIMIT 1
                ");
                $stmtWrittenSubmission->execute([$assignmentId, $studentId, $classId]);
                $submission = $stmtWrittenSubmission->fetch(PDO::FETCH_ASSOC);

                if ($submission) {
                    $score = $submission['score'] !== null ? (float) $submission['score'] : null;
                    $status = $score !== null ? 'completed' : 'pending_grading';
                    $completedCount++;
                }

                $assignmentsData[] = [
                    'id' => 'wa_' . $assignmentId,
                    'title' => $writtenAssignment['title'],
                    'description' => $writtenAssignment['description'] ?? '',
                    'type' => $writtenAssignment['assignment_type'] ?? 'post_class',
                    'category' => 'writing',
                    'total_points' => 10,
                    'question_count' => 1,
                    'question_type' => 'essay',
                    'deadline' => $writtenAssignment['deadline'] ?: $calculatedDeadline,
                    'status' => $status,
                    'score' => $score,
                ];
            }

            $completionPercent = $assignmentCount > 0
                ? round(($completedCount / $assignmentCount) * 100)
                : 0;

            $locked = !$previousCompleted;
            $isActive = !$locked && $completionPercent > 0 && $completionPercent < 100;

            $icons = [
                'fa-solid fa-book-open',
                'fa-solid fa-pen-nib',
                'fa-solid fa-feather-pointed',
                'fa-solid fa-users',
                'fa-solid fa-headphones',
                'fa-solid fa-globe',
            ];
            $iconIndex = ($orderNumber - 1) % count($icons);

            $lessonsData[] = [
                'id' => $lessonId,
                'title' => $lesson['title'],
                'icon' => $icons[$iconIndex] ?? 'fa-solid fa-book-open',
                'assignmentCount' => $assignmentCount,
                'completionPercent' => $completionPercent,
                'isActive' => $isActive,
                'locked' => $locked,
                'assignments' => $assignmentsData,
            ];

            $previousCompleted = $completionPercent >= 100;
        }

        $result[] = [
            'course_id' => $courseId,
            'course_title' => $course['course_title'],
            'level' => $course['level'],
            'class_name' => $course['class_name'],
            'lessons' => $lessonsData,
        ];
    }

    $stmtLeaderboard = $pdo->query("
        SELECT
            u.id,
            u.full_name,
            u.email,
            COALESCE(SUM(up.quiz_score), 0) AS total_points
        FROM users u
        INNER JOIN user_progress up ON up.student_id = u.id
        WHERE u.role = 'student'
        GROUP BY u.id, u.full_name, u.email
        ORDER BY total_points DESC
        LIMIT 5
    ");
    $leaderboard = $stmtLeaderboard->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'courses' => $result,
            'leaderboard' => array_map(static function (array $entry): array {
                return [
                    'id' => (int) $entry['id'],
                    'full_name' => $entry['full_name'],
                    'total_points' => (int) $entry['total_points'],
                ];
            }, $leaderboard),
        ],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi Database: ' . $e->getMessage(),
    ]);
}
