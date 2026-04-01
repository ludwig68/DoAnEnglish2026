<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

JwtHelper::requireAuth('admin');

const QUIZ_CATEGORIES = ['grammar', 'vocabulary', 'reading', 'listening', 'speaking', 'writing'];
const QUESTION_TYPES = ['matching', 'multiple_choice', 'fill_blank', 'dictation', 'writing'];

$method = $_SERVER['REQUEST_METHOD'];

function json_response(int $statusCode, array $body): void
{
    http_response_code($statusCode);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function normalize_optional_string(mixed $value): ?string
{
    $value = trim((string) ($value ?? ''));
    return $value !== '' ? $value : null;
}

function require_positive_int(mixed $value, string $label): int
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

function require_string_length(string $value, int $min, int $max, string $label): string
{
    $check = Validator::checkStringLength($value, $min, $max);
    if ($check !== true) {
        throw new InvalidArgumentException($label . ': ' . $check);
    }

    return $value;
}

function validate_quiz_category(?string $category): string
{
    $category = strtolower(trim((string) $category));
    if (!in_array($category, QUIZ_CATEGORIES, true)) {
        throw new InvalidArgumentException('Quiz category is invalid.');
    }

    return $category;
}

function validate_question_type(?string $type): string
{
    $type = strtolower(trim((string) $type));
    if (!in_array($type, QUESTION_TYPES, true)) {
        throw new InvalidArgumentException('Question type is invalid.');
    }

    return $type;
}

function lesson_exists(PDO $pdo, int $lessonId): bool
{
    $stmt = $pdo->prepare('SELECT id FROM lessons WHERE id = ? LIMIT 1');
    $stmt->execute([$lessonId]);
    return (bool) $stmt->fetch();
}

function validate_quiz_payload(PDO $pdo, array $payload): array
{
    $title = require_string_length(trim((string) ($payload['title'] ?? '')), 3, 255, 'Quiz title');
    $lessonId = require_positive_int($payload['lesson_id'] ?? null, 'Lesson');
    $category = validate_quiz_category($payload['category'] ?? '');
    $totalPoints = require_positive_int($payload['total_points'] ?? null, 'Total points');
    $description = normalize_optional_string($payload['description'] ?? null);
    $questions = array_values(array_filter($payload['questions'] ?? [], 'is_array'));

    if (!lesson_exists($pdo, $lessonId)) {
        throw new InvalidArgumentException('Selected lesson does not exist.');
    }

    if (count($questions) === 0) {
        throw new InvalidArgumentException('Quiz must contain at least one question.');
    }

    $normalizedQuestions = [];
    foreach ($questions as $questionIndex => $question) {
        $normalizedQuestions[] = validate_question_payload($question, $questionIndex);
    }

    return [
        'title' => $title,
        'lesson_id' => $lessonId,
        'category' => $category,
        'total_points' => $totalPoints,
        'description' => $description,
        'questions' => $normalizedQuestions,
    ];
}

function validate_question_payload(array $question, int $questionIndex): array
{
    $questionId = isset($question['id']) && $question['id'] !== '' ? (int) $question['id'] : null;
    $questionType = validate_question_type($question['question_type'] ?? '');
    $questionText = require_string_length(trim((string) ($question['question_text'] ?? '')), 3, 10000, 'Question text #' . ($questionIndex + 1));
    $audioUrl = normalize_optional_string($question['audio_url'] ?? null);
    $hint = normalize_optional_string($question['hint'] ?? null);
    $explanation = normalize_optional_string($question['explanation'] ?? null);
    $options = array_values(array_filter($question['options'] ?? [], 'is_array'));

    if ($audioUrl !== null) {
        require_string_length($audioUrl, 5, 255, 'Audio URL #' . ($questionIndex + 1));
    }
    if ($hint !== null) {
        require_string_length($hint, 2, 255, 'Hint #' . ($questionIndex + 1));
    }
    if ($explanation !== null) {
        require_string_length($explanation, 2, 5000, 'Explanation #' . ($questionIndex + 1));
    }

    $normalizedOptions = [];
    foreach ($options as $optionIndex => $option) {
        $normalizedOptions[] = validate_option_payload($option, $questionType, $questionIndex, $optionIndex);
    }

    if ($questionType === 'multiple_choice') {
        if (count($normalizedOptions) < 2) {
            throw new InvalidArgumentException('Multiple choice question #' . ($questionIndex + 1) . ' must have at least 2 options.');
        }

        $correctCount = count(array_filter($normalizedOptions, fn ($item) => (int) $item['is_correct'] === 1));
        if ($correctCount < 1) {
            throw new InvalidArgumentException('Multiple choice question #' . ($questionIndex + 1) . ' must have at least 1 correct option.');
        }
    }

    if ($questionType === 'matching' && count($normalizedOptions) < 1) {
        throw new InvalidArgumentException('Matching question #' . ($questionIndex + 1) . ' must have at least 1 pair.');
    }

    if ($questionType === 'fill_blank' || $questionType === 'dictation') {
        if (count($normalizedOptions) !== 1) {
            throw new InvalidArgumentException('Question #' . ($questionIndex + 1) . ' must contain exactly 1 answer option.');
        }

        if ($questionType === 'dictation' && $audioUrl === null) {
            throw new InvalidArgumentException('Dictation question #' . ($questionIndex + 1) . ' requires audio_url.');
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
        'options' => $normalizedOptions,
    ];
}

function validate_option_payload(array $option, string $questionType, int $questionIndex, int $optionIndex): array
{
    $optionId = isset($option['id']) && $option['id'] !== '' ? (int) $option['id'] : null;
    $optionText = trim((string) ($option['option_text'] ?? ''));
    $matchText = normalize_optional_string($option['match_text'] ?? null);
    $isCorrect = !empty($option['is_correct']) ? 1 : 0;

    if ($questionType === 'multiple_choice') {
        require_string_length($optionText, 1, 255, 'Option text #' . ($questionIndex + 1) . '.' . ($optionIndex + 1));
        return [
            'id' => $optionId,
            'option_text' => $optionText,
            'match_text' => null,
            'is_correct' => $isCorrect,
            'order_num' => $optionIndex + 1,
        ];
    }

    if ($questionType === 'matching') {
        require_string_length($optionText, 1, 255, 'Matching left #' . ($questionIndex + 1) . '.' . ($optionIndex + 1));
        require_string_length((string) $matchText, 1, 255, 'Matching right #' . ($questionIndex + 1) . '.' . ($optionIndex + 1));
        return [
            'id' => $optionId,
            'option_text' => $optionText,
            'match_text' => $matchText,
            'is_correct' => 1,
            'order_num' => $optionIndex + 1,
        ];
    }

    if ($questionType === 'fill_blank' || $questionType === 'dictation') {
        require_string_length($optionText, 1, 255, 'Answer #' . ($questionIndex + 1));
        return [
            'id' => $optionId,
            'option_text' => $optionText,
            'match_text' => null,
            'is_correct' => 1,
            'order_num' => 1,
        ];
    }

    return [
        'id' => $optionId,
        'option_text' => $optionText,
        'match_text' => $matchText,
        'is_correct' => $isCorrect,
        'order_num' => $optionIndex + 1,
    ];
}

function fetch_quiz_tree(PDO $pdo, int $quizId): ?array
{
    $stmtQuiz = $pdo->prepare("
        SELECT
            q.id,
            q.lesson_id,
            q.title,
            q.category,
            q.total_points,
            q.description,
            q.created_at,
            l.title AS lesson_title,
            l.order_number AS lesson_order_number,
            l.course_id,
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

    return [
        'id' => (int) $quiz['id'],
        'lesson_id' => (int) $quiz['lesson_id'],
        'course_id' => (int) $quiz['course_id'],
        'title' => $quiz['title'],
        'category' => $quiz['category'],
        'total_points' => (int) $quiz['total_points'],
        'description' => $quiz['description'],
        'created_at' => $quiz['created_at'],
        'lesson_title' => $quiz['lesson_title'],
        'lesson_order_number' => (int) $quiz['lesson_order_number'],
        'course_title' => $quiz['course_title'],
        'questions' => array_map(function (array $question) use ($optionMap): array {
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
        }, $questions),
    ];
}

function fetch_builder_meta(PDO $pdo): array
{
    $stmtCourses = $pdo->query("
        SELECT
            c.id,
            c.title,
            c.level,
            c.description,
            COUNT(l.id) AS lesson_count
        FROM courses c
        LEFT JOIN lessons l ON l.course_id = c.id
        GROUP BY c.id, c.title, c.level, c.description
        ORDER BY c.title ASC, c.id ASC
    ");

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
            q.category,
            q.total_points,
            q.created_at,
            l.title AS lesson_title,
            l.order_number AS lesson_order_number,
            c.id AS course_id,
            c.title AS course_title,
            COUNT(ques.id) AS question_count
        FROM quizzes q
        INNER JOIN lessons l ON l.id = q.lesson_id
        INNER JOIN courses c ON c.id = l.course_id
        LEFT JOIN questions ques ON ques.quiz_id = q.id
        GROUP BY q.id, q.lesson_id, q.title, q.category, q.total_points, q.created_at, l.title, l.order_number, c.id, c.title
        ORDER BY q.id DESC
    ");

    return [
        'courses' => array_map(function (array $course): array {
            return [
                'id' => (int) $course['id'],
                'title' => $course['title'],
                'level' => $course['level'],
                'description' => $course['description'],
                'lesson_count' => (int) $course['lesson_count'],
                'label' => trim($course['title'] . ($course['level'] ? ' - ' . $course['level'] : '')),
            ];
        }, $stmtCourses->fetchAll(PDO::FETCH_ASSOC)),
        'lessons' => array_map(function (array $lesson): array {
            return [
                'id' => (int) $lesson['id'],
                'title' => $lesson['title'],
                'order_number' => (int) $lesson['order_number'],
                'course_id' => (int) $lesson['course_id'],
                'course_title' => $lesson['course_title'],
                'label' => $lesson['course_title'] . ' - Bài ' . $lesson['order_number'] . ': ' . $lesson['title'],
            ];
        }, $stmtLessons->fetchAll(PDO::FETCH_ASSOC)),
        'quizzes' => array_map(function (array $quiz): array {
            return [
                'id' => (int) $quiz['id'],
                'lesson_id' => (int) $quiz['lesson_id'],
                'course_id' => (int) $quiz['course_id'],
                'title' => $quiz['title'],
                'category' => $quiz['category'],
                'total_points' => (int) $quiz['total_points'],
                'lesson_title' => $quiz['lesson_title'],
                'lesson_order_number' => (int) $quiz['lesson_order_number'],
                'course_title' => $quiz['course_title'],
                'question_count' => (int) $quiz['question_count'],
                'created_at' => $quiz['created_at'],
            ];
        }, $stmtQuizzes->fetchAll(PDO::FETCH_ASSOC)),
    ];
}

function save_question_options(PDO $pdo, int $questionId, array $options): void
{
    $stmtExisting = $pdo->prepare('SELECT id FROM question_options WHERE question_id = ?');
    $stmtExisting->execute([$questionId]);
    $existingOptionIds = array_map('intval', array_column($stmtExisting->fetchAll(PDO::FETCH_ASSOC), 'id'));
    $keptOptionIds = [];

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
                throw new InvalidArgumentException('An option does not belong to the question being updated.');
            }

            $stmtUpdate->execute([
                $option['option_text'],
                $option['match_text'],
                $option['is_correct'],
                $orderNum,
                $optionId,
                $questionId,
            ]);
            $keptOptionIds[] = $optionId;
            continue;
        }

        $stmtInsert->execute([
            $questionId,
            $option['option_text'],
            $option['match_text'],
            $option['is_correct'],
            $orderNum,
        ]);
        $keptOptionIds[] = (int) $pdo->lastInsertId();
    }

    $staleOptionIds = array_values(array_diff($existingOptionIds, $keptOptionIds));
    if (!empty($staleOptionIds)) {
        $placeholders = implode(',', array_fill(0, count($staleOptionIds), '?'));
        $stmtDelete = $pdo->prepare("DELETE FROM question_options WHERE question_id = ? AND id IN ($placeholders)");
        $stmtDelete->execute(array_merge([$questionId], $staleOptionIds));
    }
}

function save_quiz_tree(PDO $pdo, int $quizId, array $payload): void
{
    $stmtExistingQuestions = $pdo->prepare('SELECT id FROM questions WHERE quiz_id = ?');
    $stmtExistingQuestions->execute([$quizId]);
    $existingQuestionIds = array_map('intval', array_column($stmtExistingQuestions->fetchAll(PDO::FETCH_ASSOC), 'id'));
    $keptQuestionIds = [];

    $stmtInsertQuestion = $pdo->prepare("
        INSERT INTO questions (quiz_id, question_type, question_text, audio_url, hint, explanation, order_num)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtUpdateQuestion = $pdo->prepare("
        UPDATE questions
        SET question_type = ?, question_text = ?, audio_url = ?, hint = ?, explanation = ?, order_num = ?
        WHERE id = ? AND quiz_id = ?
    ");

    foreach ($payload['questions'] as $questionIndex => $question) {
        $questionId = isset($question['id']) && $question['id'] ? (int) $question['id'] : null;
        $orderNum = $questionIndex + 1;

        if ($questionId !== null) {
            if (!in_array($questionId, $existingQuestionIds, true)) {
                throw new InvalidArgumentException('A question does not belong to the quiz being updated.');
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
            $keptQuestionIds[] = $questionId;
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
            $keptQuestionIds[] = $questionId;
        }

        save_question_options($pdo, $questionId, $question['options']);
    }

    $staleQuestionIds = array_values(array_diff($existingQuestionIds, $keptQuestionIds));
    if (!empty($staleQuestionIds)) {
        $placeholders = implode(',', array_fill(0, count($staleQuestionIds), '?'));
        $stmtDelete = $pdo->prepare("DELETE FROM questions WHERE quiz_id = ? AND id IN ($placeholders)");
        $stmtDelete->execute(array_merge([$quizId], $staleQuestionIds));
    }
}

try {
    if ($method === 'GET') {
        $quizId = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : (int) ($_GET['id'] ?? 0);
        if ($quizId <= 0) {
            json_response(200, ['status' => 'success', 'data' => fetch_builder_meta($pdo)]);
        }

        $quiz = fetch_quiz_tree($pdo, $quizId);
        if ($quiz === null) {
            json_response(404, ['status' => 'error', 'message' => 'Quiz not found.']);
        }

        json_response(200, ['status' => 'success', 'data' => $quiz]);
    }

    if ($method === 'POST' || $method === 'PUT') {
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        $normalizedPayload = validate_quiz_payload($pdo, $payload);
        $quizId = $method === 'PUT'
            ? (isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0))
            : 0;

        if ($method === 'PUT' && $quizId <= 0) {
            json_response(422, ['status' => 'error', 'message' => 'Quiz id is required for update.']);
        }

        $pdo->beginTransaction();

        if ($method === 'POST') {
            $stmtInsertQuiz = $pdo->prepare("
                INSERT INTO quizzes (lesson_id, title, category, total_points, description)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtInsertQuiz->execute([
                $normalizedPayload['lesson_id'],
                $normalizedPayload['title'],
                $normalizedPayload['category'],
                $normalizedPayload['total_points'],
                $normalizedPayload['description'],
            ]);
            $quizId = (int) $pdo->lastInsertId();
        } else {
            $stmtCheck = $pdo->prepare('SELECT id FROM quizzes WHERE id = ? LIMIT 1');
            $stmtCheck->execute([$quizId]);
            if (!$stmtCheck->fetch()) {
                $pdo->rollBack();
                json_response(404, ['status' => 'error', 'message' => 'Quiz not found for update.']);
            }

            $stmtUpdateQuiz = $pdo->prepare("
                UPDATE quizzes
                SET lesson_id = ?, title = ?, category = ?, total_points = ?, description = ?
                WHERE id = ?
            ");
            $stmtUpdateQuiz->execute([
                $normalizedPayload['lesson_id'],
                $normalizedPayload['title'],
                $normalizedPayload['category'],
                $normalizedPayload['total_points'],
                $normalizedPayload['description'],
                $quizId,
            ]);
        }

        save_quiz_tree($pdo, $quizId, $normalizedPayload);
        $pdo->commit();

        $savedQuiz = fetch_quiz_tree($pdo, $quizId);
        json_response($method === 'POST' ? 201 : 200, [
            'status' => 'success',
            'message' => $method === 'POST' ? 'Quiz created successfully.' : 'Quiz updated successfully.',
            'data' => $savedQuiz,
        ]);
    }

    if ($method === 'DELETE') {
        $quizId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($quizId <= 0) {
            json_response(422, ['status' => 'error', 'message' => 'Quiz id is required for delete.']);
        }

        $stmtDelete = $pdo->prepare('DELETE FROM quizzes WHERE id = ?');
        $stmtDelete->execute([$quizId]);

        if ($stmtDelete->rowCount() === 0) {
            json_response(404, ['status' => 'error', 'message' => 'Quiz not found for delete.']);
        }

        json_response(200, ['status' => 'success', 'message' => 'Quiz deleted successfully.']);
    }

    json_response(405, ['status' => 'error', 'message' => 'Method not allowed.']);
} catch (InvalidArgumentException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    json_response(422, ['status' => 'error', 'message' => $exception->getMessage()]);
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    json_response(500, ['status' => 'error', 'message' => 'Database error while processing the quiz request.']);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    json_response(500, ['status' => 'error', 'message' => 'Unexpected server error.']);
}
