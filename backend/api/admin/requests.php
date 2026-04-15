<?php
/**
 * backend/api/admin/requests.php
 * GET  → Lấy danh sách tổng hợp Nghỉ học & Học bù
 * POST → Duyệt hoặc Từ chối yêu cầu
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

// Chỉ admin mới có quyền truy cập
JwtHelper::requireAuth('admin');

// ═══════════════════════════════════════
// GET: Lấy danh sách tổng hợp
// ═══════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // 1. Lấy đơn nghỉ học
        $stmtLeave = $pdo->prepare("
            SELECT 
                lr.id,
                'leave' as type,
                lr.student_id,
                u.full_name as student_name,
                u.email as student_email,
                lr.class_id,
                co.title as course_title,
                co.id as course_id,
                cl.class_name,
                e.class_detail_id,
                cd.detail_name as shift_name,
                lr.reason,
                lr.start_date as request_date,
                lr.status,
                lr.created_at
            FROM leave_requests lr
            JOIN users u ON lr.student_id = u.id
            LEFT JOIN classes cl ON lr.class_id = cl.id
            LEFT JOIN courses co ON cl.course_id = co.id
            LEFT JOIN enrollments e ON e.student_id = lr.student_id AND e.class_id = lr.class_id
            LEFT JOIN class_details cd ON cd.id = e.class_detail_id
            ORDER BY lr.created_at DESC
        ");
        $stmtLeave->execute();
        $leavesRaw = $stmtLeave->fetchAll(PDO::FETCH_ASSOC);

        $leaves = [];
        foreach ($leavesRaw as $r) {
            // Tính session index
            $stmtIdx = $pdo->prepare("
                SELECT COUNT(*) FROM schedules 
                WHERE class_id = ? 
                  AND (class_detail_id = ? OR class_detail_id IS NULL)
                  AND study_date <= ?
                  AND status != 'canceled'
            ");
            $stmtIdx->execute([$r['class_id'], $r['class_detail_id'], $r['request_date']]);
            $sessionIndex = (int) $stmtIdx->fetchColumn();

            // Lấy tên bài học
            $stmtLesson = $pdo->prepare("SELECT title FROM lessons WHERE course_id = ? AND order_number = ? LIMIT 1");
            $stmtLesson->execute([$r['course_id'], $sessionIndex]);
            $lessonDetail = $stmtLesson->fetch(PDO::FETCH_ASSOC);

            $r['lesson_title'] = $lessonDetail ? $lessonDetail['title'] : "Bài $sessionIndex";
            $leaves[] = $r;
        }

        // 2. Lấy đơn học bù
        $stmtMakeup = $pdo->prepare("
            SELECT 
                mr.id,
                'makeup' as type,
                mr.student_id,
                u.full_name as student_name,
                u.email as student_email,
                s.class_id,
                s.class_detail_id,
                co.title as course_title,
                co.id as course_id,
                cl.class_name,
                cd.detail_name as shift_name,
                '' as reason,
                s.study_date as request_date,
                COALESCE(mr.status, 'registered') as status,
                mr.admin_note,
                mr.created_at
            FROM makeup_registrations mr
            JOIN users u ON mr.student_id = u.id
            JOIN schedules s ON mr.target_schedule_id = s.id
            JOIN classes cl ON s.class_id = cl.id
            JOIN courses co ON cl.course_id = co.id
            LEFT JOIN class_details cd ON s.class_detail_id = cd.id
            ORDER BY mr.created_at DESC
        ");
        $stmtMakeup->execute();
        $makeupsRaw = $stmtMakeup->fetchAll(PDO::FETCH_ASSOC);

        $makeups = [];
        foreach ($makeupsRaw as $r) {
            $r['status'] = $r['status'] ?? 'registered'; // Fallback if somehow null
            
            // Tính session index
            $stmtIdx = $pdo->prepare("
                SELECT COUNT(*) FROM schedules 
                WHERE class_id = ? 
                  AND (class_detail_id = ? OR class_detail_id IS NULL)
                  AND study_date <= ?
                  AND status != 'canceled'
            ");
            $stmtIdx->execute([$r['class_id'], $r['class_detail_id'], $r['request_date']]);
            $sessionIndex = (int) $stmtIdx->fetchColumn();

            // Lấy tên bài học
            $stmtLesson = $pdo->prepare("SELECT title FROM lessons WHERE course_id = ? AND order_number = ? LIMIT 1");
            $stmtLesson->execute([$r['course_id'], $sessionIndex]);
            $lessonDetail = $stmtLesson->fetch(PDO::FETCH_ASSOC);

            $r['lesson_title'] = $lessonDetail ? $lessonDetail['title'] : "Bài $sessionIndex";
            $makeups[] = $r;
        }

        // Hợp nhất và sắp xếp theo thời gian tạo mới nhất
        $allRequests = array_merge($leaves, $makeups);
        usort($allRequests, function($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        echo json_encode([
            'status' => 'success',
            'data' => $allRequests
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
    exit;
}

// ═══════════════════════════════════════
// POST: Duyệt hoặc Từ chối
// ═══════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $type   = $input['type'] ?? ''; // 'leave' hoặc 'makeup'
    $id     = isset($input['id']) ? (int) $input['id'] : 0;
    $action = $input['action'] ?? ''; // 'approve' hoặc 'reject'
    $note   = trim($input['admin_note'] ?? '');

    if (!$id || !in_array($type, ['leave', 'makeup']) || !in_array($action, ['approve', 'reject'])) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.']);
        exit;
    }

    try {
        // Sử dụng Auto-commit để tránh lỗi transaction trôi lơ lửng
        if ($type === 'leave') {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            $stmt = $pdo->prepare("UPDATE leave_requests SET status = ?, admin_note = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $note, $id]);

            if ($action === 'approve') {
                $stmtInfo = $pdo->prepare("SELECT student_id, class_id, start_date FROM leave_requests WHERE id = ?");
                $stmtInfo->execute([$id]);
                $lr = $stmtInfo->fetch(PDO::FETCH_ASSOC);

                if ($lr) {
                    $stmtEnroll = $pdo->prepare("SELECT class_detail_id FROM enrollments WHERE student_id = ? AND class_id = ? AND status = 'active'");
                    $stmtEnroll->execute([$lr['student_id'], $lr['class_id']]);
                    $cdid = $stmtEnroll->fetchColumn();

                    $stmtSch = $pdo->prepare("SELECT id FROM schedules WHERE class_id = ? AND study_date = ? AND (class_detail_id = ? OR class_detail_id IS NULL)");
                    $stmtSch->execute([$lr['class_id'], $lr['start_date'], $cdid]);
                    $schId = $stmtSch->fetchColumn();

                    if ($schId) {
                        $stmtAtt = $pdo->prepare("
                            INSERT INTO attendance_records (schedule_id, student_id, status, note, created_at)
                            VALUES (?, ?, 'excused', 'Nghỉ học có phép', NOW())
                            ON DUPLICATE KEY UPDATE status = 'excused', note = 'Nghỉ học có phép'
                        ");
                        $stmtAtt->execute([$schId, $lr['student_id']]);
                    }
                }
            }
        } 
        else if ($type === 'makeup') {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            
            // Ép buộc kiểm tra ID trước
            $check = $pdo->prepare("SELECT id, status FROM makeup_registrations WHERE id = ?");
            $check->execute([$id]);
            $existing = $check->fetch(PDO::FETCH_ASSOC);

            if (!$existing) {
                throw new Exception("Không tìm thấy đơn học bù ID: $id trong hệ thống.");
            }

            // Thực hiện cập nhật
            $stmt = $pdo->prepare("UPDATE makeup_registrations SET status = ?, admin_note = ? WHERE id = ?");
            $stmt->execute([$status, $note, $id]);
            
            $affected = $stmt->rowCount();
            if ($affected === 0 && $existing['status'] === $status) {
                // Đã ở trạng thái này rồi, vẫn coi là thành công
            } else if ($affected === 0) {
                // Không cập nhật được dù đã tìm thấy (có thể do lỗi db lock)
                throw new Exception("Lỗi: Không thể cập nhật trạng thái đơn ID: $id vào Database.");
            }
        }

        echo json_encode([
            'status' => 'success', 
            'message' => 'Cập nhật trạng thái thành công.',
            'debug' => ['type' => $type, 'id' => $id, 'action' => $action]
        ]);

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Lỗi xử lý: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
