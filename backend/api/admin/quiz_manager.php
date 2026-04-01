<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

JwtHelper::requireAuth('admin');

const QUIZ_QUESTION_TYPES = ['matching', 'multiple_choice', 'fill_blank', 'dictation', 'writing'];

$method = $_SERVER['REQUEST_METHOD'];

function jsonResponse(int $statusCode, array $body): void
{
    http_response_code($statusCode);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function normalizeOptionalString(mixed $value): ?string
{
    $normalized = trim((string) ($value ?? ''));
    return $normalized !== '' ? $normalized : null;
}

function validatePositiveId(mixed $value, string $label): int
{
    $requiredCheck = Validator::isNotEmpty((string) ($value ?? ''));
    if ($requiredCheck !== true) {
        throw new InvalidArgumentException($label . ': ' . $requiredCheck);
    }

    $intCheck = Validator::checkIntOrDecimal($value, 'int');
    if ($intCheck !== true) {
        throw new InvalidArgumentException($label . ': ' . $intCheck);
    }

    $positiveCheck = Validator::checkPositiveNegative((int) $value, 'positive');
    if ($positiveCheck !== true) {
        throw new InvalidArgumentException($label . ': ' . $positiveCheck);
    }

    return (int) $value;
}

function validateOptionalLength(?string $value, int $min, int $max, string $label): ?string
{
    if ($value === null) {
        return null;
    }

    $lengthCheck = Validator::checkStringLength($value, $min, $max);
    if ($lengthCheck !== true) {
        throw new InvalidArgumentException($label . ': ' . $lengthCheck);
    }

    return $value;
}

function lessonExists(PDO $pdo, int $lessonId): bool
{
    $stmt = $pdo->prepare("SELECT id FROM lessons WHERE id = ? LIMIT 1");
    $stmt->execute([$lessonId]);
    return (bool) $stmt->fetch();
}

function normalizeOptionPayload(array $option, string $questionType, int $optionIndex): array
{
    $optionId = isset($option['id']) && $option['id'] !== '' ? (int) $option['id'] : null;
    $optionText = trim((string) ($option['option_text'] ?? ''));
    $matchText = normalizeOptionalString($option['match_text'] ?? null);
    $isCorrect = !empty($option['is_correct']) ? 1 : 0;

    switch ($questionType) {
        case 'multiple_choice':
            $textCheck = Validator::checkStringLength($optionText, 1, 255);
            if ($textCheck !== true) {
                throw new InvalidArgumentException("Đáp án lựa chọn #" . ($optionIndex + 1) . ': ' . $textCheck);
            }

            return [
                'id' => $optionId,
                'option_text' => $optionText,
                'match_text' => null,
                'is_correct' => $isCorrect,
            ];

        case 'matching':
            $leftCheck = Validator::checkStringLength($optionText, 1, 255);
            if ($leftCheck !== true) {
                throw new InvalidArgumentException("Vế trái cặp nối #" . ($optionIndex + 1) . ': ' . $leftCheck);
            }

            $rightCheck = Validator::checkStringLength((string) $matchText, 1, 255);
            if ($rightCheck !== true) {
                throw new InvalidArgumentException("Vế phải cặp nối #" . ($optionIndex + 1) . ': ' . $rightCheck);
            }

            return [
                'id' => $optionId,
                'option_text' => $optionText,
                'match_text' => $matchText,
                'is_correct' => 1,
            ];

        case 'fill_blank':
        case 'dictation':
            $answerCheck = Validator::checkStringLength($optionText, 1, 255);
            if ($answerCheck !== true) {
                throw new InvalidArgumentException("Đáp án câu #" . ($optionIndex + 1) . ': ' . $answerCheck);
            }

            return [
                'id' => $optionId,
                'option_text' => $optionText,
                'match_text' => null,
                'is_correct' => 1,
            ];

        default:
            return [
                'id' => $optionId,
                'option_text' => $optionText,
                'match_text' => $matchText,
                'is_correct' => $isCorrect,
            ];
    }
}

function normalizeQuestionPayload(array $question, int $questionIndex): array
{
    $questionId = isset($question['id']) && $question['id'] !== '' ? (int) $question['id'] : null;
    $questionType = trim((string) ($question['question_type'] ?? ''));
    $questionText = trim((string) ($question['question_text'] ?? ''));
    $audioUrl = normalizeOptionalString($question['audio_url'] ?? null);
    $hint = normalizeOptionalString($question['hint'] ?? null);
    $explanation = normalizeOptionalString($question['explanation'] ?? null);
    $options = array_values(array_filter($question['options'] ?? [], 'is_array'));

    if (!in_array($questionType, QUIZ_QUESTION_TYPES, true)) {
        throw new InvalidArgumentException("Câu hỏi #" . ($questionIndex + 1) . ': Loại câu hỏi không hợp lệ.');
    }

    $textCheck = Validator::checkStringLength($questionText, 3, 10000);
    if ($textCheck !== true) {
        throw new InvalidArgumentException("Nội dung câu hỏi #" . ($questionIndex + 1) . ': ' . $textCheck);
    }

    $audioUrl = validateOptionalLength($audioUrl, 5, 255, "Audio câu hỏi #" . ($questionIndex + 1));
    $hint = validateOptionalLength($hint, 2, 255, "Gợi ý câu hỏi #" . ($questionIndex + 1));
    $explanation = validateOptionalLength($explanation, 2, 5000, "Giải thích câu hỏi #" . ($questionIndex + 1));

    $normalizedOptions = [];

    foreach ($options as $optionIndex => $option) {
        $normalizedOptions[] = normalizeOptionPayload($option, $questionType, $optionIndex);
    }

    if ($questionType === 'multiple_choice') {
        if (count($normalizedOptions) < 2) {
            throw new InvalidArgumentException("Câu hỏi #" . ($questionIndex + 1) . ': Trắc nghiệm phải có ít nhất 2 lựa chọn.');
        }

        $correctCount = count(array_filter($normalizedOptions, fn ($item) => (int) $item['is_correct'] === 1));
        if ($correctCount < 1) {
            throw new InvalidArgumentException("Câu hỏi #" . ($questionIndex + 1) . ': Cần chọn ít nhất 1 đáp án đúng.');
        }
    }

    if ($questionType === 'matching' && count($normalizedOptions) < 1) {
        throw new InvalidArgumentException("Câu hỏi #" . ($questionIndex + 1) . ': Nối cặp phải có ít nhất 1 cặp dữ liệu.');
    }

    if (in_array($questionType, ['fill_blank', 'dictation'], true)) {
        if (count($normalizedOptions) !== 1) {
            throw new InvalidArgumentException("Câu hỏi #" . ($questionIndex + 1) . ': Điền từ hoặc chép chính tả chỉ được có 1 đáp án.');
        }

        if ($questionType === 'dictation' && $audioUrl === null) {
            throw new InvalidArgumentException("Câu hỏi #" . ($questionIndex + 1) . ': Chép chính tả phải có audio URL.');
        }
    }

    if ($questionType === 'writing') {
        $normalizedOptions = [];
    }

    return [
        'id' => $questionId,
        'question_type' => $questionType,
        'question_text' => $questionText,
        'audio_url' => $audioUrl,
        'hint' => $hint,
        'explanation' => $explanation,
        'order_num' => $questionIndex + 1,
        'options' => array_map(function ($option, $index) {
            $option['order_num'] = $index + 1;
            return $option;
        }, $normalizedOptions, array_keys($normalizedOptions)),
    ];
}

function validateQuizPayload(PDO $pdo, array $payload): array
{
    $title = trim((string) ($payload['title'] ?? ''));
    $lessonId = validatePositiveId($payload['lesson_id'] ?? null, 'Bài học');
    $category = normalizeOptionalString($payload['category'] ?? null);
    $questions = array_values(array_filter($payload['questions'] ?? [], 'is_array'));

    $titleCheck = Validator::checkStringLength($title, 3, 255);
    if ($titleCheck !== true) {
        throw new InvalidArgumentException('Tiêu đề quiz: ' . $titleCheck);
    }

    $category = validateOptionalLength($category, 2, 255, 'Danh mục quiz');

    if (!lessonExists($pdo, $lessonId)) {
        throw new InvalidArgumentException('Bài học được chọn không tồn tại.');
    }

    if (count($questions) === 0) {
        throw new InvalidArgumentException('Quiz phải có ít nhất 1 câu hỏi.');
    }

    $normalizedQuestions = [];
    foreach ($questions as $index => $question) {
        $normalizedQuestions[] = normalizeQuestionPayload($question, $index);
    }

    return [
        'title' => $title,
        'lesson_id' => $lessonId,
        // Schema hiện tại không có cột category, nên dùng description để lưu.
        'category' => $category,
        'questions' => $normalizedQuestions,
    ];
}

function fetchQuiz(PDO $pdo, int $quizId): ?array
{
    $stmtQuiz = $pdo->prepare("
        SELECT
            q.id,
            q.lesson_id,
            q.title,
            q.description,
            q.created_at,
            l.title AS lesson_title,
            l.order_number AS lesson_order_number,
            c.id AS course_id,
            c.title AS course_title
        FROM quizzes q
        INNER JOIN lessons l ON l.id = q.lesson_id
        INNER JOIN courses c ON c.id = l.course_id
        WHERE q.id = ?
        LIMIT 1
    ");
    $stmtQuiz->execute([$quizId]);
    $quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        return null;
    }

    $stmtQuestions = $pdo->prepare("
        SELECT
            id,
            quiz_id,
            question_type,
            question_text,
            audio_url,
            hint,
            explanation,
            order_num
        FROM questions
        WHERE quiz_id = ?
        ORDER BY order_num ASC, id ASC
    ");
    $stmtQuestions->execute([$quizId]);
    $questions = $stmtQuestions->fetchAll(PDO::FETCH_ASSOC);

    $questionIds = array_map(fn ($item) => (int) $item['id'], $questions);
    $optionMap = [];

    if (!empty($questionIds)) {
        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $stmtOptions = $pdo->prepare("
            SELECT
                id,
                question_id,
                option_text,
                match_text,
                is_correct,
                order_num
            FROM question_options
            WHERE question_id IN ($placeholders)
            ORDER BY order_num ASC, id ASC
        ");
        $stmtOptions->execute($questionIds);

        foreach ($stmtOptions->fetchAll(PDO::FETCH_ASSOC) as $option) {
            $questionId = (int) $option['question_id'];
            if (!isset($optionMap[$questionId])) {
                $optionMap[$questionId] = [];
            }

            $optionMap[$questionId][] = [
                'id' => (int) $option['id'],
                'option_text' => $option['option_text'],
                'match_text' => $option['match_text'],
                'is_correct' => (bool) $option['is_correct'],
                'order_num' => (int) $option['order_num'],
            ];
        }
    }

    $quiz['questions'] = array_map(function ($question) use ($optionMap) {
        $questionId = (int) $question['id'];
        return [
            'id' => $questionId,
            'quiz_id' => (int) $question['quiz_id'],
            'question_type' => $question['question_type'],
            'question_text' => $question['question_text'],
            'audio_url' => $question['audio_url'],
            'hint' => $question['hint'],
            'explanation' => $question['explanation'],
            'order_num' => (int) $question['order_num'],
            'options' => $optionMap[$questionId] ?? [],
        ];
    }, $questions);

    return [
        'id' => (int) $quiz['id'],
        'lesson_id' => (int) $quiz['lesson_id'],
        'title' => $quiz['title'],
        'category' => $quiz['description'],
        'description' => $quiz['description'],
        'total_points' => count($quiz['questions']),
        'created_at' => $quiz['created_at'],
        'lesson_title' => $quiz['lesson_title'],
        'lesson_order_number' => (int) $quiz['lesson_order_number'],
        'course_id' => (int) $quiz['course_id'],
        'course_title' => $quiz['course_title'],
        'questions' => $quiz['questions'],
    ];
}

function fetchBuilderMeta(PDO $pdo): array
{
    $stmtLessons = $pdo->query("
        SELECT
            l.id,
            l.title,
            l.order_number,
            l.course_id,
            c.title AS course_title
        FROM lessons l
        INNER JOIN courses c ON c.id = l.course_id
        ORDER BY c.title ASC, l.order_number ASC, l.id ASC
    ");

    $stmtQuizzes = $pdo->query("
        SELECT
            q.id,
            q.lesson_id,
            q.title,
            q.description,
            q.created_at,
            l.title AS lesson_title,
            l.order_number AS lesson_order_number,
            c.title AS course_title,
            COUNT(ques.id) AS question_count
        FROM quizzes q
        INNER JOIN lessons l ON l.id = q.lesson_id
        INNER JOIN courses c ON c.id = l.course_id
        LEFT JOIN questions ques ON ques.quiz_id = q.id
        GROUP BY q.id, q.lesson_id, q.title, q.description, q.created_at, l.title, l.order_number, c.title
        ORDER BY q.id DESC
    ");

    return [
        'lessons' => array_map(function ($lesson) {
            return [
                'id' => (int) $lesson['id'],
                'title' => $lesson['title'],
                'order_number' => (int) $lesson['order_number'],
                'course_id' => (int) $lesson['course_id'],
                'course_title' => $lesson['course_title'],
                'label' => $lesson['course_title'] . ' - Bài ' . $lesson['order_number'] . ': ' . $lesson['title'],
            ];
        }, $stmtLessons->fetchAll(PDO::FETCH_ASSOC)),
        'quizzes' => array_map(function ($quiz) {
            return [
                'id' => (int) $quiz['id'],
                'lesson_id' => (int) $quiz['lesson_id'],
                'title' => $quiz['title'],
                'category' => $quiz['description'],
                'lesson_title' => $quiz['lesson_title'],
                'lesson_order_number' => (int) $quiz['lesson_order_number'],
                'course_title' => $quiz['course_title'],
                'question_count' => (int) $quiz['question_count'],
                'total_points' => (int) $quiz['question_count'],
                'created_at' => $quiz['created_at'],
            ];
        }, $stmtQuizzes->fetchAll(PDO::FETCH_ASSOC)),
    ];
}

function saveQuestionOptions(PDO $pdo, int $questionId, array $options): void
{
    $stmtExisting = $pdo->prepare("SELECT id FROM question_options WHERE question_id = ?");
    $stmtExisting->execute([$questionId]);
    $existingOptionIds = array_map('intval', array_column($stmtExisting->fetchAll(PDO::FETCH_ASSOC), 'id'));
    $keepOptionIds = [];

    $stmtInsert = $pdo->prepare("
        INSERT INTO question_options (question_id, option_text, match_text, is_correct, order_num)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtUpdate = $pdo->prepare("
        UPDATE question_options
        SET option_text = ?, match_text = ?, is_correct = ?, order_num = ?
        WHERE id = ? AND question_id = ?
    ");

    foreach ($options as $optionIndex => $option) {
        $optionId = isset($option['id']) && $option['id'] ? (int) $option['id'] : null;
        $orderNum = $optionIndex + 1;

        if ($optionId !== null) {
            if (!in_array($optionId, $existingOptionIds, true)) {
                throw new InvalidArgumentException('Phát hiện đáp án không thuộc câu hỏi đang chỉnh sửa.');
            }

            $stmtUpdate->execute([
                $option['option_text'],
                $option['match_text'],
                $option['is_correct'],
                $orderNum,
                $optionId,
                $questionId,
            ]);
            $keepOptionIds[] = $optionId;
            continue;
        }

        $stmtInsert->execute([
            $questionId,
            $option['option_text'],
            $option['match_text'],
            $option['is_correct'],
            $orderNum,
        ]);
        $keepOptionIds[] = (int) $pdo->lastInsertId();
    }

    $staleOptionIds = array_values(array_diff($existingOptionIds, $keepOptionIds));
    if (!empty($staleOptionIds)) {
        $placeholders = implode(',', array_fill(0, count($staleOptionIds), '?'));
        $params = array_merge([$questionId], $staleOptionIds);
        $stmtDelete = $pdo->prepare("DELETE FROM question_options WHERE question_id = ? AND id IN ($placeholders)");
        $stmtDelete->execute($params);
    }
}

function saveQuizTree(PDO $pdo, int $quizId, array $quizPayload): void
{
    $stmtExistingQuestions = $pdo->prepare("SELECT id FROM questions WHERE quiz_id = ?");
    $stmtExistingQuestions->execute([$quizId]);
    $existingQuestionIds = array_map('intval', array_column($stmtExistingQuestions->fetchAll(PDO::FETCH_ASSOC), 'id'));
    $keepQuestionIds = [];

    $stmtInsertQuestion = $pdo->prepare("
        INSERT INTO questions (quiz_id, question_type, question_text, audio_url, hint, explanation, order_num)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtUpdateQuestion = $pdo->prepare("
        UPDATE questions
        SET question_type = ?, question_text = ?, audio_url = ?, hint = ?, explanation = ?, order_num = ?
        WHERE id = ? AND quiz_id = ?
    ");

    foreach ($quizPayload['questions'] as $questionIndex => $question) {
        $questionId = isset($question['id']) && $question['id'] ? (int) $question['id'] : null;
        $orderNum = $questionIndex + 1;

        if ($questionId !== null) {
            if (!in_array($questionId, $existingQuestionIds, true)) {
                throw new InvalidArgumentException('Phát hiện câu hỏi không thuộc quiz đang chỉnh sửa.');
            }

            $stmtUpdateQuestion->execute([
                $question['question_type'],
                $question['question_text'],
                $question['audio_url'],
                $question['hint'],
                $question['explanation'],
                $orderNum,
                $questionId,
                $quizId,
            ]);
            $keepQuestionIds[] = $questionId;
        } else {
            $stmtInsertQuestion->execute([
                $quizId,
                $question['question_type'],
                $question['question_text'],
                $question['audio_url'],
                $question['hint'],
                $question['explanation'],
                $orderNum,
            ]);
            $questionId = (int) $pdo->lastInsertId();
            $keepQuestionIds[] = $questionId;
        }

        saveQuestionOptions($pdo, $questionId, $question['options']);
    }

    $staleQuestionIds = array_values(array_diff($existingQuestionIds, $keepQuestionIds));
    if (!empty($staleQuestionIds)) {
        $placeholders = implode(',', array_fill(0, count($staleQuestionIds), '?'));
        $params = array_merge([$quizId], $staleQuestionIds);
        $stmtDeleteQuestions = $pdo->prepare("DELETE FROM questions WHERE quiz_id = ? AND id IN ($placeholders)");
        $stmtDeleteQuestions->execute($params);
    }
}

try {
    switch ($method) {
        case 'GET':
            $quizId = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : (int) ($_GET['id'] ?? 0);

            if ($quizId > 0) {
                $quiz = fetchQuiz($pdo, $quizId);
                if ($quiz === null) {
                    jsonResponse(404, ['status' => 'error', 'message' => 'Không tìm thấy quiz cần tải.']);
                }

                jsonResponse(200, ['status' => 'success', 'data' => ['quiz' => $quiz]]);
            }

            jsonResponse(200, ['status' => 'success', 'data' => fetchBuilderMeta($pdo)]);
            break;

        case 'POST':
        case 'PUT':
            $payload = json_decode(file_get_contents("php://input"), true) ?? [];
            $normalizedPayload = validateQuizPayload($pdo, $payload);

            $quizId = $method === 'PUT'
                ? (isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0))
                : 0;

            if ($method === 'PUT' && $quizId <= 0) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Quiz cần cập nhật không hợp lệ.']);
            }

            $pdo->beginTransaction();

            if ($method === 'POST') {
                $stmtInsertQuiz = $pdo->prepare("
                    INSERT INTO quizzes (lesson_id, title, description)
                    VALUES (?, ?, ?)
                ");
                $stmtInsertQuiz->execute([
                    $normalizedPayload['lesson_id'],
                    $normalizedPayload['title'],
                    $normalizedPayload['category'],
                ]);
                $quizId = (int) $pdo->lastInsertId();
            } else {
                $stmtCheckQuiz = $pdo->prepare("SELECT id FROM quizzes WHERE id = ? LIMIT 1");
                $stmtCheckQuiz->execute([$quizId]);
                if (!$stmtCheckQuiz->fetch()) {
                    $pdo->rollBack();
                    jsonResponse(404, ['status' => 'error', 'message' => 'Quiz cần cập nhật không tồn tại.']);
                }

                $stmtUpdateQuiz = $pdo->prepare("
                    UPDATE quizzes
                    SET lesson_id = ?, title = ?, description = ?
                    WHERE id = ?
                ");
                $stmtUpdateQuiz->execute([
                    $normalizedPayload['lesson_id'],
                    $normalizedPayload['title'],
                    $normalizedPayload['category'],
                    $quizId,
                ]);
            }

            saveQuizTree($pdo, $quizId, $normalizedPayload);
            $pdo->commit();

            $savedQuiz = fetchQuiz($pdo, $quizId);
            jsonResponse($method === 'POST' ? 201 : 200, [
                'status' => 'success',
                'message' => $method === 'POST' ? 'Tạo quiz thành công.' : 'Cập nhật quiz thành công.',
                'data' => ['quiz' => $savedQuiz],
            ]);
            break;

        case 'DELETE':
            $quizId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
            if ($quizId <= 0) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Quiz cần xóa không hợp lệ.']);
            }

            $stmtDeleteQuiz = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
            $stmtDeleteQuiz->execute([$quizId]);

            if ($stmtDeleteQuiz->rowCount() === 0) {
                jsonResponse(404, ['status' => 'error', 'message' => 'Không tìm thấy quiz để xóa.']);
            }

            jsonResponse(200, ['status' => 'success', 'message' => 'Xóa quiz thành công.']);
            break;

        default:
            jsonResponse(405, ['status' => 'error', 'message' => 'Method not allowed.']);
    }
} catch (InvalidArgumentException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    jsonResponse(422, ['status' => 'error', 'message' => $exception->getMessage()]);
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi máy chủ khi xử lý quiz.']);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    jsonResponse(500, ['status' => 'error', 'message' => 'Đã có lỗi không mong muốn xảy ra.']);
}
