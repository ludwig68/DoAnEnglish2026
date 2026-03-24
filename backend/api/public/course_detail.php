<?php
// backend/api/public/course_detail.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Mã khóa học không hợp lệ!']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.category_id,
            c.title,
            c.description,
            c.image_url,
            c.level,
            c.fee,
            c.created_at,
            cat.name AS category_name,
            COALESCE(student_stats.students_count, 0) AS students_count
        FROM courses c
        LEFT JOIN categories cat ON c.category_id = cat.id
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
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy khóa học này!']);
        exit;
    }

    $stmtLessons = $pdo->prepare("
        SELECT id, title, video_url, order_number
        FROM lessons
        WHERE course_id = ?
        ORDER BY order_number ASC, id ASC
    ");
    $stmtLessons->execute([$id]);
    $lessons = $stmtLessons->fetchAll(PDO::FETCH_ASSOC);

    $course['id'] = (int) $course['id'];
    $course['category_id'] = (int) $course['category_id'];
    $course['fee'] = (float) $course['fee'];
    $course['students_count'] = (int) $course['students_count'];
    $course['lessons'] = array_map(function ($lesson) {
        return [
            'id' => (int) $lesson['id'],
            'title' => $lesson['title'],
            'video_url' => $lesson['video_url'],
            'order_number' => (int) $lesson['order_number'],
        ];
    }, $lessons);
    $course['lesson_count'] = count($course['lessons']);

    echo json_encode([
        'status' => 'success',
        'data' => $course
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Lỗi Database: ' . $e->getMessage()]);
}
?>
