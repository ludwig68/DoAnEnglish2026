<?php

function quizExists(PDO $pdo, int $quizId): bool
{
    $stmt = $pdo->prepare("SELECT id FROM quizzes WHERE id = ? LIMIT 1");
    $stmt->execute([$quizId]);

    return (bool) $stmt->fetchColumn();
}

function resolveStudentQuizContext(PDO $pdo, int $studentId, int $quizId, ?int $preferredClassId = null): ?array
{
    $conditions = [
        'q.id = ?',
        'e.student_id = ?',
        "e.status = 'active'",
    ];
    $params = [$quizId, $studentId];

    if ($preferredClassId !== null && $preferredClassId > 0) {
        $conditions[] = 'e.class_id = ?';
        $params[] = $preferredClassId;
    }

    $where = implode(' AND ', $conditions);

    $stmt = $pdo->prepare("
        SELECT
            q.id AS quiz_id,
            q.title AS quiz_title,
            q.category,
            q.total_points,
            q.description,
            l.id AS lesson_id,
            l.title AS lesson_title,
            l.course_id,
            co.title AS course_title,
            c.id AS class_id,
            c.class_name,
            e.class_detail_id
        FROM quizzes q
        INNER JOIN lessons l ON l.id = q.lesson_id
        INNER JOIN courses co ON co.id = l.course_id
        INNER JOIN classes c ON c.course_id = l.course_id
        INNER JOIN enrollments e ON e.class_id = c.id
        WHERE {$where}
        ORDER BY e.enrollment_date DESC, e.id DESC
        LIMIT 1
    ");
    $stmt->execute($params);

    $context = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$context) {
        return null;
    }

    $context['quiz_id'] = (int) $context['quiz_id'];
    $context['lesson_id'] = (int) $context['lesson_id'];
    $context['course_id'] = (int) $context['course_id'];
    $context['class_id'] = (int) $context['class_id'];
    $context['total_points'] = (int) ($context['total_points'] ?? 0);
    $context['class_detail_id'] = $context['class_detail_id'] !== null
        ? (int) $context['class_detail_id']
        : null;

    return $context;
}
