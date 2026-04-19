<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';
require_once __DIR__ . '/../../utils/QuizAccess.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$authUser = JwtHelper::requireAuth();
if (($authUser['role'] ?? '') !== 'student') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Chỉ học viên mới được truy cập bài quiz.']);
    exit;
}

$studentId = (int) ($authUser['sub'] ?? 0);
$quizId = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : 0;
$preferredClassId = isset($_GET['class_id']) ? (int) $_GET['class_id'] : null;

if ($quizId <= 0) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'quiz_id là bắt buộc.']);
    exit;
}

try {
    $quiz = resolveStudentQuizContext($pdo, $studentId, $quizId, $preferredClassId);
    if (!$quiz) {
        $exists = quizExists($pdo, $quizId);
        http_response_code($exists ? 403 : 404);
        echo json_encode([
            'status' => 'error',
            'message' => $exists
                ? 'Bạn không thuộc lớp được phép làm quiz này.'
                : 'Quiz không tồn tại.',
        ]);
        exit;
    }

    // Check if quiz is already submitted to allow showing correct answers
    $stmtSubmitted = $pdo->prepare("SELECT COUNT(*) FROM quiz_submissions WHERE student_id = ? AND quiz_id = ?");
    $stmtSubmitted->execute([$studentId, $quizId]);
    $isSubmitted = (int)$stmtSubmitted->fetchColumn() > 0;

    $stmtQuestions = $pdo->prepare("
        SELECT id, question_type, question_text, audio_url, hint, explanation, order_num
        FROM questions
        WHERE quiz_id = ?
        ORDER BY order_num ASC, id ASC
    ");
    $stmtQuestions->execute([$quizId]);
    $rawQuestions = $stmtQuestions->fetchAll(PDO::FETCH_ASSOC);

    $questionIds = array_column($rawQuestions, 'id');
    $optionMap = [];

    if (!empty($questionIds)) {
        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $stmtOptions = $pdo->prepare("
            SELECT id, question_id, option_text, match_text, is_correct, order_num
            FROM question_options
            WHERE question_id IN ($placeholders)
            ORDER BY order_num ASC, id ASC
        ");
        $stmtOptions->execute($questionIds);

        foreach ($stmtOptions->fetchAll(PDO::FETCH_ASSOC) as $option) {
            $questionId = (int) $option['question_id'];
            $optionMap[$questionId][] = [
                'id' => (int) $option['id'],
                'option_text' => $option['option_text'],
                'match_text' => $option['match_text'],
                'is_correct' => $isSubmitted ? (int)$option['is_correct'] : null,
                'order_num' => (int) $option['order_num'],
            ];
        }
    }

    $questions = array_map(static function (array $question) use ($optionMap): array {
        $questionId = (int) $question['id'];

        return [
            'id' => $questionId,
            'question_type' => $question['question_type'],
            'question_text' => $question['question_text'],
            'audio_url' => $question['audio_url'],
            'hint' => $question['hint'],
            'explanation' => $question['explanation'],
            'order_num' => (int) $question['order_num'],
            'options' => $optionMap[$questionId] ?? [],
        ];
    }, $rawQuestions);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'id' => (int) $quiz['quiz_id'],
            'class_id' => (int) $quiz['class_id'],
            'title' => $quiz['quiz_title'],
            'category' => $quiz['category'],
            'total_points' => (int) $quiz['total_points'],
            'description' => $quiz['description'],
            'lesson_title' => $quiz['lesson_title'],
            'course_title' => $quiz['course_title'],
            'questions' => $questions,
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Lỗi Database: ' . $e->getMessage()]);
}
