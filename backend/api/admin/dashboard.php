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

JwtHelper::requireAuth('admin');

function fetchScalar(PDO $pdo, string $sql, array $params = [])
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $value = $stmt->fetchColumn();
    return $value === false ? 0 : $value;
}

function buildPercent($part, $total): int
{
    $safeTotal = max(1, (int) $total);
    return (int) round(((int) $part / $safeTotal) * 100);
}

try {
    $counts = [
        'users_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM users"),
        'students_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM users WHERE role = 'student'"),
        'instructors_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM users WHERE role = 'instructor'"),
        'admins_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM users WHERE role = 'admin'"),
        'courses_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM courses"),
        'featured_courses_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM courses WHERE is_featured = 1"),
        'categories_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM categories"),
        'learning_paths_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM learning_paths"),
        'consultations_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM consultations"),
        'consultations_pending' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM consultations WHERE status = 'pending'"),
        'contacts_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM contacts"),
        'contacts_unreplied' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM contacts WHERE is_replied = 0"),
        'website_contents_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM website_contents"),
        'assignments_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM quizzes"),
        'classes_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM classes"),
        'active_classes_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM classes WHERE CURDATE() BETWEEN start_date AND end_date"),
        'enrollments_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM enrollments"),
        'active_enrollments_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM enrollments WHERE status = 'active'"),
        'completed_enrollments_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM enrollments WHERE status = 'completed'"),
        'submissions_total' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM submissions"),
        'leave_requests_pending' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'"),
        'makeup_registrations_pending' => (int) fetchScalar($pdo, "SELECT COUNT(*) FROM makeup_registrations WHERE status = 'registered'"),
    ];

    $latestContentDate = fetchScalar($pdo, "SELECT MAX(updated_at) FROM website_contents");

    $stmtRecentUsers = $pdo->query("
        SELECT full_name, email, role, created_at
        FROM users
        ORDER BY created_at DESC, id DESC
        LIMIT 5
    ");
    $recentUsers = $stmtRecentUsers->fetchAll(PDO::FETCH_ASSOC);

    $stmtRecentConsultations = $pdo->query("
        SELECT full_name, phone, email, status, created_at
        FROM consultations
        ORDER BY created_at DESC, id DESC
        LIMIT 5
    ");
    $recentConsultations = $stmtRecentConsultations->fetchAll(PDO::FETCH_ASSOC);

    $stmtRecentContacts = $pdo->query("
        SELECT full_name, email, is_replied, created_at
        FROM contacts
        ORDER BY created_at DESC, id DESC
        LIMIT 5
    ");
    $recentContacts = $stmtRecentContacts->fetchAll(PDO::FETCH_ASSOC);

    $pendingTasks = $counts['consultations_pending'] + 
                    $counts['contacts_unreplied'] + 
                    $counts['leave_requests_pending'] + 
                    $counts['makeup_registrations_pending'];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'generated_at' => date('c'),
            'summary' => [
                'students_total' => $counts['students_total'],
                'courses_total' => $counts['courses_total'],
                'instructors_total' => $counts['instructors_total'],
                'pending_tasks_total' => $pendingTasks,
                'featured_courses_total' => $counts['featured_courses_total'],
                'latest_content_date' => $latestContentDate ?: null,
                'assignments_total' => $counts['assignments_total'],
                'submissions_total' => $counts['submissions_total'],
                'leave_requests_pending' => $counts['leave_requests_pending'],
                'makeup_registrations_pending' => $counts['makeup_registrations_pending'],
            ],
            'modules' => [
                [
                    'key' => 'users',
                    'title' => 'Quản lý tài khoản',
                    'description' => 'Theo dõi học viên, giảng viên và quản trị viên trong hệ thống.',
                    'count' => $counts['users_total'],
                    'unit' => 'tài khoản',
                    'accent' => 'emerald',
                    'to' => '/admin/users',
                ],
                [
                    'key' => 'courses',
                    'title' => 'Quản lý khóa học',
                    'description' => 'Quản lý toàn bộ khóa học đang có trên hệ thống.',
                    'count' => $counts['courses_total'],
                    'unit' => 'khóa học',
                    'accent' => 'sky',
                    'to' => '/admin/courses',
                ],
                [
                    'key' => 'categories',
                    'title' => 'Quản lý danh mục khóa học',
                    'description' => 'Phân loại nội dung học tập theo danh mục.',
                    'count' => $counts['categories_total'],
                    'unit' => 'danh mục',
                    'accent' => 'violet',
                ],
                [
                    'key' => 'consultations',
                    'title' => 'Quản lý đăng ký tư vấn',
                    'description' => 'Theo dõi người dùng để lại yêu cầu tư vấn.',
                    'count' => $counts['consultations_pending'],
                    'unit' => 'chờ xử lý',
                    'accent' => 'orange',
                ],
                [
                    'key' => 'contacts',
                    'title' => 'Quản lý liên hệ',
                    'description' => 'Theo dõi phản hồi và yêu cầu hỗ trợ từ website.',
                    'count' => $counts['contacts_unreplied'],
                    'unit' => 'chưa phản hồi',
                    'accent' => 'rose',
                ],
                [
                    'key' => 'website_contents',
                    'title' => 'Quản lý nội dung trang web',
                    'description' => 'Quản lý các khối nội dung đang hiển thị trên website.',
                    'count' => $counts['website_contents_total'],
                    'unit' => 'nội dung',
                    'accent' => 'cyan',
                ],
                [
                    'key' => 'learning_paths',
                    'title' => 'Quản lý lộ trình học',
                    'description' => 'Theo dõi số lộ trình học đang có trong hệ thống.',
                    'count' => $counts['learning_paths_total'],
                    'unit' => 'lộ trình',
                    'accent' => 'lime',
                ],
                [
                    'key' => 'statistics',
                    'title' => 'Quản lý thống kê',
                    'description' => 'Tổng số lượt ghi danh và nộp bài dùng cho theo dõi số liệu.',
                    'count' => $counts['enrollments_total'] + $counts['submissions_total'],
                    'unit' => 'bản ghi',
                    'accent' => 'indigo',
                ],
                [
                    'key' => 'assignments',
                    'title' => 'Quản lý bài tập',
                    'description' => 'Theo dõi số lượng bài tập (quiz) đang có trên hệ thống.',
                    'count' => $counts['assignments_total'],
                    'unit' => 'bài tập',
                    'accent' => 'fuchsia',
                    'to' => '/admin/quiz-builder',
                ],
                [
                    'key' => 'instructors',
                    'title' => 'Quản lý giảng viên',
                    'description' => 'Theo dõi đội ngũ giảng viên đang có.',
                    'count' => $counts['instructors_total'],
                    'unit' => 'giảng viên',
                    'accent' => 'teal',
                ],
            ],
            'priorities' => [
                [
                    'title' => 'Đăng ký tư vấn cần xử lý',
                    'count' => $counts['consultations_pending'],
                    'description' => 'Số yêu cầu tư vấn đang ở trạng thái chờ xử lý.',
                    'level' => $counts['consultations_pending'] > 0 ? 'Ưu tiên' : 'Ổn định',
                ],
                [
                    'title' => 'Tin nhắn liên hệ chưa phản hồi',
                    'count' => $counts['contacts_unreplied'],
                    'description' => 'Số tin nhắn liên hệ chưa được phản hồi.',
                    'level' => $counts['contacts_unreplied'] > 0 ? 'Ưu tiên' : 'Ổn định',
                ],
                [
                    'title' => 'Lớp học đang diễn ra',
                    'count' => $counts['active_classes_total'],
                    'description' => 'Số lớp đang trong thời gian học.',
                    'level' => 'Theo dõi',
                ],
            ],
            'kpis' => [
                [
                    'label' => 'Tỷ lệ xử lý tư vấn',
                    'value' => buildPercent(
                        $counts['consultations_total'] - $counts['consultations_pending'],
                        $counts['consultations_total']
                    ),
                ],
                [
                    'label' => 'Tỷ lệ phản hồi liên hệ',
                    'value' => buildPercent(
                        $counts['contacts_total'] - $counts['contacts_unreplied'],
                        $counts['contacts_total']
                    ),
                ],
                [
                    'label' => 'Tỷ lệ hoàn thành ghi danh',
                    'value' => buildPercent(
                        $counts['completed_enrollments_total'],
                        $counts['enrollments_total']
                    ),
                ],
                [
                    'label' => 'Tỷ lệ lớp đang hoạt động',
                    'value' => buildPercent(
                        $counts['active_classes_total'],
                        $counts['classes_total']
                    ),
                ],
            ],
            'recent' => [
                'users' => $recentUsers,
                'consultations' => $recentConsultations,
                'contacts' => $recentContacts,
            ],
        ],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi Database: ' . $e->getMessage(),
    ]);
}
