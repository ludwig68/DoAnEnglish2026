<?php
/**
 * backend/api/user/quiz_detail.php
 * Trả về chi tiết quiz + câu hỏi + options cho học viên làm bài.
 * Yêu cầu: học viên đã đăng nhập (role: student / instructor).
 */
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$authUser = JwtHelper::requireAuth();
$quizId = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : 0;

if ($quizId <= 0) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'quiz_id là bắt buộc.']);
    exit;
}

try {
    // 1. Lấy thông tin quiz
    $stmtQuiz = $pdo->prepare("
        SELECT
            q.id, q.lesson_id, q.title, q.category, q.total_points, q.description,
            l.title AS lesson_title, c.title AS course_title
        FROM quizzes q
        INNER JOIN lessons l ON l.id = q.lesson_id
        INNER JOIN courses c ON c.id = l.course_id
        WHERE q.id = ?
        LIMIT 1
    ");
    $stmtQuiz->execute([$quizId]);
    $quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Quiz không tồn tại.']);
        exit;
    }

    // 2. Lấy danh sách câu hỏi
    $stmtQ = $pdo->prepare("
        SELECT id, question_type, question_text, audio_url, hint, explanation, order_num
        FROM questions
        WHERE quiz_id = ?
        ORDER BY order_num ASC, id ASC
    ");
    $stmtQ->execute([$quizId]);
    $rawQuestions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    // 3. Lấy options theo từng câu hỏi
    $questionIds = array_column($rawQuestions, 'id');
    $optionMap = [];

    if (!empty($questionIds)) {
        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $stmtOpts = $pdo->prepare("
            SELECT id, question_id, option_text, match_text, is_correct, order_num
            FROM question_options
            WHERE question_id IN ($placeholders)
            ORDER BY order_num ASC, id ASC
        ");
        $stmtOpts->execute($questionIds);

        foreach ($stmtOpts->fetchAll(PDO::FETCH_ASSOC) as $opt) {
            $qId = (int) $opt['question_id'];
            $optionMap[$qId][] = [
                'id'          => (int) $opt['id'],
                'option_text' => $opt['option_text'],
                'match_text'  => $opt['match_text'],
                // Chỉ trả về is_correct sau khi nộp bài (ko lộ đáp án)
                // Để hiển thị đáp án ngay sau khi chọn, set is_correct = true
                'is_correct'  => (bool) $opt['is_correct'],
                'order_num'   => (int) $opt['order_num'],
            ];
        }
    }

    $questions = array_map(function ($q) use ($optionMap) {
        $qId = (int) $q['id'];
        return [
            'id'            => $qId,
            'question_type' => $q['question_type'],
            'question_text' => $q['question_text'],
            'audio_url'     => $q['audio_url'],
            'hint'          => $q['hint'],
            'explanation'   => $q['explanation'],
            'order_num'     => (int) $q['order_num'],
            'options'       => $optionMap[$qId] ?? [],
        ];
    }, $rawQuestions);

    echo json_encode([
        'status' => 'success',
        'data'   => [
            'id'           => (int) $quiz['id'],
            'title'        => $quiz['title'],
            'category'     => $quiz['category'],
            'total_points' => (int) $quiz['total_points'],
            'description'  => $quiz['description'],
            'lesson_title' => $quiz['lesson_title'],
            'course_title' => $quiz['course_title'],
            'questions'    => $questions,
        ],
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Lỗi Database: ' . $e->getMessage()]);
}
?>
