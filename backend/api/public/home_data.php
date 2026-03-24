<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/db.php';

$defaultContent = [
    'home_banner_tagline' => 'Năm học 2026',
    'home_banner_title' => 'Chinh phục tiếng Anh theo cách của bạn',
    'home_banner_description' => 'Hệ thống học tập thông minh cung cấp lộ trình cá nhân hóa, flashcard sinh động và bài tập tương tác giúp bạn ghi nhớ tốt hơn mỗi ngày.',
    'home_banner_image_url' => 'https://placehold.co/800x600/e2e8f0/64748b?text=English+Learning',
    'home_banner_primary_button_text' => 'Bắt đầu miễn phí',
    'home_banner_primary_button_link' => '/register',
    'home_intro_title' => 'Môi trường học tập lý tưởng dành cho mọi cấp độ',
    'home_intro_content' => '<p>Dù bạn là người mới bắt đầu hay đang cần tăng tốc cho kỳ thi TOEIC, IELTS, hệ thống vẫn có lộ trình phù hợp.</p><p>Bạn có thể học theo khóa học, theo bài học, và theo dõi tiến độ ngay trên cùng một nền tảng.</p>',
    'home_intro_image_url' => 'https://placehold.co/800x450/f1f5f9/94a3b8?text=Learning+Environment',
];

try {
    $stmtContent = $pdo->query("
        SELECT section_key, content_value
        FROM website_contents
        WHERE section_key IN (
            'home_banner_tagline',
            'home_banner_title',
            'home_banner_description',
            'home_banner_image_url',
            'home_banner_primary_button_text',
            'home_banner_primary_button_link',
            'home_intro_title',
            'home_intro_content',
            'home_intro_image_url'
        )
    ");
    $contentRows = $stmtContent->fetchAll(PDO::FETCH_ASSOC);

    $contentMap = $defaultContent;
    foreach ($contentRows as $row) {
        $contentMap[$row['section_key']] = $row['content_value'];
    }

    $totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $totalCourses = (int) $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();

    $stmtFeatured = $pdo->query("
        SELECT
            c.id,
            c.title,
            c.description,
            c.image_url,
            c.level,
            c.fee,
            COALESCE(student_stats.students_count, 0) AS students_count
        FROM courses c
        LEFT JOIN (
            SELECT
                cl.course_id,
                COUNT(e.id) AS students_count
            FROM classes cl
            LEFT JOIN enrollments e
                ON e.class_id = cl.id
                AND e.status IN ('active', 'completed')
            GROUP BY cl.course_id
        ) AS student_stats ON student_stats.course_id = c.id
        ORDER BY c.is_featured DESC, c.created_at DESC, c.id DESC
        LIMIT 3
    ");
    $featuredCourses = $stmtFeatured->fetchAll(PDO::FETCH_ASSOC);

    $featuredCourses = array_map(function ($course) {
        return [
            'id' => (int) $course['id'],
            'title' => $course['title'],
            'description' => $course['description'],
            'image_url' => $course['image_url'],
            'level' => $course['level'],
            'fee' => (float) $course['fee'],
            'students_count' => (int) $course['students_count'],
            'is_free' => ((float) $course['fee'] === 0.0),
        ];
    }, $featuredCourses);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'banner' => [
                'tagline' => $contentMap['home_banner_tagline'],
                'title' => $contentMap['home_banner_title'],
                'description' => $contentMap['home_banner_description'],
                'image_url' => $contentMap['home_banner_image_url'],
                'primary_button_text' => $contentMap['home_banner_primary_button_text'],
                'primary_button_link' => $contentMap['home_banner_primary_button_link'],
            ],
            'intro' => [
                'title' => $contentMap['home_intro_title'],
                'content' => $contentMap['home_intro_content'],
                'image_url' => $contentMap['home_intro_image_url'],
            ],
            'stats' => [
                'students' => number_format($totalStudents, 0, ',', '.') . '+',
                'courses' => number_format($totalCourses, 0, ',', '.') . '+',
            ],
            'featuredCourses' => $featuredCourses,
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi Database: ' . $e->getMessage()
    ]);
}
?>
