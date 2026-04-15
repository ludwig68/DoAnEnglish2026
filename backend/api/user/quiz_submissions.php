<?php
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

// ══════════════════════════════════════════════
// GET: Load previous submission for a quiz
// ══════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
    if (!$quizId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing quiz_id']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT score, status, answers_json, submitted_at
            FROM quiz_submissions
            WHERE student_id = ? AND quiz_id = ?
            LIMIT 1
        ");
        $stmt->execute([$studentId, $quizId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode([
                'status'       => 'success',
                'submitted'    => true,
                'score'        => (float)$row['score'],
                'sub_status'   => $row['status'],
                'answers_json' => $row['answers_json'],
                'submitted_at' => $row['submitted_at'],
            ]);
        } else {
            echo json_encode(['status' => 'success', 'submitted' => false]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// ══════════════════════════════════════════════
// POST: Submit answers
// ══════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON body']);
    exit;
}

$quizId = isset($input['quiz_id']) ? (int)$input['quiz_id'] : 0;
if (!$quizId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing quiz_id']);
    exit;
}

$userAnswers = $input['answers'] ?? [];
$answersJson = json_encode($userAnswers);

// Check if quiz has writing questions → do not allow resubmission
try {
    $stmtCheck = $pdo->prepare("
        SELECT COUNT(*) FROM questions q
        WHERE q.quiz_id = ? AND q.question_type IN ('writing','essay')
    ");
    $stmtCheck->execute([$quizId]);
    $hasWritingQuestions = (int)$stmtCheck->fetchColumn() > 0;

    if ($hasWritingQuestions) {
        // Check if already submitted
        $stmtExisting = $pdo->prepare("
            SELECT id FROM quiz_submissions WHERE student_id = ? AND quiz_id = ? LIMIT 1
        ");
        $stmtExisting->execute([$studentId, $quizId]);
        if ($stmtExisting->fetch()) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Bài tự luận chỉ được nộp một lần.',
                'locked'  => true
            ]);
            exit;
        }
    }
} catch (PDOException $e) { /* continue */ }

// === Grade ===
$score = 0.0;
$totalQuestions = 0;
$correctCount = 0;
$hasWriting = false;
$questionResults = [];
$status = 'completed';

try {
    $stmtQs = $pdo->prepare("SELECT id, question_type, question_text FROM questions WHERE quiz_id = ?");
    $stmtQs->execute([$quizId]);
    $questions = $stmtQs->fetchAll(PDO::FETCH_ASSOC);
    $totalQuestions = count($questions);

    if ($totalQuestions > 0) {
        $questionIds = array_column($questions, 'id');

        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $stmtOpts = $pdo->prepare("
            SELECT id, question_id, is_correct, match_text, option_text
            FROM question_options
            WHERE question_id IN ($placeholders)
            ORDER BY question_id, id ASC
        ");
        $stmtOpts->execute($questionIds);
        $allOptions = $stmtOpts->fetchAll(PDO::FETCH_ASSOC);

        $correctOptionIds = [];
        $allOptionsByQ = [];
        foreach ($allOptions as $opt) {
            $qId = (int)$opt['question_id'];
            $allOptionsByQ[$qId][] = $opt;
            if ($opt['is_correct']) $correctOptionIds[$qId][] = (int)$opt['id'];
        }

        foreach ($questions as $q) {
            $qId = (int)$q['id'];
            $type = $q['question_type'];
            $questionText = $q['question_text'] ?? '';
            $userAns = $userAnswers[(string)$qId] ?? $userAnswers[$qId] ?? null;

            if ($type === 'writing' || $type === 'essay') {
                $hasWriting = true;
                $questionResults[$qId] = 'pending';
                continue;
            }

            // Mark as skipped (grey) if unanswered
            if ($userAns === null || $userAns === '' || $userAns === []) {
                $questionResults[$qId] = 'skipped';
                continue;
            }

            $isCorrect = false;
                if ($type === 'multiple_choice' || $type === 'true_false') {
                    $selectedId = (int)$userAns;
                    $isCorrect = in_array($selectedId, $correctOptionIds[$qId] ?? []);

                } elseif ($type === 'fill_blank' || $type === 'fill_in_blank') {
                    $opts = $allOptionsByQ[$qId] ?? [];
                    $correctTexts = array_values(array_filter(array_map(fn($o) =>
                        $o['is_correct'] ? strtolower(trim($o['option_text'])) : null, $opts)));

                    if (is_array($userAns) && count($correctTexts) > 0) {
                        $userTexts = array_map(fn($t) => strtolower(trim($t)), $userAns);

                        // Count actual [BLANK] placeholders in the question text
                        preg_match_all('/\[BLANK\]|_{3,}/i', $questionText, $blankMatches);
                        $actualBlankCount = count($blankMatches[0]);

                        if ($actualBlankCount <= 1 && count($correctTexts) > 1) {
                            // Multiple alternatives for ONE blank (e.g. "increase" or "rise")
                            $firstAnswer = trim((string)($userTexts[0] ?? ''));
                            $isCorrect = $firstAnswer !== '' && in_array($firstAnswer, $correctTexts);
                        } else {
                            // Position-based: each blank must match aligned correct answer
                            $correct = 0;
                            foreach ($correctTexts as $i => $ct) {
                                if (isset($userTexts[$i]) && $userTexts[$i] === $ct) $correct++;
                            }
                            $isCorrect = $correct === count($correctTexts);
                        }
                    }

                } elseif ($type === 'matching') {
                    // userAns: { option_id: selected_match_text }
                    if (is_array($userAns) && count($userAns) > 0) {
                        $opts = $allOptionsByQ[$qId] ?? [];
                        $optMap = [];
                        foreach ($opts as $o) { $optMap[(int)$o['id']] = $o['match_text']; }
                        
                        $correctMatches = 0;
                        $totalPairs = count($optMap);
                        foreach ($userAns as $oId => $selectedText) {
                            $userT = strtolower(trim((string)$selectedText));
                            $correctT = isset($optMap[(int)$oId]) ? strtolower(trim((string)$optMap[(int)$oId])) : '';
                            if ($correctT !== '' && $userT === $correctT) {
                                $correctMatches++;
                            }
                        }
                        // Question is correct only if ALL pairs are correct
                        $isCorrect = ($totalPairs > 0 && $correctMatches === $totalPairs);
                    }

                } elseif ($type === 'dictation') {
                    $opts = $allOptionsByQ[$qId] ?? [];
                    $correctAnswer = '';
                    foreach ($opts as $o) {
                        if ($o['is_correct']) { $correctAnswer = strtolower(trim($o['option_text'])); break; }
                    }
                    if ($correctAnswer && is_string($userAns)) {
                        similar_text($correctAnswer, strtolower(trim($userAns)), $pct);
                        $isCorrect = $pct >= 80;
                    }
                }

            if ($isCorrect) $correctCount++;
            $questionResults[$qId] = $isCorrect;
        }

        $score = (float)$correctCount;
        $status = $hasWriting ? 'pending_grading' : 'completed';
    }

} catch (PDOException $e) {
    $score = 0.0;
    $status = 'error';
}

// === Save: Keep GREATEST score when retrying (unless has_writing → locked above) ===
try {
    $stmt = $pdo->prepare("
        INSERT INTO quiz_submissions (student_id, quiz_id, score, status, answers_json, submitted_at)
        VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            score       = VALUES(score),
            status      = IF(status = 'pending_grading', status, VALUES(status)),
            answers_json = VALUES(answers_json),
            submitted_at = NOW()
    ");
    $stmt->execute([$studentId, $quizId, $score, $status, $answersJson]);

    echo json_encode([
        'status'      => 'success',
        'message'     => 'Nộp bài thành công',
        'score'       => $score,
        'correct'     => $correctCount,
        'total'       => $totalQuestions,
        'has_writing' => $hasWriting,
        'results'     => $questionResults
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Lỗi khi lưu bài thi: ' . $e->getMessage()]);
}
?>
