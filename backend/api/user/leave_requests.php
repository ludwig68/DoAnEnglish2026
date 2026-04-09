<?php
/**
 * backend/api/user/leave_requests.php
 * GET  → Lấy danh sách đơn nghỉ học của học viên + thống kê chuyên cần
 * POST → Tạo đơn xin nghỉ mới
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
// GET: Lấy danh sách đơn + khóa học enrolled + thống kê
// ═══════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // 1. Danh sách đơn nghỉ
        $stmt = $pdo->prepare("
            SELECT 
                lr.id, lr.class_id, lr.reason, lr.start_date, lr.end_date,
                lr.status, lr.admin_note, lr.created_at,
                c.class_name, co.title as course_title, co.level
            FROM leave_requests lr
            LEFT JOIN classes c ON c.id = lr.class_id
            LEFT JOIN courses co ON co.id = c.course_id
            WHERE lr.student_id = ?
            ORDER BY lr.created_at DESC
        ");
        $stmt->execute([$studentId]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Danh sách khóa học đã enrolled (cho dropdown)
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

        // 3. Thống kê chuyên cần
        $totalSchedules = 0;
        $totalLeaves = 0;
        try {
            // Tổng số buổi học (tất cả lớp enrolled)
            $classIds = array_column($enrolledClasses, 'class_id');
            if (!empty($classIds)) {
                $placeholders = implode(',', array_fill(0, count($classIds), '?'));
                $stmtTotal = $pdo->prepare("
                    SELECT COUNT(*) FROM schedules 
                    WHERE class_id IN ($placeholders) AND study_date <= CURRENT_DATE
                ");
                $stmtTotal->execute($classIds);
                $totalSchedules = (int) $stmtTotal->fetchColumn();
            }

            // Tổng số ngày đã nghỉ (approved)
            $stmtLeaves = $pdo->prepare("
                SELECT COALESCE(SUM(DATEDIFF(end_date, start_date) + 1), 0) as total_days
                FROM leave_requests
                WHERE student_id = ? AND status = 'approved'
            ");
            $stmtLeaves->execute([$studentId]);
            $totalLeaves = (int) $stmtLeaves->fetchColumn();
        } catch (PDOException $e) {
            // Ignore stats errors
        }

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
                    ];
                }, $requests),
                'enrolled_classes' => array_map(function ($c) {
                    return [
                        'class_id'     => (int) $c['class_id'],
                        'class_name'   => $c['class_name'],
                        'course_title' => $c['course_title'],
                        'level'        => $c['level'],
                        'label'        => $c['course_title'] . ' - ' . $c['class_name'] . ' (' . ($c['level'] ?: 'N/A') . ')',
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
// POST: Tạo đơn xin nghỉ
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
    $startDate = trim($input['start_date'] ?? '');
    $endDate   = trim($input['end_date'] ?? '');

    // Validate
    $errors = [];
    if (!$classId) $errors[] = 'Vui lòng chọn khóa học.';
    if (!$reason)  $errors[] = 'Vui lòng nhập lý do nghỉ.';
    if (!$startDate) $errors[] = 'Vui lòng chọn ngày bắt đầu.';
    if (!$endDate)   $errors[] = 'Vui lòng chọn ngày kết thúc.';

    if ($startDate && $endDate && strtotime($endDate) < strtotime($startDate)) {
        $errors[] = 'Ngày kết thúc phải sau ngày bắt đầu.';
    }

    if ($startDate && strtotime($startDate) < strtotime('today')) {
        $errors[] = 'Ngày bắt đầu không thể là ngày trong quá khứ.';
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO leave_requests (student_id, class_id, reason, start_date, end_date, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$studentId, $classId, $reason, $startDate, $endDate]);

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
