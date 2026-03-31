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
    $classDetailId = trim((string) ($data['class_detail_id'] ?? ''));
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
        return ['error' => 'Vui lòng chọn lớp học.'];
    }

    $classInt = Validator::checkIntOrDecimal($classId, 'int');
    if ($classInt !== true) {
        return ['error' => 'Mã lớp học phải là số nguyên.'];
    }

    $classPositive = Validator::checkPositiveNegative((int) $classId, 'positive');
    if ($classPositive !== true) {
        return ['error' => 'Mã lớp học không hợp lệ.'];
    }

    $classStmt = $pdo->prepare("SELECT start_date, end_date FROM classes WHERE id = ?");
    $classStmt->execute([(int) $classId]);
    $classData = $classStmt->fetch(PDO::FETCH_ASSOC);
    if (!$classData) {
        return ['error' => 'Lớp học không tồn tại trên hệ thống.'];
    }

    if ($classDetailId === '') {
        return ['error' => 'Vui lòng chọn hoặc tạo Nhóm lịch học trước khi xếp lịch.'];
    }

    $detailStmt = $pdo->prepare("SELECT COUNT(*) FROM class_details WHERE id = ? AND class_id = ?");
    $detailStmt->execute([(int) $classDetailId, (int) $classId]);
    if ((int) $detailStmt->fetchColumn() === 0) {
        return ['error' => 'Nhóm lịch học không hợp lệ hoặc không thuộc lớp học này.'];
    }

    if ($teacherId !== '') {
        $teacherInt = Validator::checkIntOrDecimal($teacherId, 'int');
        if ($teacherInt !== true) {
            return ['error' => 'Mã giảng viên phải là số nguyên.'];
        }

        $teacherPositive = Validator::checkPositiveNegative((int) $teacherId, 'positive');
        if ($teacherPositive !== true) {
            return ['error' => 'Mã giảng viên không hợp lệ.'];
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
            return ['error' => 'Giảng viên đứng lớp được chọn không hợp lệ hoặc đã nghỉ việc.'];
        }
    }

    $dateRequired = Validator::isNotEmpty($studyDate);
    if ($dateRequired !== true) {
        return ['error' => 'Vui lòng chọn ngày học.'];
    }

    $dateCheck = Validator::isValidDate($studyDate, 'Y-m-d');
    if ($dateCheck !== true) {
        return ['error' => 'Định dạng ngày học không hợp lệ.'];
    }

    if (!empty($classData['start_date']) && $studyDate < $classData['start_date']) {
        return ['error' => 'Ngày học không được diễn ra trước ngày khai giảng của lớp.'];
    }

    if (!empty($classData['end_date']) && $studyDate > $classData['end_date']) {
        return ['error' => 'Ngày học không được diễn ra sau ngày kết thúc dự kiến của lớp.'];
    }

    $startRequired = Validator::isNotEmpty($startTime);
    if ($startRequired !== true) {
        return ['error' => 'Vui lòng nhập giờ bắt đầu.'];
    }

    $endRequired = Validator::isNotEmpty($endTime);
    if ($endRequired !== true) {
        return ['error' => 'Vui lòng nhập giờ kết thúc.'];
    }

    $timePattern = '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/';
    $startTimeCheck = Validator::checkRegex($startTime, $timePattern, 'Giờ bắt đầu không hợp lệ.');
    if ($startTimeCheck !== true) {
        return ['error' => $startTimeCheck];
    }

    $endTimeCheck = Validator::checkRegex($endTime, $timePattern, 'Giờ kết thúc không hợp lệ.');
    if ($endTimeCheck !== true) {
        return ['error' => $endTimeCheck];
    }

    $normalizedStartTime = normalizeTimeInput($startTime);
    $normalizedEndTime = normalizeTimeInput($endTime);
    if ($normalizedStartTime >= $normalizedEndTime) {
        return ['error' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu.'];
    }

    $allowedTeachingTypes = ['offline', 'online'];
    if (!in_array($teachingType, $allowedTeachingTypes, true)) {
        return ['error' => 'Hình thức giảng dạy không hợp lệ.'];
    }

    $allowedStatuses = ['scheduled', 'completed', 'canceled'];
    if (!in_array($status, $allowedStatuses, true)) {
        return ['error' => 'Trạng thái ca dạy không hợp lệ.'];
    }

    if ($roomInfo !== '') {
        $roomCheck = Validator::checkStringLength($roomInfo, 2, 255);
        if ($roomCheck !== true) {
            return ['error' => 'Thông tin Phòng học / Link học phải từ 2 đến 255 ký tự.'];
        }

        if ($teachingType === 'online') {
            $roomUrlCheck = Validator::checkRegex($roomInfo, '/^https?:\/\/\S+$/i', 'Link học trực tuyến phải bắt đầu bằng http:// hoặc https://.');
            if ($roomUrlCheck !== true) {
                return ['error' => $roomUrlCheck];
            }
        }
    }

    if ($note !== '') {
        $noteCheck = Validator::checkStringLength($note, 3, 1000);
        if ($noteCheck !== true) {
            return ['error' => 'Ghi chú thêm phải từ 3 đến 1000 ký tự (nếu có).'];
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
        return ['error' => 'Lớp học này đã có lịch dạy trùng giờ trong ngày được chọn.'];
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
            return ['error' => 'Giảng viên đứng lớp đã có lịch dạy trùng giờ trong ngày được chọn.'];
        }
    }

    return [
        'data' => [
            'class_id' => (int) $classId,
            'class_detail_id' => (int) $classDetailId,
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
                cd.detail_name AS class_detail_name,
                co.title AS course_name,
                COALESCE(u.full_name, iu.full_name) AS teacher_name
            FROM schedules s
            LEFT JOIN class_details cd ON s.class_detail_id = cd.id
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
            jsonResponse(422, ['status' => 'error', 'message' => 'Kiểu lặp lịch không hợp lệ.']);
        }

        $validated = validateSchedulePayload($pdo, $payload);
        if (isset($validated['error'])) {
            jsonResponse(422, ['status' => 'error', 'message' => $validated['error']]);
        }

        $baseData = $validated['data'];
        $originalStudyDate = $baseData['study_date'];
        $scheduleDates = [$originalStudyDate];
        $autoShiftedMessage = '';

        if ($recurrencePattern !== 'none') {
            $rangeCheck = Validator::checkStartEndDate($originalStudyDate, $recurrenceEndDate, 'Y-m-d');
            if ($rangeCheck !== true) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Ngày kết thúc lặp lịch không hợp lệ hoặc nhỏ hơn ngày bắt đầu.']);
            }

            $scheduleDates = buildRecurringDates($originalStudyDate, $recurrenceEndDate, $recurrenceWeekdays);
            
            if ($scheduleDates === []) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Không tìm thấy ngày học nào hợp lệ trong khoảng thời gian đã chọn.']);
            }

            if ($scheduleDates[0] !== $originalStudyDate) {
                $actualDateStr = date('d/m/Y', strtotime($scheduleDates[0]));
                $autoShiftedMessage = " Lưu ý: Ngày học đã được tự động dời lên ngày {$actualDateStr} để khớp với lịch học lặp theo thứ bạn chọn.";
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO schedules (
                class_id, class_detail_id, teacher_id, study_date, start_time, end_time, teaching_type, room_info, status, note
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
                    $data['class_detail_id'],
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
                    throw new RuntimeException('Lỗi cơ sở dữ liệu khi lưu ca dạy mới.');
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
        $message = "Đã tạo thành công {$count} buổi học.";
        $message .= $autoShiftedMessage;
        
        jsonResponse(200, ['status' => 'success', 'message' => trim($message)]);
        break;

    case 'PUT':
        $payload = json_decode(file_get_contents("php://input"), true) ?? [];
        $id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($payload['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Mã ca dạy không hợp lệ.']);
        }

        $existsStmt = $pdo->prepare("SELECT COUNT(*) FROM schedules WHERE id = ?");
        $existsStmt->execute([$id]);
        if ((int) $existsStmt->fetchColumn() === 0) {
            jsonResponse(404, ['status' => 'error', 'message' => 'Ca dạy không tồn tại hoặc đã bị xóa.']);
        }

        $validated = validateSchedulePayload($pdo, $payload, $id);
        if (isset($validated['error'])) {
            jsonResponse(422, ['status' => 'error', 'message' => $validated['error']]);
        }

        $data = $validated['data'];
        $stmt = $pdo->prepare("
            UPDATE schedules
            SET class_id = ?, class_detail_id = ?, teacher_id = ?, study_date = ?, start_time = ?, end_time = ?, teaching_type = ?, room_info = ?, status = ?, note = ?
            WHERE id = ?
        ");

        $success = $stmt->execute([
            $data['class_id'],
            $data['class_detail_id'],
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
            jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi khi lưu thông tin lên cơ sở dữ liệu.']);
        }

        jsonResponse(200, ['status' => 'success', 'message' => 'Cập nhật lịch học thành công.']);
        break;

    case 'DELETE':
        // 1. Lấy ID của bản ghi lịch học và phạm vi xóa (scope)
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $scope = trim((string) ($_GET['scope'] ?? 'single'));

        // 2. Kiểm tra ID hợp lệ (phải là số dương)
        $idCheck = Validator::checkPositiveNegative($id, 'positive');
        if ($idCheck !== true) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Mã ca dạy không hợp lệ.']);
        }

        // 3. Truy vấn để lấy thông tin lớp học (class_id) từ ID lịch học này
        $scheduleStmt = $pdo->prepare("
            SELECT id, class_id, class_detail_id 
            FROM schedules 
            WHERE id = ? 
            LIMIT 1
        ");
        $scheduleStmt->execute([$id]);
        $schedule = $scheduleStmt->fetch();

        if (!$schedule) {
            jsonResponse(404, ['status' => 'error', 'message' => 'Ca dạy không tồn tại hoặc đã bị xóa.']);
        }

        // 4. Xử lý logic xóa theo phạm vi (Scope)
        if ($scope === 'class') {
            // Xóa TOÀN BỘ lịch của lớp học này (không phân biệt ngày tháng)
            $stmt = $pdo->prepare("DELETE FROM schedules WHERE class_id = ?");
            $success = $stmt->execute([(int) $schedule['class_id']]);

            if (!$success) {
                jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi khi xóa toàn bộ lịch của lớp.']);
            }

            jsonResponse(200, [
                'status' => 'success', 
                'message' => 'Đã xóa thành công toàn bộ lịch dạy của lớp này.'
            ]);
        } elseif ($scope === 'group') {
            if (empty($schedule['class_detail_id'])) {
                jsonResponse(422, ['status' => 'error', 'message' => 'Ca dạy này không thuộc nhóm lịch học nào.']);
            }
            // Xóa TOÀN BỘ lịch của nhóm lịch học này
            $stmt = $pdo->prepare("DELETE FROM schedules WHERE class_detail_id = ?");
            $success = $stmt->execute([(int) $schedule['class_detail_id']]);

            if (!$success) {
                jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi khi xóa toàn bộ nhóm lịch học.']);
            }

            jsonResponse(200, [
                'status' => 'success', 
                'message' => 'Đã xóa thành công toàn bộ nhóm lịch dạy.'
            ]);
        } else {
            // Mặc định: Chỉ xóa một ca học duy nhất (scope=single)
            $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
            $success = $stmt->execute([$id]);

            if (!$success) {
                jsonResponse(500, ['status' => 'error', 'message' => 'Lỗi khi xóa ca dạy.']);
            }

            jsonResponse(200, [
                'status' => 'success', 
                'message' => 'Đã xóa ca dạy thành công.'
            ]);
        }
        break;
}
