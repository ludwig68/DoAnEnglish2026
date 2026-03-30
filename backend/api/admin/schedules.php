<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

$method = $_SERVER['REQUEST_METHOD'];
JwtHelper::requireAuth('admin');

function jsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function fetchTeacherOptions(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT id, full_name
        FROM users
        WHERE role = 'instructor'
          AND status = 'active'
        ORDER BY full_name ASC, id ASC
    ");

    return $stmt->fetchAll();
}

function normalizeTimeInput(string $value): string
{
    return preg_match('/^\d{2}:\d{2}$/', $value) ? $value . ':00' : $value;
}

function getRecurrenceWeekdays(string $pattern): array
{
    $map = [
        'none' => [],
        'mon_wed_fri' => [1, 3, 5],
        'tue_thu_sat' => [2, 4, 6],
    ];

    return $map[$pattern] ?? [];
}

function buildRecurringDates(string $startDate, string $endDate, array $weekdays): array
{
    $dates = [];
    $cursor = new DateTimeImmutable($startDate);
    $last = new DateTimeImmutable($endDate);

    while ($cursor <= $last) {
        $weekday = (int) $cursor->format('N');
        if (in_array($weekday, $weekdays, true)) {
            $dates[] = $cursor->format('Y-m-d');
        }
        $cursor = $cursor->modify('+1 day');
    }

    return $dates;
}

function validateSchedulePayload(PDO $pdo, array $data, int $excludeId = 0): array
{
    $classId = trim((string) ($data['class_id'] ?? ''));
    $teacherId = trim((string) ($data['teacher_id'] ?? ''));
    $studyDate = trim((string) ($data['study_date'] ?? ''));
    $startTime = trim((string) ($data['start_time'] ?? ''));
    $endTime = trim((string) ($data['end_time'] ?? ''));
    $teachingType = trim((string) ($data['teaching_type'] ?? 'offline'));
    $roomInfo = trim((string) ($data['room_info'] ?? ''));
    $status = trim((string) ($data['status'] ?? 'scheduled'));
    $note = trim((string) ($data['note'] ?? ''));

    $classRequired = Validator::isNotEmpty($classId);
    if ($classRequired !== true) {
        return ['error' => 'Vui long chon lop hoc.'];
    }

    $classInt = Validator::checkIntOrDecimal($classId, 'int');
    if ($classInt !== true) {
        return ['error' => 'Ma lop hoc phai la so nguyen.'];
    }

    $classPositive = Validator::checkPositiveNegative((int) $classId, 'positive');
    if ($classPositive !== true) {
        return ['error' => 'Ma lop hoc phai lon hon 0.'];
    }

    $classStmt = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE id = ?");
    $classStmt->execute([(int) $classId]);
    if ((int) $classStmt->fetchColumn() === 0) {
        return ['error' => 'Lop hoc khong ton tai.'];
    }

    if ($teacherId !== '') {
        $teacherInt = Validator::checkIntOrDecimal($teacherId, 'int');
        if ($teacherInt !== true) {
            return ['error' => 'Ma giang vien phai la so nguyen.'];
        }

        $teacherPositive = Validator::checkPositiveNegative((int) $teacherId, 'positive');
        if ($teacherPositive !== true) {
            return ['error' => 'Ma giang vien phai lon hon 0.'];
        }

        $teacherStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM users
            WHERE id = ?
              AND role = 'instructor'
              AND status = 'active'
        ");
        $teacherStmt->execute([(int) $teacherId]);
        if ((int) $teacherStmt->fetchColumn() === 0) {
            return ['error' => 'Giang vien duoc chon khong hop le.'];
        }
    }

    $dateRequired = Validator::isNotEmpty($studyDate);
    if ($dateRequired !== true) {
        return ['error' => 'Vui long chon ngay hoc.'];
    }

    $dateCheck = Validator::isValidDate($studyDate, 'Y-m-d');
    if ($dateCheck !== true) {
        return ['error' => 'Ngay hoc khong hop le.'];
    }

    $startRequired = Validator::isNotEmpty($startTime);
    if ($startRequired !== true) {
        return ['error' => 'Vui long nhap gio bat dau.'];
    }

    $endRequired = Validator::isNotEmpty($endTime);
    if ($endRequired !== true) {
        return ['error' => 'Vui long nhap gio ket thuc.'];
    }

    $timePattern = '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/';
    $startTimeCheck = Validator::checkRegex($startTime, $timePattern, 'Gio bat dau khong hop le.');
    if ($startTimeCheck !== true) {
        return ['error' => $startTimeCheck];
    }

    $endTimeCheck = Validator::checkRegex($endTime, $timePattern, 'Gio ket thuc khong hop le.');
    if ($endTimeCheck !== true) {
        return ['error' => $endTimeCheck];
    }

    $normalizedStartTime = normalizeTimeInput($startTime);
    $normalizedEndTime = normalizeTimeInput($endTime);
    if ($normalizedStartTime >= $normalizedEndTime) {
        return ['error' => 'Gio ket thuc phai lon hon gio bat dau.'];
    }

    $allowedTeachingTypes = ['offline', 'online'];
    if (!in_array($teachingType, $allowedTeachingTypes, true)) {
        return ['error' => 'Hinh thuc day khong hop le.'];
    }

    $allowedStatuses = ['scheduled', 'completed', 'canceled'];
    if (!in_array($status, $allowedStatuses, true)) {
        return ['error' => 'Trang thai lich hoc khong hop le.'];
    }

    if ($roomInfo !== '') {
        $roomCheck = Validator::checkStringLength($roomInfo, 2, 255);
        if ($roomCheck !== true) {
            return ['error' => 'Thong tin phong hoc/link hoc phai tu 2 den 255 ky tu.'];
        }

        if ($teachingType === 'online') {
            $roomUrlCheck = Validator::checkRegex($roomInfo, '/^https?:\/\/\S+$/i', 'Link hoc online phai bat dau bang http:// hoac https://');
            if ($roomUrlCheck !== true) {
                return ['error' => $roomUrlCheck];
            }
        }
    }

    if ($note !== '') {
        $noteCheck = Validator::checkStringLength($note, 3, 1000);
        if ($noteCheck !== true) {
            return ['error' => 'Ghi chu phai tu 3 den 1000 ky tu neu co nhap.'];
        }
    }

    $classConflictStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM schedules
        WHERE class_id = ?
          AND study_date = ?
          AND id <> ?
          AND start_time < ?
          AND end_time > ?
    ");
    $classConflictStmt->execute([(int) $classId, $studyDate, $excludeId, $normalizedEndTime, $normalizedStartTime]);
    if ((int) $classConflictStmt->fetchColumn() > 0) {
        return ['error' => 'Lop hoc nay da co lich trung gio trong ngay duoc chon.'];
    }

    if ($teacherId !== '') {
        $teacherConflictStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM schedules
            WHERE teacher_id = ?
              AND study_date = ?
              AND id <> ?
              AND start_time < ?
              AND end_time > ?
        ");
        $teacherConflictStmt->execute([(int) $teacherId, $studyDate, $excludeId, $normalizedEndTime, $normalizedStartTime]);
        if ((int) $teacherConflictStmt->fetchColumn() > 0) {
            return ['error' => 'Giang vien nay da co lich trung gio trong ngay duoc chon.'];
        }
    }

    return [
        'data' => [
            'class_id' => (int) $classId,
            'teacher_id' => $teacherId !== '' ? (int) $teacherId : null,
            'study_date' => $studyDate,
            'start_time' => $normalizedStartTime,
            'end_time' => $normalizedEndTime,
            'teaching_type' => $teachingType,
            'room_info' => $roomInfo !== '' ? $roomInfo : null,
            'status' => $status,
            'note' => $note !== '' ? $note : null,
        ],
    ];
}

