<?php
/**
 * backend/api/user/makeup_registrations.php
 * GET  → Lấy danh sách đăng ký học bù + lịch học bù khả dụng
 * POST → Tạo đăng ký học bù
 * DELETE → Hủy đăng ký học bù
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
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

// GET: Lấy lịch học bù khả dụng & danh sách đã đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // 1. Lấy danh sách đã đăng ký 
        $stmtRegs = $pdo->prepare("
            SELECT 
                mr.id as makeup_id,
                mr.status,
                mr.admin_note,
                s.id as schedule_id,
                s.study_date,
                s.start_time,
                s.end_time,
                s.room_info,
                c.class_name,
                co.title as course_title,
                co.id as course_id
            FROM makeup_registrations mr
            JOIN schedules s ON mr.target_schedule_id = s.id
            JOIN classes c ON s.class_id = c.id
            JOIN courses co ON c.course_id = co.id
            WHERE mr.student_id = ? AND mr.status != 'cancelled'
            ORDER BY s.study_date ASC, s.start_time ASC
        ");
        $stmtRegs->execute([$studentId]);
        $registrations = $stmtRegs->fetchAll(PDO::FETCH_ASSOC);

        // Map format registered + tìm lesson title thật
        $registered = [];
        foreach ($registrations as $r) {
            // Tính session index của target_schedule
            $stmtIdx = $pdo->prepare("
                SELECT COUNT(*) FROM schedules 
                WHERE class_id = (SELECT class_id FROM schedules WHERE id = ?) 
                  AND (class_detail_id = (SELECT class_detail_id FROM schedules WHERE id = ?) OR class_detail_id IS NULL)
                  AND study_date <= (SELECT study_date FROM schedules WHERE id = ?)
                  AND status != 'canceled'
            ");
            $stmtIdx->execute([$r['schedule_id'], $r['schedule_id'], $r['schedule_id']]);
            $sessionIndex = (int) $stmtIdx->fetchColumn();

            // Lấy tên bài học
            $stmtLesson = $pdo->prepare("SELECT title FROM lessons WHERE course_id = ? AND order_number = ? LIMIT 1");
            $stmtLesson->execute([$r['course_id'], $sessionIndex]);
            $lessonDetail = $stmtLesson->fetch(PDO::FETCH_ASSOC);

            $registered[] = [
                'id' => (int) $r['makeup_id'],
                'schedule_id' => (int) $r['schedule_id'],
                'course_title' => $r['course_title'],
                'lesson_title' => $lessonDetail ? $lessonDetail['title'] : "Bài $sessionIndex",
                'class_name' => $r['class_name'],
                'study_date' => $r['study_date'],
                'start_time' => substr($r['start_time'], 0, 5),
                'end_time' => substr($r['end_time'], 0, 5),
                'room_info' => $r['room_info'],
                'session_index' => $sessionIndex,
                'status' => $r['status'],
                'admin_note' => $r['admin_note']
            ];
        }

        $registeredScheduleIds = array_column($registered, 'schedule_id');

        // 2. Lấy danh sách các lớp khả dụng để học bù.
        $stmtAvail = $pdo->prepare("
            SELECT 
                s.id,
                s.study_date,
                s.start_time,
                s.end_time,
                s.room_info,
                s.class_id,
                s.class_detail_id,
                c.class_name,
                co.title as course_title,
                co.id as course_id,
                u.full_name as teacher_name
            FROM schedules s
            JOIN classes c ON s.class_id = c.id
            JOIN courses co ON c.course_id = co.id
            LEFT JOIN users u ON s.teacher_id = u.id
            WHERE s.study_date >= CURRENT_DATE
              AND co.id IN (
                  SELECT c2.course_id 
                  FROM enrollments e 
                  JOIN classes c2 ON e.class_id = c2.id 
                  WHERE e.student_id = ? AND e.status = 'active'
              )
              AND (s.class_detail_id IS NULL OR s.class_detail_id NOT IN (
                  SELECT class_detail_id FROM enrollments WHERE student_id = ? AND status = 'active'
              ))
            ORDER BY s.study_date ASC, s.start_time ASC
        ");
        $stmtAvail->execute([$studentId, $studentId]);
        $availSchedules = $stmtAvail->fetchAll(PDO::FETCH_ASSOC);

        $available = [];
        foreach ($availSchedules as $sch) {
            if (in_array((int)$sch['id'], $registeredScheduleIds)) continue; 

            // Tính session index
            $stmtIdx = $pdo->prepare("
                SELECT COUNT(*) FROM schedules 
                WHERE class_id = ? 
                  AND (class_detail_id = ? OR class_detail_id IS NULL)
                  AND study_date <= ?
                  AND status != 'canceled'
            ");
            $stmtIdx->execute([$sch['class_id'], $sch['class_detail_id'], $sch['study_date']]);
            $sessionIndex = (int) $stmtIdx->fetchColumn();

            // Lấy tên bài học
            $stmtLesson = $pdo->prepare("SELECT title FROM lessons WHERE course_id = ? AND order_number = ? LIMIT 1");
            $stmtLesson->execute([$sch['course_id'], $sessionIndex]);
            $lessonDetail = $stmtLesson->fetch(PDO::FETCH_ASSOC);

            $available[] = [
                'id' => (int) $sch['id'],
                'course_title' => $sch['course_title'],
                'lesson_title' => $lessonDetail ? $lessonDetail['title'] : "Bài $sessionIndex",
                'teacher_name' => $sch['teacher_name'],
                'study_date' => $sch['study_date'],
                'start_time' => substr($sch['start_time'], 0, 5),
                'end_time' => substr($sch['end_time'], 0, 5),
                'room_info' => $sch['room_info'] ?: 'Chưa xếp phòng',
                'session_index' => $sessionIndex
            ];
        }

        // 3. Lấy danh sách các buổi học sinh viên đã vắng (có đơn xin nghỉ được duyệt - APPROVED)
        $stmtMissed = $pdo->prepare("
            SELECT 
                lr.id as leave_request_id,
                s.id as missed_schedule_id,
                s.study_date,
                s.start_time,
                s.class_id,
                s.class_detail_id,
                c.class_name,
                co.title as course_title,
                co.id as course_id
            FROM leave_requests lr
            INNER JOIN enrollments e ON e.student_id = lr.student_id AND e.class_id = lr.class_id
            INNER JOIN schedules s ON s.class_id = lr.class_id 
                AND s.study_date = lr.start_date 
                AND (s.class_detail_id = e.class_detail_id OR s.class_detail_id IS NULL)
            JOIN classes c ON s.class_id = c.id
            JOIN courses co ON c.course_id = co.id
            WHERE lr.student_id = ? AND lr.status = 'approved' AND e.status = 'active'
            ORDER BY s.study_date DESC
        ");
        $stmtMissed->execute([$studentId]);
        $missedRows = $stmtMissed->fetchAll(PDO::FETCH_ASSOC);

        $missedSessions = [];
        foreach ($missedRows as $r) {
            // Tính session index
            $stmtIdx = $pdo->prepare("
                SELECT COUNT(*) FROM schedules 
                WHERE class_id = ? 
                  AND (class_detail_id = ? OR class_detail_id IS NULL)
                  AND study_date <= ?
                  AND status != 'canceled'
            ");
            $stmtIdx->execute([$r['class_id'], $r['class_detail_id'], $r['study_date']]);
            $sessionIndex = (int) $stmtIdx->fetchColumn();

            // Lấy tên bài học
            $stmtLesson = $pdo->prepare("SELECT title FROM lessons WHERE course_id = ? AND order_number = ? LIMIT 1");
            $stmtLesson->execute([$r['course_id'], $sessionIndex]);
            $lessonDetail = $stmtLesson->fetch(PDO::FETCH_ASSOC);

            $missedSessions[] = [
                'leave_request_id' => (int) $r['leave_request_id'],
                'missed_schedule_id' => (int) $r['missed_schedule_id'],
                'course_title' => $r['course_title'],
                'lesson_title' => $lessonDetail ? $lessonDetail['title'] : "Bài $sessionIndex",
                'study_date' => $r['study_date'],
                'start_time' => substr($r['start_time'], 0, 5),
                'session_index' => $sessionIndex
            ];
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'registered' => $registered,
                'available' => $available,
                'missed_sessions' => $missedSessions
            ]
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}

// POST: Tạo đơn đăng ký học bù
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $targetScheduleId = isset($input['schedule_id']) ? (int) $input['schedule_id'] : 0;
    $leaveRequestId = isset($input['leave_request_id']) ? (int) $input['leave_request_id'] : null;

    if (!$targetScheduleId) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng chọn ca học bù.']);
        exit;
    }

    try {
        // --- NEW: Validation Session Index ---
        if ($leaveRequestId && $targetScheduleId) {
            // 1. Lấy thông tin buổi nghỉ
            $stmtLR = $pdo->prepare("
                SELECT lr.start_date, lr.class_id, e.class_detail_id 
                FROM leave_requests lr
                JOIN enrollments e ON e.student_id = lr.student_id AND e.class_id = lr.class_id
                WHERE lr.id = ? AND lr.student_id = ?
            ");
            $stmtLR->execute([$leaveRequestId, $studentId]);
            $lr = $stmtLR->fetch(PDO::FETCH_ASSOC);

            if ($lr) {
                // Tính index buổi nghỉ
                $stmtIdxMissed = $pdo->prepare("
                    SELECT COUNT(*) FROM schedules 
                    WHERE class_id = ? 
                      AND (class_detail_id = ? OR class_detail_id IS NULL)
                      AND study_date <= ?
                      AND status != 'canceled'
                ");
                $stmtIdxMissed->execute([$lr['class_id'], $lr['class_detail_id'], $lr['start_date']]);
                $missedIdx = (int) $stmtIdxMissed->fetchColumn();

                // Tính index buổi muốn học bù
                $stmtTS = $pdo->prepare("SELECT class_id, class_detail_id, study_date FROM schedules WHERE id = ?");
                $stmtTS->execute([$targetScheduleId]);
                $ts = $stmtTS->fetch(PDO::FETCH_ASSOC);

                if ($ts) {
                    $stmtIdxTarget = $pdo->prepare("
                        SELECT COUNT(*) FROM schedules 
                        WHERE class_id = ? 
                          AND (class_detail_id = ? OR class_detail_id IS NULL)
                          AND study_date <= ?
                          AND status != 'canceled'
                    ");
                    $stmtIdxTarget->execute([$ts['class_id'], $ts['class_detail_id'], $ts['study_date']]);
                    $targetIdx = (int) $stmtIdxTarget->fetchColumn();

                    if ($missedIdx !== $targetIdx) {
                        http_response_code(422);
                        echo json_encode(['status' => 'error', 'message' => "Bài học không khớp. Bạn phải chọn buổi học bù có cùng nội dung (Bài $missedIdx)."]);
                        exit;
                    }
                }
            }
        }

        // Check if already registered for this specific missed session?
        // Usually, a leave_request can only be made up ONCE.
        if ($leaveRequestId) {
            $stmtCheckLeave = $pdo->prepare("SELECT id FROM makeup_registrations WHERE student_id = ? AND leave_request_id = ? AND status != 'cancelled'");
            $stmtCheckLeave->execute([$studentId, $leaveRequestId]);
            if ($stmtCheckLeave->fetch()) {
                http_response_code(409);
                echo json_encode(['status' => 'error', 'message' => 'Bạn đã đăng ký học bù cho buổi nghỉ này rồi.']);
                exit;
            }
        }

        // Check if already registered
        $stmtCheck = $pdo->prepare("SELECT id FROM makeup_registrations WHERE student_id = ? AND target_schedule_id = ? AND status != 'cancelled'");
        $stmtCheck->execute([$studentId, $targetScheduleId]);
        if ($stmtCheck->fetch()) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Bạn đã đăng ký khóa học này rồi.']);
            exit;
        }

        // Insert
        $stmt = $pdo->prepare("INSERT INTO makeup_registrations (student_id, target_schedule_id, leave_request_id, status) VALUES (?, ?, ?, 'registered')");
        $stmt->execute([$studentId, $targetScheduleId, $leaveRequestId]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Đăng ký học bù thành công!'
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi đăng ký: ' . $e->getMessage()]);
    }
    exit;
}

// DELETE: Hủy đăng ký học bù
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if (!$id) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy ID đăng ký.']);
        exit;
    }

    try {
        // Kiểm tra sở hữu và thời gian hủy
        $stmt = $pdo->prepare("
            SELECT mr.id, s.study_date, s.start_time 
            FROM makeup_registrations mr
            JOIN schedules s ON mr.target_schedule_id = s.id
            WHERE mr.id = ? AND mr.student_id = ?
        ");
        $stmt->execute([$id, $studentId]);
        $reg = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reg) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy thông tin đăng ký.']);
            exit;
        }

        // Check if >= 4 hours before start time
        $scheduleDateTimeStr = $reg['study_date'] . ' ' . $reg['start_time'];
        $scheduleDateTime = new DateTime($scheduleDateTimeStr);
        $now = new DateTime();
        $diff = $scheduleDateTime->getTimestamp() - $now->getTimestamp();

        if ($diff < 4 * 3600) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Chỉ có thể hủy đăng ký trước giờ học tối thiểu 4 tiếng.']);
            exit;
        }

        $stmtDel = $pdo->prepare("UPDATE makeup_registrations SET status = 'cancelled' WHERE id = ?");
        $stmtDel->execute([$id]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Đã hủy đăng ký học bù.'
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
