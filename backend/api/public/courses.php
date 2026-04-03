<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/ImageHelper.php';
try {
    $stmtCat = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

    array_unshift($categories, ['id' => 0, 'name' => 'Tất cả khóa học']);

    $stmtCourse = $pdo->query("
        SELECT
            c.id,
            c.category_id,
            c.category_id AS categoryId,
            c.title,
            c.title AS name,
            c.description,
            c.image_url,
            c.level,
            c.fee,
            COALESCE(student_stats.students_count, 0) AS students_count,
            COALESCE(lesson_stats.lesson_count, 0) AS lesson_count
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
        LEFT JOIN (
            SELECT course_id, COUNT(id) AS lesson_count
            FROM lessons
            GROUP BY course_id
        ) AS lesson_stats ON lesson_stats.course_id = c.id
        ORDER BY c.created_at DESC, c.id DESC
    ");
    $courses = $stmtCourse->fetchAll(PDO::FETCH_ASSOC);

    foreach ($courses as &$course) {
        $course['id'] = (int) $course['id'];
        $course['category_id'] = (int) $course['category_id'];
        $course['categoryId'] = (int) $course['categoryId'];
        $course['fee'] = (float) $course['fee'];
        $course['students_count'] = (int) $course['students_count'];
        $course['lesson_count'] = (int) $course['lesson_count'];
        $course['image_url'] = resolveImageUrl($course['image_url']);
        $course['imageUrl'] = $course['image_url'];
        $course['isFree'] = ($course['fee'] == 0);
        $course['is_free'] = $course['isFree'];
    }

    echo json_encode([
        'status' => 'success',
        'categories' => $categories,
        'courses' => $courses
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
