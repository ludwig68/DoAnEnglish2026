<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
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

// ── GET: Danh sách bài nộp cần chấm (writing) ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Chế độ xem chi tiết 1 bài nộp
    if (isset($_GET['submission_id'])) {
        $subId = (int)$_GET['submission_id'];
        try {
            $stmt = $pdo->prepare("
                SELECT
                    sub.id, sub.submission_content, sub.score, sub.feedback, sub.submitted_at,
                    sub.class_id, sub.assignment_id, sub.student_id,
                    u.full_name AS student_name, u.email AS student_email,
                    a.title AS assignment_title, a.description AS prompt,
                    c.class_name
                FROM submissions sub
                JOIN users u ON u.id = sub.student_id
                JOIN assignments a ON a.id = sub.assignment_id
                JOIN classes c ON c.id = sub.class_id
                WHERE sub.id = ? AND c.instructor_id = ?
            ");
            $stmt->execute([$subId, $teacherId]);
            $sub = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$sub) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy bài nộp.']);
                exit;
            }

            // Word count from submission content
            $text = strip_tags($sub['submission_content'] ?? '');
            $wordCount = $text ? count(preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY)) : 0;

            echo json_encode([
                'status' => 'success',
                'data' => [
                    ...$sub,
                    'word_count' => $wordCount,
                    'score' => $sub['score'] !== null ? (float)$sub['score'] : null,
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // Danh sách hàng chờ chấm / lịch sử
    $mode = $_GET['mode'] ?? 'queue'; // queue | history
    $classId = (int)($_GET['class_id'] ?? 0);

    try {
        $conditions = ["c.instructor_id = ?"];
        $params = [$teacherId];

        if ($mode === 'queue') {
            $conditions[] = "sub.score IS NULL";
        }
        if ($classId > 0) {
            $conditions[] = "sub.class_id = ?";
            $params[] = $classId;
        }

        $where = implode(' AND ', $conditions);

        $stmt = $pdo->prepare("
            SELECT
                sub.id, sub.student_id, sub.assignment_id, sub.class_id,
                sub.score, sub.submitted_at, sub.feedback,
                u.full_name AS student_name,
                a.title AS assignment_title, a.description AS prompt,
                c.class_name,
                CHAR_LENGTH(REGEXP_REPLACE(TRIM(REGEXP_REPLACE(COALESCE(sub.submission_content,''), '<[^>]+>', ' ')), '\\\\s+', ' ')) 
                    / 5 AS approx_word_count
            FROM submissions sub
            JOIN users u ON u.id = sub.student_id
            JOIN assignments a ON a.id = sub.assignment_id
            JOIN classes c ON c.id = sub.class_id
            WHERE $where
            ORDER BY sub.submitted_at DESC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stats
        $totalPending = 0;
        $totalGraded = 0;
        foreach ($rows as $r) {
            if ($r['score'] === null) $totalPending++;
            else $totalGraded++;
        }

        $formatted = array_map(function($r) {
            return [
                'id' => (int)$r['id'],
                'student_name' => $r['student_name'],
                'class_name' => $r['class_name'],
                'assignment_title' => $r['assignment_title'],
                'prompt' => $r['prompt'],
                'score' => $r['score'] !== null ? (float)$r['score'] : null,
                'submitted_at' => $r['submitted_at'],
                'approx_word_count' => (int)$r['approx_word_count'],
            ];
        }, $rows);

        echo json_encode([
            'status' => 'success',
            'stats' => ['pending' => $totalPending, 'graded' => $totalGraded],
            'data' => $formatted,
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// ── PUT: Chấm điểm và gửi phản hồi ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $subId = (int)($payload['submission_id'] ?? 0);
    $score = isset($payload['score']) ? (float)$payload['score'] : null;
    $feedback = trim($payload['feedback'] ?? '');
    $rubric = $payload['rubric'] ?? null; // JSON rubric breakdown

    if ($subId <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Mã bài nộp không hợp lệ.']);
        exit;
    }
    if ($score === null || $score < 0 || $score > 10) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Điểm phải từ 0 đến 10.']);
        exit;
    }

    try {
        // Verify instructor has access to this submission
        $checkStmt = $pdo->prepare("
            SELECT sub.id FROM submissions sub
            JOIN classes c ON c.id = sub.class_id
            WHERE sub.id = ? AND c.instructor_id = ?
        ");
        $checkStmt->execute([$subId, $teacherId]);
        if (!$checkStmt->fetch()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền chấm bài này.']);
            exit;
        }

        $fullFeedback = $feedback;
        if ($rubric) {
            $fullFeedback = json_encode(['text' => $feedback, 'rubric' => $rubric]);
        }

        $stmt = $pdo->prepare("
            UPDATE submissions
            SET score = ?, feedback = ?
            WHERE id = ?
        ");
        $stmt->execute([round($score, 2), $fullFeedback, $subId]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Đã chấm điểm thành công.'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
