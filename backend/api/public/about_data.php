<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/db.php';

$defaultValues = [
    [
        'icon' => 'fa-layer-group',
        'title' => 'Lộ trình cá nhân hóa',
        'description' => 'Từ mất gốc đến nâng cao, mỗi khóa học được chia nhỏ thành các chặng rõ ràng để bạn theo dõi tiến độ dễ dàng.',
        'icon_background' => 'bg-emerald-50',
        'icon_color' => 'text-[#16a34a]',
    ],
    [
        'icon' => 'fa-clone',
        'title' => 'Flashcard thông minh',
        'description' => 'Học từ vựng kèm phát âm, ví dụ và hình ảnh trực quan giúp việc ghi nhớ tự nhiên hơn.',
        'icon_background' => 'bg-blue-50',
        'icon_color' => 'text-blue-600',
    ],
    [
        'icon' => 'fa-stopwatch',
        'title' => 'Thực hành thực chiến',
        'description' => 'Bài tập đa dạng, phản hồi nhanh và theo dõi kết quả ngay sau mỗi lần luyện tập.',
        'icon_background' => 'bg-amber-50',
        'icon_color' => 'text-amber-600',
    ],
];

$defaultTeam = [
    [
        'id' => 1,
        'name' => 'Nguyễn Văn A',
        'role' => 'Project Founder & Lead Developer',
        'avatar' => 'https://placehold.co/400x400/f1f5f9/94a3b8?text=Admin',
    ],
    [
        'id' => 2,
        'name' => 'Trần Thị B',
        'role' => 'Academic Director',
        'avatar' => 'https://placehold.co/400x400/f1f5f9/94a3b8?text=Teacher',
    ],
    [
        'id' => 3,
        'name' => 'Lê Văn C',
        'role' => 'UI/UX Designer',
        'avatar' => 'https://placehold.co/400x400/f1f5f9/94a3b8?text=Design',
    ],
    [
        'id' => 4,
        'name' => 'Phạm Thị D',
        'role' => 'Content Creator',
        'avatar' => 'https://placehold.co/400x400/f1f5f9/94a3b8?text=Content',
    ],
];

$defaultContent = [
    'about_hero_tagline' => 'Câu chuyện của chúng tôi',
    'about_hero_title' => 'Định hình lại cách bạn học Tiếng Anh',
    'about_hero_highlight' => 'học Tiếng Anh',
    'about_hero_description' => 'Chúng tôi tin rằng việc làm chủ một ngôn ngữ không nên là gánh nặng tài chính hay những giờ học thuộc lòng nhàm chán.',
    'about_story_image_url' => 'https://placehold.co/800x600/f1f5f9/94a3b8?text=Our+Mission',
    'about_story_title' => 'Xóa bỏ rào cản ngôn ngữ cho người Việt',
    'about_story_content' => '<p>English Learning được xây dựng để giúp người học tiếp cận tiếng Anh theo cách dễ hiểu, dễ theo dõi và bám sát nhu cầu thực tế.</p><p>Hệ thống kết hợp nội dung khóa học, lộ trình và các công cụ luyện tập trên cùng một nền tảng.</p>',
    'about_values_title' => 'Điều gì làm chúng tôi khác biệt?',
    'about_values_description' => 'Hệ thống được thiết kế để tập trung vào hiệu quả học tập thực tế thay vì chỉ hiển thị nội dung.',
    'about_team_title' => 'Những người đứng sau dự án',
    'about_cta_title' => 'Sẵn sàng để thay đổi trình độ của bạn?',
    'about_cta_description' => 'Tạo tài khoản miễn phí ngay hôm nay và bắt đầu học cùng hệ thống.',
    'about_cta_button_text' => 'Đăng ký học ngay',
    'about_cta_button_link' => '/register',
];

try {
    $stmtContent = $pdo->query("
        SELECT section_key, content_value
        FROM website_contents
        WHERE section_key IN (
            'about_hero_tagline',
            'about_hero_title',
            'about_hero_highlight',
            'about_hero_description',
            'about_story_image_url',
            'about_story_title',
            'about_story_content',
            'about_values_title',
            'about_values_description',
            'about_values_items',
            'about_team_title',
            'about_team_members',
            'about_cta_title',
            'about_cta_description',
            'about_cta_button_text',
            'about_cta_button_link'
        )
    ");
    $contentRows = $stmtContent->fetchAll(PDO::FETCH_ASSOC);

    $contentMap = $defaultContent;
    foreach ($contentRows as $row) {
        $contentMap[$row['section_key']] = $row['content_value'];
    }

    $totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $totalCourses = (int) $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();

    $values = $defaultValues;
    if (!empty($contentMap['about_values_items'])) {
        $decodedValues = json_decode($contentMap['about_values_items'], true);
        if (is_array($decodedValues) && count($decodedValues) > 0) {
            $values = $decodedValues;
        }
    }

    $team = $defaultTeam;
    if (!empty($contentMap['about_team_members'])) {
        $decodedTeam = json_decode($contentMap['about_team_members'], true);
        if (is_array($decodedTeam) && count($decodedTeam) > 0) {
            $team = $decodedTeam;
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'hero' => [
                'tagline' => $contentMap['about_hero_tagline'],
                'title' => $contentMap['about_hero_title'],
                'highlight' => $contentMap['about_hero_highlight'],
                'description' => $contentMap['about_hero_description'],
            ],
            'story' => [
                'image_url' => $contentMap['about_story_image_url'],
                'title' => $contentMap['about_story_title'],
                'content' => $contentMap['about_story_content'],
                'year' => '2026',
            ],
            'stats' => [
                'students' => number_format($totalStudents, 0, ',', '.') . '+',
                'courses' => number_format($totalCourses, 0, ',', '.') . '+',
            ],
            'values' => $values,
            'team_title' => $contentMap['about_team_title'],
            'team' => $team,
            'values_title' => $contentMap['about_values_title'],
            'values_description' => $contentMap['about_values_description'],
            'cta' => [
                'title' => $contentMap['about_cta_title'],
                'description' => $contentMap['about_cta_description'],
                'button_text' => $contentMap['about_cta_button_text'],
                'button_link' => $contentMap['about_cta_button_link'],
            ],
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
