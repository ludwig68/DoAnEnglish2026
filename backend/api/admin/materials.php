<?php
// ═══════════════════════════════════════════════════════════════
// API: CRUD tài liệu giảng dạy (Admin)
// GET: Lấy danh sách | POST: Thêm | DELETE: Xóa
// ═══════════════════════════════════════════════════════════════
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

$authUser = JwtHelper::requireAuth();
if (($authUser['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}
$adminId = (int)($authUser['sub'] ?? 0);

// ═══ GET: Lấy danh sách tài liệu ═══
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $courseId = (int)($_GET['course_id'] ?? 0);
    $lessonId = (int)($_GET['lesson_id'] ?? 0);

    try {
        $conditions = [];
        $params = [];

        if ($courseId > 0) {
            $conditions[] = "m.course_id = ?";
            $params[] = $courseId;
        }
        if ($lessonId > 0) {
            $conditions[] = "m.lesson_id = ?";
            $params[] = $lessonId;
        }

        $where = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $pdo->prepare("
            SELECT 
                m.id, m.course_id, m.lesson_id, m.schedule_id,
                m.title, m.file_type, m.file_url, m.file_size, m.original_name,
                m.created_at,
                c.title AS course_title,
                l.title AS lesson_title,
                s.study_date, s.start_time, s.end_time,
                u.full_name AS uploaded_by_name
            FROM course_materials m
            JOIN courses c ON c.id = m.course_id
            LEFT JOIN lessons l ON l.id = m.lesson_id
            LEFT JOIN schedules s ON s.id = m.schedule_id
            LEFT JOIN users u ON u.id = m.uploaded_by
            $where
            ORDER BY m.created_at DESC
        ");
        $stmt->execute($params);
        $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = array_map(function($m) {
            return [
                'id'             => (int)$m['id'],
                'course_id'      => (int)$m['course_id'],
                'lesson_id'      => $m['lesson_id'] ? (int)$m['lesson_id'] : null,
                'schedule_id'    => $m['schedule_id'] ? (int)$m['schedule_id'] : null,
                'title'          => $m['title'],
                'file_type'      => $m['file_type'],
                'file_url'       => $m['file_url'],
                'file_size'      => (int)$m['file_size'],
                'original_name'  => $m['original_name'],
                'created_at'     => $m['created_at'],
                'course_title'   => $m['course_title'],
                'lesson_title'   => $m['lesson_title'],
                'study_date'     => $m['study_date'],
                'start_time'     => $m['start_time'] ? substr($m['start_time'], 0, 5) : null,
                'end_time'       => $m['end_time'] ? substr($m['end_time'], 0, 5) : null,
                'uploaded_by_name' => $m['uploaded_by_name'],
            ];
        }, $materials);

        echo json_encode(['status' => 'success', 'data' => $formatted]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// ═══ POST: Thêm tài liệu mới ═══
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    
    $courseId   = (int)($payload['course_id'] ?? 0);
    $lessonId   = !empty($payload['lesson_id']) ? (int)$payload['lesson_id'] : null;
    $scheduleId = !empty($payload['schedule_id']) ? (int)$payload['schedule_id'] : null;
    $title      = trim($payload['title'] ?? '');
    $fileType   = $payload['file_type'] ?? 'document';
    $fileUrl    = trim($payload['file_url'] ?? '');
    $fileSize   = (int)($payload['file_size'] ?? 0);
    $originalName = trim($payload['original_name'] ?? '');

    if (!$courseId || !$title || !$fileUrl) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin bắt buộc (course_id, title, file_url).']);
        exit;
    }

    // Validate course exists
    $stmtCheck = $pdo->prepare("SELECT id FROM courses WHERE id = ?");
    $stmtCheck->execute([$courseId]);
    if (!$stmtCheck->fetch()) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Khóa học không tồn tại.']);
        exit;
    }

    // Validate lesson belongs to course (if provided)
    if ($lessonId) {
        $stmtL = $pdo->prepare("SELECT id FROM lessons WHERE id = ? AND course_id = ?");
        $stmtL->execute([$lessonId, $courseId]);
        if (!$stmtL->fetch()) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Bài học không thuộc khóa học này.']);
            exit;
        }
    }

    // Validate schedule belongs to a class of this course (if provided)
    if ($scheduleId) {
        $stmtS = $pdo->prepare("
            SELECT s.id FROM schedules s
            JOIN classes cl ON cl.id = s.class_id
            WHERE s.id = ? AND cl.course_id = ?
        ");
        $stmtS->execute([$scheduleId, $courseId]);
        if (!$stmtS->fetch()) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Lịch dạy không thuộc khóa học này.']);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO course_materials (course_id, lesson_id, schedule_id, title, file_type, file_url, file_size, original_name, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$courseId, $lessonId, $scheduleId, $title, $fileType, $fileUrl, $fileSize, $originalName, $adminId]);
        $newId = $pdo->lastInsertId();

        echo json_encode([
            'status' => 'success',
            'message' => 'Đã thêm tài liệu thành công.',
            'data' => ['id' => (int)$newId]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// ═══ DELETE: Xóa tài liệu ═══
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Mã tài liệu không hợp lệ.']);
        exit;
    }

    try {
        // Get file path before deleting
        $stmt = $pdo->prepare("SELECT file_url FROM course_materials WHERE id = ?");
        $stmt->execute([$id]);
        $mat = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mat) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy tài liệu.']);
            exit;
        }

        // Delete file from disk
        if ($mat['file_url'] && str_contains($mat['file_url'], '/uploads/materials/')) {
            $filePath = dirname(__DIR__, 2) . str_replace('/backend', '', $mat['file_url']);
            // Attempt to build correct path
            $filePath2 = dirname(__DIR__, 2) . '/uploads/materials/' . basename($mat['file_url']);
            if (file_exists($filePath2)) {
                unlink($filePath2);
            }
        }

        // Delete DB record
        $stmtDel = $pdo->prepare("DELETE FROM course_materials WHERE id = ?");
        $stmtDel->execute([$id]);

        echo json_encode(['status' => 'success', 'message' => 'Đã xóa tài liệu.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
