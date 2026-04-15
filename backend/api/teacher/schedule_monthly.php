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
    
    // Nhận dải ngày từ frontend để tối ưu hiệu năng
    $startDate = $_GET['start_date'] ?? date('Y-m-01'); // Mặc định đầu tháng này
    $endDate = $_GET['end_date'] ?? date('Y-m-t');     // Mặc định cuối tháng này
    
    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            c.class_name,
            co.title as course_name,
            u.full_name as teacher_name
        FROM schedules s
        INNER JOIN classes c ON c.id = s.class_id
        INNER JOIN courses co ON co.id = c.course_id
        LEFT JOIN users u ON s.teacher_id = u.id
        WHERE (c.instructor_id = ? OR s.teacher_id = ?) 
          AND s.study_date BETWEEN ? AND ?
        ORDER BY s.study_date ASC, s.start_time ASC
    ");
    $stmt->execute([$teacherId, $teacherId, $startDate, $endDate]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data cho frontend
    $data = array_map(function($r) {
        $currentTime = time();
        $endTimeStr = $r['study_date'] . ' ' . $r['end_time'];
        $isPast = ($currentTime > strtotime($endTimeStr));

        return [
            'id' => $r['id'],
            'class_name' => $r['class_name'],
            'course_name' => $r['course_name'],
            'study_date' => $r['study_date'],
            'start_time' => substr($r['start_time'], 0, 5),
            'end_time' => substr($r['end_time'], 0, 5),
            'teaching_type' => $r['teaching_type'],
            'status' => $r['status'],
            'isPast' => $isPast,
            'room_info' => $r['room_info']
        ];
    }, $schedules);

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
