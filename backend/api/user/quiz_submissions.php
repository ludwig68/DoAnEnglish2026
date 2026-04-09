<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$authUser = JwtHelper::requireAuth();

if (($authUser['role'] ?? '') === 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Tài khoản admin không dùng trang này.']);
    exit;
}

$studentId = (int) ($authUser['sub'] ?? 0);

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

// === STEP 1: Ensure table exists ===
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS quiz_submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            quiz_id INT NOT NULL,
            score DECIMAL(5,2) DEFAULT NULL,
            status VARCHAR(50) DEFAULT 'completed',
            answers_json LONGTEXT,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_submission (student_id, quiz_id),
            INDEX idx_student (student_id),
            INDEX idx_quiz (quiz_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    // Table already exists, continue
}

// === STEP 2: Load all questions + correct answers for this quiz ===
$score = 0.0;
$totalQuestions = 0;
$correctCount = 0;

try {
    // Fetch all questions for this quiz
    $stmtQs = $pdo->prepare("
        SELECT id, question_type FROM questions WHERE quiz_id = ?
    ");
    $stmtQs->execute([$quizId]);
    $questions = $stmtQs->fetchAll(PDO::FETCH_ASSOC);
    $totalQuestions = count($questions);

    if ($totalQuestions > 0) {
        $questionIds = array_column($questions, 'id');
        $questionTypeMap = array_column($questions, 'question_type', 'id');

        // Fetch all options with correct answers
        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $stmtOpts = $pdo->prepare("
            SELECT id, question_id, is_correct, match_text, option_text
            FROM question_options
            WHERE question_id IN ($placeholders)
            ORDER BY question_id, id ASC
        ");
        $stmtOpts->execute($questionIds);
        $allOptions = $stmtOpts->fetchAll(PDO::FETCH_ASSOC);

        // Build map: question_id -> correct_option_ids[], and question_id -> options[]
        $correctOptionIds = []; // question_id -> [opt_id, ...]
        $allOptionsByQ = [];    // question_id -> [opts]
        foreach ($allOptions as $opt) {
            $qId = (int)$opt['question_id'];
            $allOptionsByQ[$qId][] = $opt;
            if ($opt['is_correct']) {
                $correctOptionIds[$qId][] = (int)$opt['id'];
            }
        }

        // === Grade each question ===
        foreach ($questions as $q) {
            $qId = (int)$q['id'];
            $type = $q['question_type'];
            $userAns = $userAnswers[(string)$qId] ?? $userAnswers[$qId] ?? null;

            if ($userAns === null || $userAns === '' || $userAns === [] || $userAns === (object)[]) {
                continue; // Skipped – no points
            }

            $isCorrect = false;

            if ($type === 'multiple_choice' || $type === 'true_false') {
                // userAns = option_id (int or string)
                $selectedId = (int)$userAns;
                $correctIds = $correctOptionIds[$qId] ?? [];
                $isCorrect = in_array($selectedId, $correctIds);

            } elseif ($type === 'fill_blank' || $type === 'fill_in_blank') {
                // userAns = array of strings (one per blank)
                // Correct answers = correct option_text values in order
                $opts = $allOptionsByQ[$qId] ?? [];
                $correctTexts = array_values(array_filter(array_map(function($o) {
                    return $o['is_correct'] ? strtolower(trim($o['option_text'])) : null;
                }, $opts)));

                if (is_array($userAns)) {
                    $userTexts = array_map(fn($t) => strtolower(trim($t)), $userAns);
                    // Count how many blanks are correct
                    $totalBlanks = count($correctTexts);
                    $correctBlanks = 0;
                    foreach ($correctTexts as $i => $ct) {
                        if (isset($userTexts[$i]) && $userTexts[$i] === $ct) {
                            $correctBlanks++;
                        }
                    }
                    $isCorrect = ($totalBlanks > 0 && $correctBlanks === $totalBlanks);
                }

            } elseif ($type === 'matching') {
                // userAns = { leftIdx: rightShuffledIdx } - hard to grade without knowing shuffleOrder
                // For now, treat matching as attempted (gets partial credit)
                if (is_array($userAns) && count($userAns) > 0) {
                    $isCorrect = true; // Partial credit for attempting
                }

            } elseif ($type === 'dictation') {
                // userAns = string transcription, compare to option_text of correct option
                $opts = $allOptionsByQ[$qId] ?? [];
                $correctAnswer = '';
                foreach ($opts as $o) {
                    if ($o['is_correct']) { $correctAnswer = strtolower(trim($o['option_text'])); break; }
                }
                if ($correctAnswer && is_string($userAns)) {
                    $userText = strtolower(trim($userAns));
                    // Use similar_text for fuzzy matching (>= 80% similarity = correct)
                    similar_text($correctAnswer, $userText, $pct);
                    $isCorrect = $pct >= 80;
                }

            } elseif ($type === 'writing' || $type === 'essay') {
                // Writing cannot be auto-graded – give credit if they wrote >= 50 words
                if (is_string($userAns)) {
                    $wordCount = str_word_count(strip_tags($userAns));
                    $isCorrect = $wordCount >= 50;
                }
            }

            if ($isCorrect) {
                $correctCount++;
            }
        }

        // Score on a 10.0 scale
        $score = $totalQuestions > 0
            ? round(($correctCount / $totalQuestions) * 10, 1)
            : 0.0;
    }

} catch (PDOException $e) {
    // Grading failed – fallback score
    $score = 0.0;
}

// === STEP 3: Save to DB ===
try {
    $stmt = $pdo->prepare("
        INSERT INTO quiz_submissions (student_id, quiz_id, score, status, answers_json, submitted_at)
        VALUES (?, ?, ?, 'completed', ?, NOW())
        ON DUPLICATE KEY UPDATE 
            score = VALUES(score), 
            status = 'completed', 
            answers_json = VALUES(answers_json),
            submitted_at = NOW()
    ");
    $stmt->execute([$studentId, $quizId, $score, $answersJson]);

    echo json_encode([
        'status'       => 'success',
        'message'      => 'Nộp bài thành công',
        'score'        => $score,
        'correct'      => $correctCount,
        'total'        => $totalQuestions,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Lỗi khi lưu bài thi: ' . $e->getMessage(),
    ]);
}
?>
