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
require_once __DIR__ . '/../../utils/QuizAccess.php';

$authUser = JwtHelper::requireAuth();
if (($authUser['role'] ?? '') !== 'student') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Chỉ học viên mới được nộp bài quiz.']);
    exit;
}

$studentId = (int) ($authUser['sub'] ?? 0);

function quizSubmissionResponse(int $code, array $payload): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function resolveQuizContextOrAbort(PDO $pdo, int $studentId, int $quizId, ?int $preferredClassId = null): array
{
    $context = resolveStudentQuizContext($pdo, $studentId, $quizId, $preferredClassId);
    if ($context) {
        return $context;
    }

    $exists = quizExists($pdo, $quizId);

    quizSubmissionResponse(
        $exists ? 403 : 404,
        [
            'status' => 'error',
            'message' => $exists
                ? 'Bạn không thuộc lớp được phép nộp quiz này.'
                : 'Quiz không tồn tại.',
        ]
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $quizId = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : 0;
    $preferredClassId = isset($_GET['class_id']) ? (int) $_GET['class_id'] : null;

    if ($quizId <= 0) {
        quizSubmissionResponse(400, ['status' => 'error', 'message' => 'Missing quiz_id']);
    }

    try {
        $context = resolveQuizContextOrAbort($pdo, $studentId, $quizId, $preferredClassId);

        $stmt = $pdo->prepare("
            SELECT class_id, score, status, answers_json, feedback, rubric_data, submitted_at
            FROM quiz_submissions
            WHERE student_id = ? AND quiz_id = ?
            ORDER BY (class_id = ?) DESC, submitted_at DESC
            LIMIT 1
        ");
        $stmt->execute([$studentId, $quizId, (int) $context['class_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            quizSubmissionResponse(200, [
                'status' => 'success',
                'submitted' => true,
                'class_id' => (int) $row['class_id'],
                'score' => $row['score'] !== null ? (float) $row['score'] : null,
                'sub_status' => $row['status'],
                'answers_json' => $row['answers_json'],
                'feedback' => $row['feedback'] ?? '',
                'rubric_data' => $row['rubric_data'] ?? null,
                'submitted_at' => $row['submitted_at'],
            ]);
        }

        quizSubmissionResponse(200, ['status' => 'success', 'submitted' => false, 'class_id' => (int) $context['class_id']]);
    } catch (PDOException $e) {
        quizSubmissionResponse(500, ['status' => 'error', 'message' => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    quizSubmissionResponse(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    quizSubmissionResponse(400, ['status' => 'error', 'message' => 'Invalid JSON body']);
}

$quizId = isset($input['quiz_id']) ? (int) $input['quiz_id'] : 0;
$preferredClassId = isset($input['class_id']) ? (int) $input['class_id'] : null;
if ($quizId <= 0) {
    quizSubmissionResponse(400, ['status' => 'error', 'message' => 'Missing quiz_id']);
}

$userAnswers = is_array($input['answers'] ?? null) ? $input['answers'] : [];
$answersJson = json_encode($userAnswers, JSON_UNESCAPED_UNICODE);

try {
    $context = resolveQuizContextOrAbort($pdo, $studentId, $quizId, $preferredClassId);
    $classId = (int) $context['class_id'];

    $stmtCheckWriting = $pdo->prepare("
        SELECT COUNT(*)
        FROM questions q
        WHERE q.quiz_id = ? AND q.question_type IN ('writing', 'essay')
    ");
    $stmtCheckWriting->execute([$quizId]);
    $hasWritingQuestions = (int) $stmtCheckWriting->fetchColumn() > 0;

    if ($hasWritingQuestions) {
        $stmtExisting = $pdo->prepare("
            SELECT id
            FROM quiz_submissions
            WHERE student_id = ? AND quiz_id = ? AND class_id = ?
            LIMIT 1
        ");
        $stmtExisting->execute([$studentId, $quizId, $classId]);
        if ($stmtExisting->fetch()) {
            quizSubmissionResponse(200, [
                'status' => 'error',
                'message' => 'Bài tự luận chỉ được nộp một lần.',
                'locked' => true,
            ]);
        }
    }

    $stmtQuestions = $pdo->prepare("
        SELECT id, question_type, question_text
        FROM questions
        WHERE quiz_id = ?
    ");
    $stmtQuestions->execute([$quizId]);
    $questions = $stmtQuestions->fetchAll(PDO::FETCH_ASSOC);

    $totalQuestions = count($questions);
    $score = 0.0;
    $correctCount = 0;
    $hasWriting = false;
    $questionResults = [];
    $status = 'completed';

    if ($totalQuestions > 0) {
        $questionIds = array_column($questions, 'id');
        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));

        $stmtOptions = $pdo->prepare("
            SELECT id, question_id, is_correct, match_text, option_text
            FROM question_options
            WHERE question_id IN ($placeholders)
            ORDER BY question_id, id ASC
        ");
        $stmtOptions->execute($questionIds);
        $allOptions = $stmtOptions->fetchAll(PDO::FETCH_ASSOC);

        $correctOptionIds = [];
        $allOptionsByQuestion = [];
        foreach ($allOptions as $option) {
            $questionId = (int) $option['question_id'];
            $allOptionsByQuestion[$questionId][] = $option;
            if ((int) $option['is_correct'] === 1) {
                $correctOptionIds[$questionId][] = (int) $option['id'];
            }
        }

        foreach ($questions as $question) {
            $questionId = (int) $question['id'];
            $type = (string) $question['question_type'];
            $questionText = (string) ($question['question_text'] ?? '');
            $userAnswer = $userAnswers[(string) $questionId] ?? $userAnswers[$questionId] ?? null;

            if ($type === 'writing' || $type === 'essay') {
                $hasWriting = true;
                $questionResults[$questionId] = 'pending';
                continue;
            }

            if ($userAnswer === null || $userAnswer === '' || $userAnswer === []) {
                $questionResults[$questionId] = 'skipped';
                continue;
            }

            $isCorrect = false;

            if ($type === 'multiple_choice' || $type === 'true_false') {
                $selectedId = (int) $userAnswer;
                $isCorrect = in_array($selectedId, $correctOptionIds[$questionId] ?? [], true);
            } elseif ($type === 'fill_blank' || $type === 'fill_in_blank') {
                $options = $allOptionsByQuestion[$questionId] ?? [];
                $correctTexts = array_values(array_filter(array_map(
                    static fn(array $option): ?string => (int) $option['is_correct'] === 1
                        ? strtolower(trim((string) $option['option_text']))
                        : null,
                    $options
                )));

                if (is_array($userAnswer) && count($correctTexts) > 0) {
                    $userTexts = array_map(static fn($value): string => strtolower(trim((string) $value)), $userAnswer);

                    preg_match_all('/\[BLANK\]|_{3,}/i', $questionText, $blankMatches);
                    $actualBlankCount = count($blankMatches[0]);

                    if ($actualBlankCount <= 1 && count($correctTexts) > 1) {
                        $firstAnswer = trim((string) ($userTexts[0] ?? ''));
                        $isCorrect = $firstAnswer !== '' && in_array($firstAnswer, $correctTexts, true);
                    } else {
                        $matched = 0;
                        foreach ($correctTexts as $index => $correctText) {
                            if (isset($userTexts[$index]) && $userTexts[$index] === $correctText) {
                                $matched++;
                            }
                        }
                        $isCorrect = $matched === count($correctTexts);
                    }
                }
            } elseif ($type === 'matching') {
                if (is_array($userAnswer) && count($userAnswer) > 0) {
                    $options = $allOptionsByQuestion[$questionId] ?? [];
                    $optionMap = [];
                    foreach ($options as $option) {
                        $optionMap[(int) $option['id']] = strtolower(trim((string) ($option['match_text'] ?? '')));
                    }

                    $correctMatches = 0;
                    $totalPairs = count($optionMap);
                    foreach ($userAnswer as $optionId => $selectedText) {
                        $userText = strtolower(trim((string) $selectedText));
                        $correctText = $optionMap[(int) $optionId] ?? '';
                        if ($correctText !== '' && $userText === $correctText) {
                            $correctMatches++;
                        }
                    }

                    $isCorrect = $totalPairs > 0 && $correctMatches === $totalPairs;
                }
            } elseif ($type === 'dictation') {
                $options = $allOptionsByQuestion[$questionId] ?? [];
                $correctAnswer = '';
                foreach ($options as $option) {
                    if ((int) $option['is_correct'] === 1) {
                        $correctAnswer = strtolower(trim((string) $option['option_text']));
                        break;
                    }
                }

                if ($correctAnswer !== '' && is_string($userAnswer)) {
                    similar_text($correctAnswer, strtolower(trim($userAnswer)), $pct);
                    $isCorrect = $pct >= 80;
                }
            }

            if ($isCorrect) {
                $correctCount++;
            }
            $questionResults[$questionId] = $isCorrect;
        }

        $score = (float) $correctCount;
        $status = $hasWriting ? 'pending_grading' : 'completed';
    }

    $stmtSave = $pdo->prepare("
        INSERT INTO quiz_submissions (student_id, quiz_id, class_id, score, status, answers_json, submitted_at, feedback, rubric_data)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NULL, NULL)
        ON DUPLICATE KEY UPDATE
            score = VALUES(score),
            status = VALUES(status),
            answers_json = VALUES(answers_json),
            feedback = NULL,
            rubric_data = NULL,
            submitted_at = NOW()
    ");
    $stmtSave->execute([$studentId, $quizId, $classId, $score, $status, $answersJson]);

    quizSubmissionResponse(200, [
        'status' => 'success',
        'message' => 'Nộp bài thành công',
        'class_id' => $classId,
        'score' => $score,
        'correct' => $correctCount,
        'total' => $totalQuestions,
        'has_writing' => $hasWriting,
        'results' => $questionResults,
    ]);
} catch (PDOException $e) {
    quizSubmissionResponse(500, ['status' => 'error', 'message' => 'Lỗi khi lưu bài thi: ' . $e->getMessage()]);
}
