<?php
/**
 * backend/api/user/leave_requests.php
 * GET  → Lấy danh sách đơn nghỉ + lớp enrolled + lịch học từng lớp + thống kê
 * POST → Tạo đơn xin nghỉ (theo schedule_id cụ thể)
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

$authUser = JwtHelper::requireAuth();

if (($authUser['role'] ?? '') === 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Tài khoản admin không dùng trang này.']);
    exit;
}

$studentId = (int) ($authUser['sub'] ?? 0);

// ═══════════════════════════════════════
// GET: Lấy danh sách đơn + lớp enrolled + schedules + thống kê
// ═══════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // 1. Danh sách đơn nghỉ
        $stmt = $pdo->prepare("
            SELECT 
                lr.id, lr.class_id, lr.reason, lr.start_date, lr.end_date,
                lr.status, lr.admin_note, lr.created_at,
                c.class_name, co.title as course_title, co.level,
                s_start.start_time as session_start_time,
                s_start.end_time as session_end_time
            FROM leave_requests lr
            LEFT JOIN classes c ON c.id = lr.class_id
            LEFT JOIN courses co ON co.id = c.course_id
            LEFT JOIN schedules s_start ON s_start.class_id = lr.class_id AND s_start.study_date = lr.start_date
            WHERE lr.student_id = ?
            ORDER BY lr.created_at DESC
        ");
        $stmt->execute([$studentId]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Danh sách lớp enrolled + lịch học sắp tới
        $stmtClasses = $pdo->prepare("
            SELECT 
                cl.id as class_id,
                cl.class_name,
                co.title as course_title,
                co.level
            FROM enrollments e
            INNER JOIN classes cl ON cl.id = e.class_id
            INNER JOIN courses co ON co.id = cl.course_id
            WHERE e.student_id = ? AND e.status = 'active'
            ORDER BY e.enrollment_date DESC
        ");
        $stmtClasses->execute([$studentId]);
        $enrolledClasses = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

        // 3. Lấy lịch học sắp tới cho TẤT CẢ lớp đã enrolled
        $classIds = array_column($enrolledClasses, 'class_id');
        $schedulesMap = []; // class_id -> [schedules]

        if (!empty($classIds)) {
            $placeholders = implode(',', array_fill(0, count($classIds), '?'));
            $stmtSchedules = $pdo->prepare("
                SELECT 
                    s.id, s.class_id, s.study_date, s.start_time, s.end_time,
                    s.teaching_type, s.room_info,
                    cd.detail_name as lesson_title,
                    u.full_name as teacher_name
                FROM schedules s
                LEFT JOIN class_details cd ON cd.id = s.class_detail_id
                LEFT JOIN users u ON u.id = s.teacher_id
                WHERE s.class_id IN ($placeholders)
                  AND s.study_date >= CURRENT_DATE
                ORDER BY s.study_date ASC, s.start_time ASC
            ");
            $stmtSchedules->execute($classIds);

            foreach ($stmtSchedules->fetchAll(PDO::FETCH_ASSOC) as $sch) {
                $cid = (int) $sch['class_id'];

                // Kiểm tra đã có đơn nghỉ pending/approved cho buổi này chưa
                $stmtCheck = $pdo->prepare("
                    SELECT COUNT(*) FROM leave_requests
                    WHERE student_id = ? AND class_id = ? AND start_date = ? AND status IN ('pending','approved')
                ");
                $stmtCheck->execute([$studentId, $cid, $sch['study_date']]);
                $alreadyRequested = (int) $stmtCheck->fetchColumn() > 0;

                $schedulesMap[$cid][] = [
                    'id'            => (int) $sch['id'],
                    'study_date'    => $sch['study_date'],
                    'start_time'    => substr($sch['start_time'], 0, 5),
                    'end_time'      => substr($sch['end_time'], 0, 5),
                    'teaching_type' => $sch['teaching_type'],
                    'room_info'     => $sch['room_info'],
                    'lesson_title'  => $sch['lesson_title'],
                    'teacher_name'  => $sch['teacher_name'],
                    'already_requested' => $alreadyRequested,
                ];
            }
        }

        // 4. Thống kê chuyên cần 
        $totalSchedules = 0;
        $totalLeaves = 0;
        try {
            if (!empty($classIds)) {
                $placeholders = implode(',', array_fill(0, count($classIds), '?'));
                $stmtTotal = $pdo->prepare("
                    SELECT COUNT(*) FROM schedules 
                    WHERE class_id IN ($placeholders) AND study_date <= CURRENT_DATE
                ");
                $stmtTotal->execute($classIds);
                $totalSchedules = (int) $stmtTotal->fetchColumn();
            }

            $stmtLeaves = $pdo->prepare("
                SELECT COUNT(*) FROM leave_requests
                WHERE student_id = ? AND status = 'approved'
            ");
            $stmtLeaves->execute([$studentId]);
            $totalLeaves = (int) $stmtLeaves->fetchColumn();
        } catch (PDOException $e) {}

        $attendanceRate = $totalSchedules > 0
            ? round((($totalSchedules - $totalLeaves) / $totalSchedules) * 100)
            : 100;

        echo json_encode([
            'status' => 'success',
            'data' => [
                'requests' => array_map(function ($r) {
                    return [
                        'id'           => (int) $r['id'],
                        'class_id'     => (int) $r['class_id'],
                        'class_name'   => $r['class_name'],
                        'course_title' => $r['course_title'],
                        'level'        => $r['level'],
                        'reason'       => $r['reason'],
                        'start_date'   => $r['start_date'],
                        'end_date'     => $r['end_date'],
                        'status'       => $r['status'],
                        'admin_note'   => $r['admin_note'],
                        'created_at'   => $r['created_at'],
                        'session_time' => ($r['session_start_time'] ? substr($r['session_start_time'], 0, 5) . ' – ' . substr($r['session_end_time'], 0, 5) : ''),
                    ];
                }, $requests),
                'enrolled_classes' => array_map(function ($c) use ($schedulesMap) {
                    $cid = (int) $c['class_id'];
                    return [
                        'class_id'     => $cid,
                        'class_name'   => $c['class_name'],
                        'course_title' => $c['course_title'],
                        'level'        => $c['level'],
                        'label'        => $c['course_title'] . ' - ' . $c['class_name'] . ' (' . ($c['level'] ?: 'N/A') . ')',
                        'schedules'    => $schedulesMap[$cid] ?? [],
                    ];
                }, $enrolledClasses),
                'stats' => [
                    'total_schedules'  => $totalSchedules,
                    'total_leaves'     => $totalLeaves,
                    'attendance_rate'  => $attendanceRate,
                ],
            ],
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}

// ═══════════════════════════════════════
// POST: Tạo đơn xin nghỉ (theo buổi học cụ thể)
// ═══════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON body']);
        exit;
    }

    $classId   = isset($input['class_id']) ? (int) $input['class_id'] : 0;
    $reason    = trim($input['reason'] ?? '');
    $studyDate = trim($input['study_date'] ?? ''); // Ngày buổi học cụ thể

    // Validate
    $errors = [];
    if (!$classId) $errors[] = 'Vui lòng chọn khóa học.';
    if (!$reason)  $errors[] = 'Vui lòng nhập lý do nghỉ.';
    if (!$studyDate) $errors[] = 'Vui lòng chọn buổi học.';

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
        exit;
    }

    // Kiểm tra đã có đơn nghỉ cho buổi này chưa
    try {
        $stmtCheck = $pdo->prepare("
            SELECT COUNT(*) FROM leave_requests
            WHERE student_id = ? AND class_id = ? AND start_date = ? AND status IN ('pending','approved')
        ");
        $stmtCheck->execute([$studentId, $classId, $studyDate]);
        if ((int) $stmtCheck->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Bạn đã gửi đơn nghỉ cho buổi học này rồi.']);
            exit;
        }
    } catch (PDOException $e) {}

    try {
        $stmt = $pdo->prepare("
            INSERT INTO leave_requests (student_id, class_id, reason, start_date, end_date, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$studentId, $classId, $reason, $studyDate, $studyDate]);

        echo json_encode([
            'status'  => 'success',
            'message' => 'Đơn xin nghỉ đã được gửi thành công! Đơn sẽ được xử lý trong vòng 24 giờ.',
            'id'      => (int) $pdo->lastInsertId(),
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi gửi đơn: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
?>
