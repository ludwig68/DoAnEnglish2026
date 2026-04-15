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

if (($authUser['role'] ?? '') === 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Tài khoản admin không dùng trang này.']);
    exit;
}

$studentId = (int) ($authUser['sub'] ?? 0);

// Đảm bảo bảng quiz_submissions tồn tại
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quiz_submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            quiz_id INT NOT NULL,
            score DECIMAL(5,2) DEFAULT NULL,
            status VARCHAR(50) DEFAULT 'completed',
            answers_json LONGTEXT,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_submission (student_id, quiz_id),
            INDEX idx_student (student_id),
            INDEX idx_quiz (quiz_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    // ok, continue
}

try {
    // 1. Lấy danh sách khóa học mà học viên đã enrolled
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

        // 1.1 Lấy lịch học theo đúng ca học (class_detail_id) để tính deadline
        // Lưu ý: Nếu ca học chưa được set lịch cụ thể, ta không nên lấy bừa lịch của ca khác.
        $stmtSched = $pdo->prepare("SELECT study_date FROM schedules WHERE class_id = ? AND (class_detail_id = ? OR class_detail_id IS NULL) ORDER BY study_date ASC");
        $stmtSched->execute([$classId, $classDetailId]);
        $scheduleDates = $stmtSched->fetchAll(PDO::FETCH_COLUMN);

        // 2. Lấy danh sách lessons của khóa học
        $stmtLessons = $pdo->prepare("
            SELECT
                l.id,
                l.title,
                l.order_number
            FROM lessons l
            WHERE l.course_id = ?
            ORDER BY l.order_number ASC, l.id ASC
        ");
        $stmtLessons->execute([$courseId]);
        $lessons = $stmtLessons->fetchAll(PDO::FETCH_ASSOC);

        $lessonsData = [];
        $previousCompleted = true; // Lesson đầu tiên luôn mở

        foreach ($lessons as $lesson) {
            $lessonId = (int) $lesson['id'];
            $orderNum = (int) $lesson['order_number'];

            // Tính deadline: 00h00 trước ngày học kế tiếp
            // Giả sử Lesson N được dạy vào buổi học thứ N (index N-1). 
            // Vậy ngày học kế tiếp là buổi học thứ N+1 (index N trong mảng scheduleDates)
            $calculatedDeadline = null;
            if (!empty($scheduleDates)) {
                if (isset($scheduleDates[$orderNum])) {
                    // Có buổi học kế tiếp
                    $calculatedDeadline = $scheduleDates[$orderNum] . ' 00:00:00';
                } else {
                    // Là buổi học cuối hoặc vượt quá lịch, lấy buổi cuối + 2 ngày
                    $lastDate = end($scheduleDates);
                    $calculatedDeadline = date('Y-m-d', strtotime($lastDate . ' +2 days')) . ' 00:00:00';
                }
            } else {
                // Mặc định nếu không có bất kỳ lịch nào (mock fallback)
                $calculatedDeadline = date('Y-m-d', strtotime('+7 days')) . ' 00:00:00';
            }

            // 3. Lấy quizzes (bài tập từ QuizBuilder) thuộc lesson này
            $stmtAssignments = $pdo->prepare("
                SELECT
                    q.id,
                    q.title,
                    q.description,
                    q.category,
                    q.total_points,
                    q.created_at,
                    COUNT(qs.id) AS question_count,
                    MIN(qs.question_type) AS question_type
                FROM quizzes q
                LEFT JOIN questions qs ON qs.quiz_id = q.id
                WHERE q.lesson_id = ?
                GROUP BY q.id, q.title, q.description, q.category, q.total_points, q.created_at
                ORDER BY q.id ASC
            ");
            $stmtAssignments->execute([$lessonId]);
            $assignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);

            $assignmentCount = count($assignments);
            $completedCount = 0;
            $assignmentsData = [];

            foreach ($assignments as $assignment) {
                $assignmentId = (int) $assignment['id'];

                // 4. Kiểm tra submission của học viên cho quiz này
                $status = 'pending';
                $score = null;
                try {
                    $stmtSub = $pdo->prepare("
                        SELECT s.id, s.score, s.status, s.submitted_at
                        FROM quiz_submissions s
                        WHERE s.quiz_id = ? AND s.student_id = ?
                        ORDER BY s.submitted_at DESC
                        LIMIT 1
                    ");
                    $stmtSub->execute([$assignmentId, $studentId]);
                    $submission = $stmtSub->fetch(PDO::FETCH_ASSOC);

                    if ($submission) {
                        $score = $submission['score'] !== null ? (float) $submission['score'] : null;
                        $status = $submission['status'] ?? 'completed';
                        if ($status === 'completed' || $status === 'pending_grading') $completedCount++;
                    }
                } catch (PDOException $e) {
                    // Bảng quiz_submissions chưa tồn tại → mặc định pending
                }

                // Map category -> type label
                $typeMap = [
                    'grammar'    => 'pre_class',
                    'vocabulary' => 'pre_class',
                    'reading'    => 'post_class',
                    'listening'  => 'post_class',
                    'speaking'   => 'post_class',
                    'writing'    => 'post_class',
                    'summary'    => 'general',
                ];

                $assignmentsData[] = [
                    'id'           => $assignmentId,
                    'title'        => $assignment['title'],
                    'description'  => $assignment['description'] ?? '',
                    'type'         => $typeMap[$assignment['category']] ?? 'general',
                    'category'     => $assignment['category'],
                    'total_points' => (int) $assignment['total_points'],
                    'question_count' => (int) $assignment['question_count'],
                    'question_type' => $assignment['question_type'] ?? 'multiple_choice',
                    'deadline'     => $calculatedDeadline,
                    'status'       => $status,
                    'score'        => $score,
                ];
            }

            // Tính % hoàn thành
            $completionPercent = $assignmentCount > 0
                ? round(($completedCount / $assignmentCount) * 100)
                : 0;

            // Kiểm tra user_progress
            $stmtProgress = $pdo->prepare("
                SELECT id FROM user_progress
                WHERE student_id = ? AND lesson_id = ?
                LIMIT 1
            ");
            $stmtProgress->execute([$studentId, $lessonId]);
            $hasProgress = (bool) $stmtProgress->fetch();

            // Xác định trạng thái khóa/mở
            $locked = !$previousCompleted;
            $isActive = !$locked && $completionPercent > 0 && $completionPercent < 100;

            // Icon theo thứ tự
            $icons = [
                'fa-solid fa-book-open',
                'fa-solid fa-pen-nib',
                'fa-solid fa-feather-pointed',
                'fa-solid fa-users',
                'fa-solid fa-headphones',
                'fa-solid fa-globe',
            ];
            $iconIdx = ((int) $lesson['order_number'] - 1) % count($icons);

            $lessonsData[] = [
                'id'                => $lessonId,
                'title'             => $lesson['title'],
                'icon'              => $icons[$iconIdx] ?? 'fa-solid fa-book-open',
                'assignmentCount'   => $assignmentCount,
                'completionPercent' => $completionPercent,
                'isActive'          => $isActive,
                'locked'            => $locked,
                'assignments'       => $assignmentsData,
            ];

            // Cập nhật cho lesson kế tiếp
            $previousCompleted = $completionPercent >= 100;
        }

        $result[] = [
            'course_id'    => $courseId,
            'course_title' => $course['course_title'],
            'level'        => $course['level'],
            'class_name'   => $course['class_name'],
            'lessons'      => $lessonsData,
        ];
    }

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

    echo json_encode([
        'status' => 'success',
        'data'   => [
            'courses' => $result,
            'leaderboard' => array_map(function ($entry) {
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
        'status'  => 'error',
        'message' => 'Lỗi Database: ' . $e->getMessage(),
    ]);
}
?>
