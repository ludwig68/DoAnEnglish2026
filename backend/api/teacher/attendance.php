<?php
/**
 * backend/api/teacher/attendance.php
 * GET  → Lấy thông tin buổi học & danh sách điểm danh (kể cả học bù)
 * POST → Lưu/Cập nhật dữ liệu điểm danh
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

try {
    $authUser = JwtHelper::requireAuth();
    if (($authUser['role'] ?? '') !== 'instructor') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
        exit;
    }
    $teacherId = (int)($authUser['sub'] ?? 0);

    // ═══════════════════════════════════════
    // GET: Lấy thông tin điểm danh
    // ═══════════════════════════════════════
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $scheduleId = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;
        if (!$scheduleId) {
            // Nếu không truyền ID, tìm ca học gần nhất của giảng viên này trong ngày hôm nay hoặc sắp tới
            $stmtSuggest = $pdo->prepare("
                SELECT s.id 
                FROM schedules s
                JOIN classes c ON s.class_id = c.id
                WHERE (c.instructor_id = ? OR s.teacher_id = ?)
                  AND s.study_date >= CURRENT_DATE
                ORDER BY s.study_date ASC, s.start_time ASC
                LIMIT 1
            ");
            $stmtSuggest->execute([$teacherId, $teacherId]);
            $scheduleId = (int)$stmtSuggest->fetchColumn();
            
            if (!$scheduleId) {
                echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy ca học nào để điểm danh.']);
                exit;
            }
        }

        // 1. Thông tin buổi học
        $stmtInfo = $pdo->prepare("
            SELECT 
                s.id, s.study_date, s.start_time, s.end_time, s.room_info, s.teaching_type, s.note as session_note, s.attendance_checked,
                c.id as class_id, c.class_name,
                co.title as course_title,
                l.title as lesson_title
            FROM schedules s
            JOIN classes c ON s.class_id = c.id
            JOIN courses co ON c.course_id = co.id
            LEFT JOIN lessons l ON s.lesson_id = l.id
            WHERE s.id = ? AND (c.instructor_id = ? OR s.teacher_id = ?)
        ");
        $stmtInfo->execute([$scheduleId, $teacherId, $teacherId]);
        $session = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy thông tin buổi học hoặc bạn không có quyền.']);
            exit;
        }

        $classId = (int)$session['class_id'];

        // 2. Lấy danh sách học viên chính thức
        $stmtEnroll = $pdo->prepare("
            SELECT u.id, u.full_name, u.email, 'regular' as student_type
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            WHERE e.class_id = ? AND e.status = 'active'
        ");
        $stmtEnroll->execute([$classId]);
        $regularStudents = $stmtEnroll->fetchAll(PDO::FETCH_ASSOC);

        // 3. Lấy danh sách học viên học bù (makeup)
        $stmtMakeup = $pdo->prepare("
            SELECT u.id, u.full_name, u.email, 'makeup' as student_type
            FROM makeup_registrations mr
            JOIN users u ON mr.student_id = u.id
            WHERE mr.target_schedule_id = ? AND mr.status != 'cancelled'
        ");
        $stmtMakeup->execute([$scheduleId]);
        $makeupStudents = $stmtMakeup->fetchAll(PDO::FETCH_ASSOC);

        $allStudents = array_merge($regularStudents, $makeupStudents);

        // 4. Lấy dữ liệu điểm danh hiện tại
        $stmtAtt = $pdo->prepare("SELECT student_id, status, note FROM attendance_records WHERE schedule_id = ?");
        $stmtAtt->execute([$scheduleId]);
        $records = $stmtAtt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        // 5. Tính toán Global Attendance % cho từng học viên
        $formattedStudents = [];
        foreach ($allStudents as $s) {
            $sid = (int)$s['id'];
            
            // Tính số buổi Present / Tổng buổi đã qua của lớp này
            $stmtStats = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_past,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count
                FROM schedules sch
                LEFT JOIN attendance_records ar ON ar.schedule_id = sch.id AND ar.student_id = ?
                WHERE sch.class_id = ? 
                  AND sch.study_date < CURRENT_DATE
                  AND sch.status != 'canceled'
            ");
            $stmtStats->execute([$sid, $classId]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
            
            $totalPast = (int)$stats['total_past'];
            $presentCount = (int)$stats['present_count'];
            $attendancePct = $totalPast > 0 ? round(($presentCount / $totalPast) * 100) : 100;

            $formattedStudents[] = [
                'id'            => $sid,
                'full_name'     => $s['full_name'],
                'email'         => $s['email'],
                'student_type'  => $s['student_type'],
                'attendance_pct'=> $attendancePct,
                'current_status'=> $records[$sid]['status'] ?? 'unmarked',
                'current_note'  => $records[$sid]['note'] ?? ''
            ];
        }

        echo json_encode([
            'status' => 'success',
            'data'   => [
                'session'  => $session,
                'students' => $formattedStudents
            ]
        ]);
        exit;
    }

    // ═══════════════════════════════════════
    // POST: Lưu điểm danh
    // ═══════════════════════════════════════
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $scheduleId = isset($input['schedule_id']) ? (int)$input['schedule_id'] : 0;
        $records    = $input['records'] ?? []; // Array of { student_id, status, note }
        $sessionNote = $input['session_note'] ?? null;

        if (!$scheduleId) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID buổi học.']);
            exit;
        }

        // Bắt đầu transaction
        $pdo->beginTransaction();

        try {
            // 1. Cập nhật note và trạng thái buổi học
            $stmtUpdateSch = $pdo->prepare("UPDATE schedules SET note = ?, attendance_checked = 1, status = 'completed' WHERE id = ?");
            $stmtUpdateSch->execute([$sessionNote, $scheduleId]);

            // Lấy class_id để kiểm tra warning
            $stmtGetClass = $pdo->prepare("SELECT class_id FROM schedules WHERE id = ?");
            $stmtGetClass->execute([$scheduleId]);
            $classId = (int)$stmtGetClass->fetchColumn();

            // 2. Lưu từng bản ghi điểm danh
            foreach ($records as $r) {
                $sid    = (int)$r['student_id'];
                $status = $r['status'];
                $rowNote = $r['note'] ?? '';

                if (!in_array($status, ['present', 'absent', 'late', 'excused'])) continue;

                $stmtUpsert = $pdo->prepare("
                    INSERT INTO attendance_records (schedule_id, student_id, status, note, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE status = VALUES(status), note = VALUES(note)
                ");
                $stmtUpsert->execute([$scheduleId, $sid, $status, $rowNote]);

                // 3. Logic Warning: Nếu status là 'absent'
                if ($status === 'absent') {
                    // Đếm tổng số buổi vắng của học viên này trong lớp này
                    $stmtCountAbsence = $pdo->prepare("
                        SELECT COUNT(*) 
                        FROM attendance_records ar
                        JOIN schedules s ON ar.schedule_id = s.id
                        WHERE ar.student_id = ? AND s.class_id = ? AND ar.status = 'absent'
                    ");
                    $stmtCountAbsence->execute([$sid, $classId]);
                    $absenceCount = (int)$stmtCountAbsence->fetchColumn();

                    if ($absenceCount >= 3) {
                        // Check if warning already exists for this count
                        $stmtCheckWarn = $pdo->prepare("SELECT id FROM academic_warnings WHERE student_id = ? AND class_id = ? AND trigger_count = ?");
                        $stmtCheckWarn->execute([$sid, $classId, $absenceCount]);
                        if (!$stmtCheckWarn->fetch()) {
                            $stmtInsertWarn = $pdo->prepare("INSERT INTO academic_warnings (student_id, class_id, schedule_id, trigger_count) VALUES (?, ?, ?, ?)");
                            $stmtInsertWarn->execute([$sid, $classId, $scheduleId, $absenceCount]);
                        }
                    }
                }
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Điểm danh đã được lưu thành công.']);

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
