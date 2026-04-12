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

try {
    $authUser = JwtHelper::requireAuth();
    if (($authUser['role'] ?? '') !== 'instructor') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Quyền truy cập bị từ chối.']);
        exit;
    }

    $teacherId = (int) ($authUser['sub'] ?? 0);
    
    // Lấy ngày bắt đầu tuần
    $startDateStr = $_GET['start_date'] ?? date('Y-m-d', strtotime('monday this week'));
    $startDate = new DateTime($startDateStr);
    $endDate = (clone $startDate)->modify('+6 days');
    
    $scheduleData = [];
    $shortDays = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
    $daysOfWeek = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];

    for ($i = 0; $i < 7; $i++) {
        $currentDate = clone $startDate;
        $currentDate->modify("+$i days");
        $dateStr = $currentDate->format('Y-m-d');
        
        $stmt = $pdo->prepare("
            SELECT 
                s.id, s.start_time, s.end_time, s.room_info, s.teaching_type, s.status,
                c.class_name, co.title as course_title, cat.name as category_name
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            INNER JOIN courses co ON co.id = c.course_id
            LEFT JOIN categories cat ON cat.id = co.category_id
            WHERE (c.instructor_id = ? OR s.teacher_id = ?) 
              AND s.study_date = ?
            ORDER BY s.start_time ASC
        ");
        $stmt->execute([$teacherId, $teacherId, $dateStr]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $classes = array_map(function($r) use ($dateStr) {
            $currentTime = time();
            $startTimeStr = $dateStr . ' ' . $r['start_time'];
            $endTimeStr = $dateStr . ' ' . $r['end_time'];
            $isOngoing = ($currentTime >= strtotime($startTimeStr) && $currentTime <= strtotime($endTimeStr));

            return [
                'id' => $r['id'],
                'type' => $r['category_name'] ?: 'General',
                'title' => $r['class_name'] . " (" . $r['course_title'] . ")",
                'time' => substr($r['start_time'], 0, 5) . " - " . substr($r['end_time'], 0, 5),
                'location' => $r['room_info'] ?: ($r['teaching_type'] === 'online' ? 'Online (Zoom)' : 'Phòng học'),
                'isPrimary' => $isOngoing,
                'isOnline' => ($r['teaching_type'] === 'online'),
                'status' => $r['status']
            ];
        }, $rows);

        $scheduleData[] = [
            'day' => $shortDays[$i],
            'fullDay' => $daysOfWeek[$i],
            'date' => (int)$currentDate->format('d'),
            'isActive' => ($dateStr === date('Y-m-d')),
            'classes' => $classes
        ];
    }

    // LẤY CÁC YÊU CẦU DẠY THAY/HỌC BÙ (Mock logic hoặc query makeup_registrations)
    // Giả sử giáo viên thấy các đăng ký học bù vào lớp mình
    $stmtSubs = $pdo->prepare("
        SELECT 
            mr.id, mr.status, u.full_name as student_name,
            s.study_date, s.start_time, s.end_time, c.class_name
        FROM makeup_registrations mr
        JOIN schedules s ON mr.target_schedule_id = s.id
        JOIN classes c ON s.class_id = c.id
        JOIN users u ON mr.student_id = u.id
        WHERE (c.instructor_id = ? OR s.teacher_id = ?)
          AND mr.status = 'registered'
          AND s.study_date >= CURRENT_DATE
        LIMIT 5
    ");
    $stmtSubs->execute([$teacherId, $teacherId]);
    $substitutions = array_map(function($s) {
        return [
            'id' => $s['id'],
            'title' => "Học bù: " . $s['student_name'] . " - " . $s['class_name'],
            'info' => date('d/m', strtotime($s['study_date'])) . " • " . substr($s['start_time'], 0, 5) . " - " . substr($s['end_time'], 0, 5)
        ];
    }, $stmtSubs->fetchAll(PDO::FETCH_ASSOC));

    // LẤY LỊCH THI CỦA CÁC LỚP GIÁO VIÊN DẠY
    $stmtExams = $pdo->prepare("
        SELECT 
            q.title, q.category, a.deadline, co.title as course_title
        FROM assignments a
        JOIN courses co ON a.course_id = co.id
        JOIN classes c ON c.course_id = co.id
        LEFT JOIN quizzes q ON q.lesson_id = a.lesson_id -- Giả định quan hệ
        WHERE c.instructor_id = ? 
          AND a.deadline >= CURRENT_DATE 
          AND a.deadline <= ?
        ORDER BY a.deadline ASC
        LIMIT 3
    ");
    $stmtExams->execute([$teacherId, $endDate->format('Y-m-d')]);
    $examsList = array_map(function($e) {
        $dt = new DateTime($e['deadline']);
        return [
            'month' => $dt->format('m'),
            'day' => $dt->format('d'),
            'title' => $e['title'] ?: 'Bài kiểm tra ' . $e['category'],
            'level' => $e['course_title']
        ];
    }, $stmtExams->fetchAll(PDO::FETCH_ASSOC));

    echo json_encode([
        'status' => 'success',
        'data' => [
            'weekSchedule' => $scheduleData,
            'substitutions' => $substitutions,
            'upcomingExams' => $examsList,
            'weekLabel' => $startDate->format('d') . " – " . $endDate->format('d') . " Tháng " . $startDate->format('m') . ", " . $startDate->format('Y')
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
