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

if (($authUser['role'] ?? '') !== 'instructor') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Chỉ giáo viên mới có quyền truy cập trang này.']);
    exit;
}

$teacherId = (int) ($authUser['sub'] ?? 0);

try {
    // 1. LẤY THỐNG KÊ (Stats)
    $stmtStats = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM classes WHERE (instructor_id = :tid1) AND (end_date >= CURRENT_DATE)) as active_classes,
            (SELECT COUNT(DISTINCT student_id) FROM enrollments e INNER JOIN classes c ON c.id = e.class_id WHERE c.instructor_id = :tid2) as total_students,
            (SELECT COUNT(*) FROM submissions s 
             INNER JOIN classes c ON c.id = s.class_id 
             WHERE c.instructor_id = :tid3 AND s.score IS NULL) as pending_grades
    ");
    $stmtStats->execute(['tid1' => $teacherId, 'tid2' => $teacherId, 'tid3' => $teacherId]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

    // 2. LẤY LỚP HỌC HÔM NAY (Today Classes)
    $stmtToday = $pdo->prepare("
        SELECT 
            s.study_date, s.start_time, s.end_time, s.room_info, s.teaching_type,
            c.class_name, co.title as course_title,
            (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id) as student_count
        FROM schedules s
        INNER JOIN classes c ON c.id = s.class_id
        INNER JOIN courses co ON co.id = c.course_id
        WHERE (c.instructor_id = ? OR s.teacher_id = ?) 
          AND s.study_date = CURRENT_DATE
        ORDER BY s.start_time ASC
    ");
    $stmtToday->execute([$teacherId, $teacherId]);
    $todayClasses = $stmtToday->fetchAll(PDO::FETCH_ASSOC);

    // 3. TIẾN ĐỘ CÁC LỚP (Class Progress)
    $stmtClassesList = $pdo->prepare("
        SELECT 
            c.id, c.class_name, co.title as course_title, co.level
        FROM classes c
        INNER JOIN courses co ON co.id = c.course_id
        WHERE c.instructor_id = ?
        ORDER BY c.start_date DESC
        LIMIT 5
    ");
    $stmtClassesList->execute([$teacherId]);
    $classesList = $stmtClassesList->fetchAll(PDO::FETCH_ASSOC);

    $classProgress = array_map(function($cls) use ($pdo) {
        $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE class_id = ?");
        $stmtTotal->execute([$cls['id']]);
        $totalSessions = (int)$stmtTotal->fetchColumn();

        $stmtDone = $pdo->prepare("
            SELECT COUNT(*) FROM schedules 
            WHERE class_id = ? 
              AND (study_date < CURRENT_DATE OR (study_date = CURRENT_DATE AND end_time <= CURRENT_TIME))
        ");
        $stmtDone->execute([$cls['id']]);
        $doneSessions = (int)$stmtDone->fetchColumn();
        
        $progressPercent = ($totalSessions > 0) ? round(($doneSessions / $totalSessions) * 100) : 0;

        return [
            'name' => $cls['class_name'] . " (" . $cls['course_title'] . ")",
            'progress' => $progressPercent,
            'info1' => "Đã dạy: $doneSessions/$totalSessions buổi",
            'info2' => "Cấp độ: " . $cls['level'],
            'colorClass' => $progressPercent > 70 ? 'bg-emerald-500' : ($progressPercent > 30 ? 'bg-blue-500' : 'bg-amber-400')
        ];
    }, $classesList);

    $payload = [
        'stats' => [
            'activeClassesCount' => (int)($stats['active_classes'] ?? 0),
            'totalStudentsCount' => (int)($stats['total_students'] ?? 0),
            'pendingGradesCount' => (int)($stats['pending_grades'] ?? 0)
        ],
        'todayClasses' => array_map(function($c) {
            $startTime = strtotime($c['start_time']);
            $endTime = strtotime($c['end_time']);
            $currentTime = time();
            
            return [
              'time' => substr($c['start_time'], 0, 5) . " - " . substr($c['end_time'], 0, 5),
              'class_name' => $c['class_name'],
              'course_title' => $c['course_title'],
              'room' => $c['room_info'] ?: 'Phòng học',
              'student_count' => (int)$c['student_count'],
              'type' => $c['teaching_type'],
              'status' => ($currentTime >= $startTime && $currentTime <= $endTime) ? 'ongoing' : 'upcoming'
            ];
        }, $todayClasses),
        'classProgress' => $classProgress
    ];

    echo json_encode([
        'status' => 'success',
        'data' => $payload
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Lỗi DB: ' . $e->getMessage()]);
}
