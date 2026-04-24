<?php
// ═══════════════════════════════════════════════════════════════
// API: Chấm điểm bài tập (Giảng viên)
// Hỗ trợ: GET (danh sách / chi tiết), PUT (chấm điểm)
// ═══════════════════════════════════════════════════════════════
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

$authUser = JwtHelper::requireAuth();
if (($authUser['role'] ?? '') !== 'instructor') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

$teacherId = (int) ($authUser['sub'] ?? 0);

// ═══════════════════════════════════════════════════════════════
// GET: Danh sách bài nộp / Chi tiết 1 bài
// ═══════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // ── Xem chi tiết 1 bài nộp ──
    if (isset($_GET['submission_id'])) {
        $subIdRaw = $_GET['submission_id'];
        $isQuiz = strpos($subIdRaw, 'qs_') === 0;

        try {
            if ($isQuiz) {
                // Xử lý bài Tự luận nằm trong Quiz
                $qsId = (int) str_replace('qs_', '', $subIdRaw);
                $stmt = $pdo->prepare("
                    SELECT qs.id, qs.answers_json, qs.score, qs.feedback, qs.rubric_data, qs.submitted_at, qs.status,
                           u.full_name AS student_name, u.email AS student_email,
                           q.id AS quiz_id, q.title AS assignment_title,
                           c.class_name
                    FROM quiz_submissions qs
                    JOIN users u ON u.id = qs.student_id
                    JOIN quizzes q ON q.id = qs.quiz_id
                    JOIN classes c ON c.id = qs.class_id
                    WHERE qs.id = ? AND c.instructor_id = ?
                ");
                $stmt->execute([$qsId, $teacherId]);
                $sub = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$sub) {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy bài nộp quiz.']);
                    exit;
                }

                $stmtQ = $pdo->prepare("SELECT id, question_text FROM questions WHERE quiz_id = ? AND question_type IN ('writing', 'essay') LIMIT 1");
                $stmtQ->execute([$sub['quiz_id']]);
                $essayQ = $stmtQ->fetch(PDO::FETCH_ASSOC);

                $content = '';
                $prompt = '';
                if ($essayQ) {
                    $prompt = $essayQ['question_text'];
                    $answers = json_decode($sub['answers_json'], true);
                    $content = $answers[$essayQ['id']] ?? '';
                }

                $text = strip_tags($content);
                $wordCount = $text ? count(preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY)) : 0;

                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'id' => 'qs_' . $sub['id'],
                        'submission_content' => $content,
                        'score' => $sub['score'] !== null ? (float) $sub['score'] : null,
                        'feedback' => $sub['feedback'] ?? '',
                        'rubric_data' => $sub['rubric_data'] ? json_decode($sub['rubric_data'], true) : null,
                        'status' => $sub['status'] ?? (($sub['score'] !== null) ? 'completed' : 'pending_grading'),
                        'submitted_at' => $sub['submitted_at'],
                        'student_name' => $sub['student_name'],
                        'student_email' => $sub['student_email'],
                        'assignment_title' => $sub['assignment_title'] . ' (Tự luận)',
                        'prompt' => $prompt,
                        'class_name' => $sub['class_name'],
                        'word_count' => $wordCount,
                    ]
                ]);
            } else {
                // Xử lý bài luận thông thường (submissions)
                $subId = (int) $subIdRaw;
                $stmt = $pdo->prepare("
                    SELECT
                        sub.id, sub.submission_content, sub.score, sub.feedback, sub.rubric_data, sub.submitted_at,
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

                $text = strip_tags($sub['submission_content'] ?? '');
                $wordCount = $text ? count(preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY)) : 0;

                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'id' => (int) $sub['id'],
                        'submission_content' => $sub['submission_content'],
                        'score' => $sub['score'] !== null ? (float) $sub['score'] : null,
                        'feedback' => $sub['feedback'],
                        'rubric_data' => $sub['rubric_data'] ? json_decode($sub['rubric_data'], true) : null,
                        'submitted_at' => $sub['submitted_at'],
                        'student_name' => $sub['student_name'],
                        'student_email' => $sub['student_email'],
                        'assignment_title' => $sub['assignment_title'],
                        'prompt' => $sub['prompt'],
                        'class_name' => $sub['class_name'],
                        'status' => ($sub['score'] !== null) ? 'completed' : 'pending_grading',
                        'word_count' => $wordCount,
                    ]
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ── Danh sách hàng chờ chấm / lịch sử đã chấm ──
    $mode = $_GET['mode'] ?? 'queue'; // queue | history
    $classId = (int) ($_GET['class_id'] ?? 0);

    try {
        $formatted = [];
        $pendingCount = 0;
        $gradedCount = 0;

        // --- FETCH STANDARD SUBMISSIONS ---
        $conditions = ["c.instructor_id = ?"];
        $params = [$teacherId];

        if ($mode === 'queue') {
            $conditions[] = "sub.score IS NULL";
        } else {
            $conditions[] = "sub.score IS NOT NULL";
        }

        if ($classId > 0) {
            $conditions[] = "sub.class_id = ?";
            $params[] = $classId;
        }

        $where = implode(' AND ', $conditions);

        $stmt = $pdo->prepare("
            SELECT
                sub.id, sub.score, sub.submitted_at, sub.submission_content,
                u.full_name AS student_name,
                a.title AS assignment_title, a.description AS prompt, a.deadline,
                c.class_name
            FROM submissions sub
            JOIN users u ON u.id = sub.student_id
            JOIN assignments a ON a.id = sub.assignment_id
            JOIN classes c ON c.id = sub.class_id
            WHERE $where
        ");
        $stmt->execute($params);
        $rowsSub = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rowsSub as $r) {
            $text = strip_tags($r['submission_content'] ?? '');
            $wc = $text ? count(preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY)) : 0;
            $formatted[] = [
                'id' => (int) $r['id'],
                'student_name' => $r['student_name'],
                'class_name' => $r['class_name'],
                'assignment_title' => $r['assignment_title'],
                'prompt' => $r['prompt'],
                'score' => $r['score'] !== null ? (float) $r['score'] : null,
                'submitted_at' => $r['submitted_at'],
                'deadline' => $r['deadline'],
                'approx_word_count' => $wc,
            ];
        }

        // Stats for standard
        $stmtStats = $pdo->prepare("
            SELECT
                SUM(CASE WHEN sub.score IS NULL THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN sub.score IS NOT NULL THEN 1 ELSE 0 END) as graded
            FROM submissions sub
            JOIN classes c ON c.id = sub.class_id
            WHERE c.instructor_id = ?
        ");
        $stmtStats->execute([$teacherId]);
        $statsRow = $stmtStats->fetch(PDO::FETCH_ASSOC);
        $pendingCount += (int) ($statsRow['pending'] ?? 0);
        $gradedCount += (int) ($statsRow['graded'] ?? 0);

        // --- FETCH QUIZ SUBMISSIONS WITH ESSAYS ---
        $qsConditions = ["c.instructor_id = ?"];
        $qsParams = [$teacherId];

        if ($mode === 'queue') {
            $qsConditions[] = "qs.status = 'pending_grading'";
        } else {
            $qsConditions[] = "(qs.feedback IS NOT NULL OR qs.rubric_data IS NOT NULL)";
        }

        if ($classId > 0) {
            $qsConditions[] = "c.id = ?";
            $qsParams[] = $classId;
        }
        $qsWhere = implode(' AND ', $qsConditions);

        $stmtQs = $pdo->prepare("
            SELECT
                qs.id, qs.quiz_id, qs.score, qs.status, qs.submitted_at, qs.answers_json, qs.feedback, qs.rubric_data,
                u.full_name AS student_name,
                q.title AS assignment_title,
                c.class_name
            FROM quiz_submissions qs
            JOIN users u ON u.id = qs.student_id
            JOIN quizzes q ON q.id = qs.quiz_id
            JOIN classes c ON c.id = qs.class_id
            WHERE $qsWhere
        ");
        $stmtQs->execute($qsParams);
        $rowsQs = $stmtQs->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rowsQs as $r) {
            // Find essay
            $stmtQ = $pdo->prepare("SELECT id, question_text FROM questions WHERE quiz_id = ? AND question_type IN ('writing', 'essay') LIMIT 1");
            $stmtQ->execute([$r['quiz_id']]);
            $essayQ = $stmtQ->fetch(PDO::FETCH_ASSOC);
            if ($essayQ) {
                $answers = json_decode($r['answers_json'], true);
                $content = $answers[$essayQ['id']] ?? '';
                $text = strip_tags($content);
                $wc = $text ? count(preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY)) : 0;
                $formatted[] = [
                    'id' => 'qs_' . $r['id'],
                    'student_name' => $r['student_name'],
                    'class_name' => $r['class_name'],
                    'assignment_title' => $r['assignment_title'] . ' (Tự luận)',
                    'prompt' => $essayQ['question_text'],
                    'score' => ($r['feedback'] || $r['rubric_data']) ? (float) $r['score'] : null,
                    'submitted_at' => $r['submitted_at'],
                    'approx_word_count' => $wc,
                ];
            }
        }

        // Stats for quizzes
        $stmtStatsQs = $pdo->prepare("
            SELECT
                SUM(CASE WHEN qs.status = 'pending_grading' THEN 1 ELSE 0 END) as pendingQs,
                SUM(CASE WHEN (qs.feedback IS NOT NULL OR qs.rubric_data IS NOT NULL) THEN 1 ELSE 0 END) as gradedQs
            FROM quiz_submissions qs
            JOIN quizzes q ON q.id = qs.quiz_id
            JOIN classes c ON c.id = qs.class_id
            WHERE c.instructor_id = ?
              AND EXISTS (
                  SELECT 1
                  FROM questions qst
                  WHERE qst.quiz_id = q.id
                    AND qst.question_type IN ('writing', 'essay')
              )
        ");
        $stmtStatsQs->execute([$teacherId]);
        $statsRowQs = $stmtStatsQs->fetch(PDO::FETCH_ASSOC) ?: ['pendingQs' => 0, 'gradedQs' => 0];
        $pendingCount += (int) ($statsRowQs['pendingQs'] ?? 0);
        $gradedCount += (int) ($statsRowQs['gradedQs'] ?? 0);

        // Sort by submitted_at DESC
        usort($formatted, function ($a, $b) {
            return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
        });

        echo json_encode([
            'status' => 'success',
            'stats' => [
                'pending' => $pendingCount,
                'graded' => $gradedCount,
            ],
            'data' => $formatted,
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// ═══════════════════════════════════════════════════════════════
// PUT: Chấm điểm và gửi phản hồi
// ═══════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $subIdRaw = $payload['submission_id'] ?? '';
    $score = isset($payload['score']) ? (float) $payload['score'] : null;
    $feedback = trim($payload['feedback'] ?? '');
    $rubric = $payload['rubric'] ?? null;

    if (!$subIdRaw) {
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
        $rubricJson = $rubric ? json_encode($rubric) : null;
        $isQuiz = strpos($subIdRaw, 'qs_') === 0;

        if ($isQuiz) {
            $qsId = (int) str_replace('qs_', '', $subIdRaw);
            // Verify permission
            $checkStmt = $pdo->prepare("
                SELECT qs.id FROM quiz_submissions qs
                JOIN classes c ON c.id = qs.class_id
                WHERE qs.id = ? AND c.instructor_id = ?
            ");
            $checkStmt->execute([$qsId, $teacherId]);
            if (!$checkStmt->fetch()) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền chấm bài này.']);
                exit;
            }

            // Update quiz_submission
            $stmt = $pdo->prepare("
                UPDATE quiz_submissions
                SET score = ?, feedback = ?, rubric_data = ?, status = 'completed'
                WHERE id = ?
            ");
            $success = $stmt->execute([round($score, 2), $feedback, $rubricJson, $qsId]);
            if (!$success) {
                throw new Exception("Lỗi cập nhật CSDL cho bài Quiz.");
            }

            // --- CREATE NOTIFICATION ---
            $infoStmt = $pdo->prepare("SELECT qs.student_id, q.title FROM quiz_submissions qs JOIN quizzes q ON q.id = qs.quiz_id WHERE qs.id = ?");
            $infoStmt->execute([$qsId]);
            $info = $infoStmt->fetch(PDO::FETCH_ASSOC);
            if ($info) {
                $notiStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'grading', ?)");
                $notiMsg = "Bài Quiz '{$info['title']}' của bạn đã có điểm: " . round($score, 2);
                $notiStmt->execute([$info['student_id'], "Thông báo điểm số", $notiMsg, "/user/assignments"]);
            }
        } else {
            $subId = (int) $subIdRaw;
            // Verify permission
            $checkStmt = $pdo->prepare("
                SELECT sub.id FROM submissions sub
                JOIN classes c ON c.id = sub.class_id
                WHERE sub.id = ? AND c.instructor_id = ?
            ");
            $checkStmt->execute([$subId, $teacherId]);
            if (!$checkStmt->fetch()) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => "Bạn không có quyền chấm bài (#$subId) này hoặc lớp học không thuộc quyền quản lý của bạn."]);
                exit;
            }

            // Update submission
            $stmt = $pdo->prepare("
                UPDATE submissions
                SET score = ?, feedback = ?, rubric_data = ?
                WHERE id = ?
            ");
            $success = $stmt->execute([round($score, 2), $feedback, $rubricJson, $subId]);
            if (!$success) {
                throw new Exception("Lỗi cập nhật CSDL cho bài nộp tự luận.");
            }

            // --- CREATE NOTIFICATION ---
            $infoStmt = $pdo->prepare("SELECT sub.student_id, a.title FROM submissions sub JOIN assignments a ON a.id = sub.assignment_id WHERE sub.id = ?");
            $infoStmt->execute([$subId]);
            $info = $infoStmt->fetch(PDO::FETCH_ASSOC);
            if ($info) {
                $notiStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'grading', ?)");
                $notiMsg = "Bài tập '{$info['title']}' của bạn đã có điểm: " . round($score, 2);
                $notiStmt->execute([$info['student_id'], "Thông báo điểm số", $notiMsg, "/user/assignments"]);
            }
        }

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
