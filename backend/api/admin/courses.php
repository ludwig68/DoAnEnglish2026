<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);
JwtHelper::requireAuth('admin');

function normalizeNullableInt($value)
{
    if ($value === null || $value === '') {
        return null;
    }

    return (int) $value;
}

function validateCoursePayload(PDO $pdo, array $data): array
{
    $title = trim((string) ($data['title'] ?? ''));
    $description = trim((string) ($data['description'] ?? ''));
    $imageUrl = trim((string) ($data['image_url'] ?? ''));
    $level = trim((string) ($data['level'] ?? ''));
    $fee = $data['fee'] ?? 0;
    $categoryId = normalizeNullableInt($data['category_id'] ?? null);
    $pathId = normalizeNullableInt($data['path_id'] ?? null);
    $isFeatured = !empty($data['is_featured']) ? 1 : 0;

    $titleCheck = Validator::checkStringLength($title, 3, 255);
    if ($titleCheck !== true) {
        throw new InvalidArgumentException('Tên khóa học: ' . $titleCheck);
    }

    $feeCheck = Validator::checkIntOrDecimal($fee, 'decimal');
    if ($feeCheck !== true) {
        throw new InvalidArgumentException('Học phí: ' . $feeCheck);
    }

    $feeRangeCheck = Validator::checkNumberRange((float) $fee, 0, 1000000000);
    if ($feeRangeCheck !== true) {
        throw new InvalidArgumentException('Học phí: ' . $feeRangeCheck);
    }

    if ($description !== '') {
        $descriptionCheck = Validator::checkStringLength($description, 10, 5000);
        if ($descriptionCheck !== true) {
            throw new InvalidArgumentException('Mô tả: ' . $descriptionCheck);
        }
    }

    if ($imageUrl !== '') {
        $imageCheck = Validator::checkStringLength($imageUrl, 5, 255);
        if ($imageCheck !== true) {
            throw new InvalidArgumentException('Ảnh khóa học: ' . $imageCheck);
        }
    }

    if ($level !== '') {
        $levelCheck = Validator::checkStringLength($level, 1, 20);
        if ($levelCheck !== true) {
            throw new InvalidArgumentException('Trình độ: ' . $levelCheck);
        }
    }

    if ($categoryId !== null) {
        $categoryCheck = Validator::checkPositiveNegative($categoryId, 'positive');
        if ($categoryCheck !== true) {
            throw new InvalidArgumentException('Danh mục khóa học: ' . $categoryCheck);
        }

        $stmtCategory = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
        $stmtCategory->execute([$categoryId]);
        if (!$stmtCategory->fetch()) {
            throw new InvalidArgumentException('Danh mục khóa học không tồn tại.');
        }
    }

    if ($pathId !== null) {
        $pathCheck = Validator::checkPositiveNegative($pathId, 'positive');
        if ($pathCheck !== true) {
            throw new InvalidArgumentException('Lộ trình học: ' . $pathCheck);
        }

        $stmtPath = $pdo->prepare("SELECT id FROM learning_paths WHERE id = ?");
        $stmtPath->execute([$pathId]);
        if (!$stmtPath->fetch()) {
            throw new InvalidArgumentException('Lộ trình học không tồn tại.');
        }
    }

    return [
        'title' => $title,
        'description' => $description !== '' ? $description : null,
        'image_url' => $imageUrl !== '' ? $imageUrl : null,
        'level' => $level !== '' ? $level : null,
        'fee' => (float) $fee,
        'category_id' => $categoryId,
        'path_id' => $pathId,
        'is_featured' => $isFeatured,
    ];
}