switch ($method) {
    case 'GET':
        $sql = "
            SELECT
                s.*,
                c.class_name,
                co.title AS course_name,
                COALESCE(u.full_name, iu.full_name) AS teacher_name
            FROM schedules s
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN courses co ON c.course_id = co.id
            LEFT JOIN users iu ON c.instructor_id = iu.id
            LEFT JOIN users u ON s.teacher_id = u.id
            ORDER BY s.study_date DESC, s.start_time DESC
        ";

        $stmt = $pdo->query($sql);
        $schedules = $stmt->fetchAll();

        $classes = $pdo->query("
            SELECT
                c.id,
                c.class_name,
                co.title AS course_name,
                c.start_date,
                c.end_date
            FROM classes c
            LEFT JOIN courses co ON c.course_id = co.id
            ORDER BY c.id DESC
        ")->fetchAll();

        $teachers = fetchTeacherOptions($pdo);

        jsonResponse(200, [
            'status' => 'success',
            'data' => $schedules,
            'classes' => $classes,
            'teachers' => $teachers, 
        ]);
        break;

    case 'POST':
        $payload = json_decode(file_get_contents("php://input"), true) ?? [];
        $recurrencePattern = trim((string) ($payload['recurrence_pattern'] ?? 'none'));
        $recurrenceEndDate = trim((string) ($payload['recurrence_end_date'] ?? ''));
        $recurrenceWeekdays = getRecurrenceWeekdays($recurrencePattern);

        if ($recurrencePattern !== 'none' && $recurrenceWeekdays === []) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Kieu lap lich khong hop le.']);
        }

        $validated = validateSchedulePayload($pdo, $payload);
        if (isset($validated['error'])) {
            jsonResponse(422, ['status' => 'error', 'message' => $validated['error']]);
        }

        $baseData = $validated['data'];
        $scheduleDates = [$baseData['study_date']];

        if ($recurrencePattern !== 'none') {
            $rangeCheck = Validator::checkStartEndDate($baseData['study_date'], $recurrenceEndDate, 'Y-m-d');
            if ($rangeCheck !== true) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Ngay ket thuc lap lich khong hop le.']);
            }

            $startWeekday = (int) (new DateTimeImmutable($baseData['study_date']))->format('N');
            if (!in_array($startWeekday, $recurrenceWeekdays, true)) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Ngay bat dau khong nam trong nhom thu lap lich da chon.']);
            }

            $scheduleDates = buildRecurringDates($baseData['study_date'], $recurrenceEndDate, $recurrenceWeekdays);
            if ($scheduleDates === []) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Khong tao duoc lich nao tu khoang ngay da chon.']);
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO schedules (
                class_id, teacher_id, study_date, start_time, end_time, teaching_type, room_info, status, note
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $pdo->beginTransaction();

        try {
            foreach ($scheduleDates as $studyDate) {
                $schedulePayload = $payload;
                $schedulePayload['study_date'] = $studyDate;

                $validatedItem = validateSchedulePayload($pdo, $schedulePayload);
                if (isset($validatedItem['error'])) {
                    throw new RuntimeException($validatedItem['error']);
                }

                $data = $validatedItem['data'];
                $success = $stmt->execute([
                    $data['class_id'],
                    $data['teacher_id'],
                    $data['study_date'],
                    $data['start_time'],
                    $data['end_time'],
                    $data['teaching_type'],
                    $data['room_info'],
                    $data['status'],
                    $data['note'],
                ]);

                if (!$success) {
                    throw new RuntimeException('Loi CSDL khi them lich.');
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonResponse(422, ['status' => 'error', 'message' => $e->getMessage()]);
        }

        $count = count($scheduleDates);
        $message = $count > 1
            ? "Da tao {$count} buoi hoc theo lich lap."
            : 'Da tao lich hoc thanh cong.';
        jsonResponse(200, ['status' => 'success', 'message' => $message]);
        break;

    case 'PUT':
        $payload = json_decode(file_get_contents("php://input"), true) ?? [];
        $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(422, ['status' => 'error', 'message' => 'ID lich hoc khong hop le.']);
        }

        $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE id = ?");
        $existsStmt->execute([$id]);
        if ((int) $existsStmt->fetchColumn() === 0) {
            jsonResponse(404, ['status' => 'error', 'message' => 'Lich hoc khong ton tai.']);
        }

        $validated = validateSchedulePayload($pdo, $payload, $id);
        if (isset($validated['error'])) {
            jsonResponse(422, ['status' => 'error', 'message' => $validated['error']]);
        }

        $data = $validated['data'];
        $stmt = $pdo->prepare("
            UPDATE schedules
            SET class_id = ?, teacher_id = ?, study_date = ?, start_time = ?, end_time = ?, teaching_type = ?, room_info = ?, status = ?, note = ?
            WHERE id = ?
        ");

        $success = $stmt->execute([
            $data['class_id'],
            $data['teacher_id'],
            $data['study_date'],
            $data['start_time'],
            $data['end_time'],
            $data['teaching_type'],
            $data['room_info'],
            $data['status'],
            $data['note'],
            $id,
        ]);

        if (!$success) {
            jsonResponse(500, ['status' => 'error', 'message' => 'Loi cap nhat CSDL.']);
        }

        jsonResponse(200, ['status' => 'success', 'message' => 'Cap nhat lich hoc thanh cong.']);
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $scope = trim((string) ($_GET['scope'] ?? 'single'));

        $idCheck = Validator::checkPositiveNegative($id, 'positive');
        if ($idCheck !== true) {
            jsonResponse(422, ['status' => 'error', 'message' => 'ID lich hoc khong hop le.']);
        }

        $scheduleStmt = $pdo->prepare("
            SELECT id, class_id, study_date
            FROM schedules
            WHERE id = ?
            LIMIT 1
        ");
        $scheduleStmt->execute([$id]);
        $schedule = $scheduleStmt->fetch();

        if (!$schedule) {
            jsonResponse(404, ['status' => 'error', 'message' => 'Lich hoc khong ton tai.']);
        }

        if ($scope === 'future') {
            $studyDate = trim((string) ($schedule['study_date'] ?? ''));
            $dateCheck = Validator::isValidDate($studyDate, 'Y-m-d');
            if ($dateCheck !== true) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Ngay hoc khong hop le.']);
            }

            $stmt = $pdo->prepare("DELETE FROM schedules WHERE class_id = ? AND study_date >= ?");
            $success = $stmt->execute([(int) $schedule['class_id'], $studyDate]);

            if (!$success) {
                jsonResponse(500, ['status' => 'error', 'message' => 'Loi xoa cac ca hoc tu moc thoi gian da chon.']);
            }

            jsonResponse(200, ['status' => 'success', 'message' => 'Da xoa ca hien tai va cac ca ve sau thanh cong.']);
        }

        $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
        $success = $stmt->execute([$id]);

        if (!$success) {
            jsonResponse(500, ['status' => 'error', 'message' => 'Loi xoa du lieu lich hoc.']);
        }

        jsonResponse(200, ['status' => 'success', 'message' => 'Da xoa ca hoc thanh cong.']);
        break;

    default:
        jsonResponse(405, ['status' => 'error', 'message' => 'Method not allowed.']);
}
