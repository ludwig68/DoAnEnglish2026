<?php
// ═══════════════════════════════════════════════════════════════
// API: Xem tài liệu giảng dạy (Teacher — Read Only)
// ═══════════════════════════════════════════════════════════════
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
$classId = (int)($_GET['class_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

try {
    // Lấy danh sách khóa học mà giảng viên này dạy
    $stmtCourses = $pdo->prepare("
        SELECT DISTINCT c.course_id, co.title AS course_title
        FROM classes c
        JOIN courses co ON co.id = c.course_id
        WHERE c.instructor_id = ? " . ($classId ? "AND c.id = " . $classId : "") . "
        ORDER BY co.title
    ");
    $stmtCourses->execute([$teacherId]);
    $courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

    if (empty($courses)) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit;
    }

    $courseIds = array_column($courses, 'course_id');
    $placeholders = implode(',', array_fill(0, count($courseIds), '?'));

    // Lấy tài liệu theo các khóa học mà GV dạy
    $stmtMat = $pdo->prepare("
        SELECT 
            m.id, m.course_id, m.lesson_id, m.schedule_id,
            m.title, m.file_type, m.file_url, m.file_size, m.original_name,
            m.created_at,
            co.title AS course_title,
            l.title AS lesson_title,
            s.study_date, s.start_time, s.end_time,
            cl.class_name
        FROM course_materials m
        JOIN courses co ON co.id = m.course_id
        LEFT JOIN lessons l ON l.id = m.lesson_id
        LEFT JOIN schedules s ON s.id = m.schedule_id
        LEFT JOIN classes cl ON cl.id = s.class_id
        WHERE m.course_id IN ($placeholders)
        ORDER BY co.title, l.id, m.created_at DESC
    ");
    $stmtMat->execute($courseIds);
    $materials = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

    // Nhóm theo khóa học → bài học
    $grouped = [];
    foreach ($materials as $m) {
        $cid = (int)$m['course_id'];
        if (!isset($grouped[$cid])) {
            $grouped[$cid] = [
                'course_id' => $cid,
                'course_title' => $m['course_title'],
                'lessons' => [],
                '_ungrouped' => [] // materials without lesson
            ];
        }

        $item = [
            'id'            => (int)$m['id'],
            'title'         => $m['title'],
            'file_type'     => $m['file_type'],
            'file_url'      => $m['file_url'],
            'file_size'     => (int)$m['file_size'],
            'original_name' => $m['original_name'],
            'created_at'    => $m['created_at'],
            'schedule_id'   => $m['schedule_id'] ? (int)$m['schedule_id'] : null,
            'study_date'    => $m['study_date'],
            'start_time'    => $m['start_time'] ? substr($m['start_time'], 0, 5) : null,
            'end_time'      => $m['end_time'] ? substr($m['end_time'], 0, 5) : null,
            'class_name'    => $m['class_name'],
        ];

        $lid = $m['lesson_id'] ? (int)$m['lesson_id'] : 0;
        if ($lid > 0) {
            if (!isset($grouped[$cid]['lessons'][$lid])) {
                $grouped[$cid]['lessons'][$lid] = [
                    'lesson_id' => $lid,
                    'lesson_title' => $m['lesson_title'],
                    'materials' => []
                ];
            }
            $grouped[$cid]['lessons'][$lid]['materials'][] = $item;
        } else {
            $grouped[$cid]['_ungrouped'][] = $item;
        }
    }

    // Flatten
    $result = [];
    foreach ($grouped as $course) {
        $lessons = array_values($course['lessons']);
        $result[] = [
            'course_id' => $course['course_id'],
            'course_title' => $course['course_title'],
            'lessons' => $lessons,
            'general_materials' => $course['_ungrouped'],
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $result]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