try {
    switch ($method) {
        case 'GET':
            $stmtCourses = $pdo->query("
                SELECT
                    c.id,
                    c.title,
                    c.description,
                    c.image_url,
                    c.level,
                    c.fee,
                    c.is_featured,
                    c.category_id,
                    c.path_id,
                    cat.name AS category_name,
                    lp.title AS path_title,
                    DATE_FORMAT(c.created_at, '%d/%m/%Y') AS created_at,
                    COALESCE(lesson_stats.lesson_count, 0) AS lesson_count,
                    COALESCE(class_stats.class_count, 0) AS class_count
                FROM courses c
                LEFT JOIN categories cat ON cat.id = c.category_id
                LEFT JOIN learning_paths lp ON lp.id = c.path_id
                LEFT JOIN (
                    SELECT course_id, COUNT(*) AS lesson_count
                    FROM lessons
                    GROUP BY course_id
                ) AS lesson_stats ON lesson_stats.course_id = c.id
                LEFT JOIN (
                    SELECT course_id, COUNT(*) AS class_count
                    FROM classes
                    GROUP BY course_id
                ) AS class_stats ON class_stats.course_id = c.id
                ORDER BY c.id DESC
            ");
            $courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

            $stmtCategories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
            $categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

            $stmtPaths = $pdo->query("SELECT id, title FROM learning_paths ORDER BY title ASC");
            $paths = $stmtPaths->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'courses' => array_map(function ($course) {
                        return [
                            'id' => (int) $course['id'],
                            'title' => $course['title'],
                            'description' => $course['description'],
                            'image_url' => $course['image_url'],
                            'level' => $course['level'],
                            'fee' => (float) $course['fee'],
                            'is_featured' => (bool) $course['is_featured'],
                            'category_id' => $course['category_id'] !== null ? (int) $course['category_id'] : null,
                            'path_id' => $course['path_id'] !== null ? (int) $course['path_id'] : null,
                            'category_name' => $course['category_name'],
                            'path_title' => $course['path_title'],
                            'created_at' => $course['created_at'],
                            'lesson_count' => (int) $course['lesson_count'],
                            'class_count' => (int) $course['class_count'],
                        ];
                    }, $courses),
                    'categories' => array_map(function ($item) {
                        return [
                            'id' => (int) $item['id'],
                            'name' => $item['name'],
                        ];
                    }, $categories),
                    'paths' => array_map(function ($item) {
                        return [
                            'id' => (int) $item['id'],
                            'title' => $item['title'],
                        ];
                    }, $paths),
                ],
            ]);
            break;

        case 'POST':
            $payload = validateCoursePayload($pdo, $data ?? []);

            $stmt = $pdo->prepare("
                INSERT INTO courses (category_id, path_id, title, description, image_url, level, fee, is_featured)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $payload['category_id'],
                $payload['path_id'],
                $payload['title'],
                $payload['description'],
                $payload['image_url'],
                $payload['level'],
                $payload['fee'],
                $payload['is_featured'],
            ]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Tạo khóa học thành công!',
            ]);
            break;

        case 'PUT':
            $id = (int) ($data['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Khóa học không hợp lệ.']);
                exit;
            }

            $stmtCheck = $pdo->prepare("SELECT id FROM courses WHERE id = ?");
            $stmtCheck->execute([$id]);
            if (!$stmtCheck->fetch()) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy khóa học.']);
                exit;
            }

            $payload = validateCoursePayload($pdo, $data ?? []);

            $stmt = $pdo->prepare("
                UPDATE courses
                SET category_id = ?, path_id = ?, title = ?, description = ?, image_url = ?, level = ?, fee = ?, is_featured = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $payload['category_id'],
                $payload['path_id'],
                $payload['title'],
                $payload['description'],
                $payload['image_url'],
                $payload['level'],
                $payload['fee'],
                $payload['is_featured'],
                $id,
            ]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Cập nhật khóa học thành công!',
            ]);
            break;

        case 'DELETE':
            $id = (int) ($_GET['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Khóa học không hợp lệ.']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Đã xóa khóa học!',
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
            break;
    }
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi Database: ' . $e->getMessage(),
    ]);
}
