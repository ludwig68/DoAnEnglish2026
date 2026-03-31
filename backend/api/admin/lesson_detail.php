<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Bật khối TRY..CATCH để bắt gọn mọi lỗi SQL
try {
    require_once '../../config/db.php';
    require_once '../../utils/Validator.php'; // THÊM RÀNG BUỘC Validator

    $method = $_SERVER['REQUEST_METHOD'];
    $schedule_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($schedule_id <= 0 && $method == 'GET') {
        echo json_encode(["status" => "error", "message" => "Thiếu ID buổi học."]);
        exit;
    }

    if ($method === 'GET') {
        // 1. Lấy thông tin chung
        $stmtInfo = $pdo->prepare("
            SELECT s.*, c.class_name, u.full_name as teacher_name 
            FROM schedules s 
            LEFT JOIN classes c ON s.class_id = c.id 
            LEFT JOIN users u ON s.teacher_id = u.id 
            WHERE s.id = ?
        ");
        $stmtInfo->execute([$schedule_id]);
        $scheduleInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        if (!$scheduleInfo) {
            echo json_encode(["status" => "error", "message" => "Không tìm thấy ca dạy này."]);
            exit;
        }

        // 2. Lấy danh sách Tài liệu In-class (Đang để uploaded_at)
        $stmtMaterials = $pdo->prepare("SELECT * FROM materials WHERE schedule_id = ? ORDER BY uploaded_at DESC");
        $stmtMaterials->execute([$schedule_id]);
        $materials = $stmtMaterials->fetchAll(PDO::FETCH_ASSOC);

        // 3. Lấy danh sách Bài tập Post-class
        $stmtAssignments = $pdo->prepare("SELECT * FROM assignments WHERE schedule_id = ? ORDER BY created_at DESC");
        $stmtAssignments->execute([$schedule_id]);
        $assignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success",
            "schedule" => $scheduleInfo,
            "materials" => $materials,
            "assignments" => $assignments
        ]);
        exit; // Kết thúc request tại đây để không chạy xuống dướI
    }

    // XỬ LÝ LƯU TÀI LIỆU & BÀI TẬP VÀO DATABASE
    if ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $action = isset($data['action']) ? $data['action'] : '';

        // 1. Lưu Tài liệu In-class
        if ($action === 'add_material') {
            $title = $data['title'] ?? '';
            $file_url = $data['file_url'] ?? '';
            $material_type = $data['material_type'] ?? 'document';
            $sch_id = $data['schedule_id'] ?? 0;

            // Kiểm tra rỗng
            $checkTitle = Validator::isNotEmpty($title);
            if ($checkTitle !== true) { echo json_encode(["status" => "error", "message" => "Tên tài liệu: " . $checkTitle]); exit; }
            
            $checkUrl = Validator::isNotEmpty($file_url);
            if ($checkUrl !== true) { echo json_encode(["status" => "error", "message" => "Link liên kết: " . $checkUrl]); exit; }

            // Validate Link Format basic
            if (!filter_var($file_url, FILTER_VALIDATE_URL)) {
                echo json_encode(["status" => "error", "message" => "Link tài liệu không đúng định dạng URL (VD: https://...)."]);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO materials (schedule_id, title, file_url, material_type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$sch_id, $title, $file_url, $material_type]);
            echo json_encode(["status" => "success", "message" => "Tải tài liệu lên thành công!"]);
            exit;
        }

        // 2. Lưu Bài tập Post-class
        if ($action === 'add_assignment') {
            $title = $data['title'] ?? '';
            $desc = $data['description'] ?? '';
            $deadline = $data['deadline'] ?? '';
            $sch_id = $data['schedule_id'] ?? 0;

            // Kiểm tra rỗng
            $checkTitle = Validator::isNotEmpty($title);
            if ($checkTitle !== true) { echo json_encode(["status" => "error", "message" => "Tiêu đề: " . $checkTitle]); exit; }
            
            $checkDesc = Validator::isNotEmpty($desc);
            if ($checkDesc !== true) { echo json_encode(["status" => "error", "message" => "Yêu cầu: " . $checkDesc]); exit; }

            $checkDeadline = Validator::isNotEmpty($deadline);
            if ($checkDeadline !== true) { echo json_encode(["status" => "error", "message" => "Hạn chót: " . $checkDeadline]); exit; }

            // Kiểm tra Thời gian tương lai
            $checkDate = Validator::checkFuturePastDate($deadline, 'future', 'Y-m-d H:i:s');
            if ($checkDate !== true) { echo json_encode(["status" => "error", "message" => "Hạn chót: " . $checkDate]); exit; }

            $stmt = $pdo->prepare("INSERT INTO assignments (schedule_id, title, description, deadline, assignment_type) VALUES (?, ?, ?, ?, 'post_class')");
            $stmt->execute([$sch_id, $title, $desc, $deadline]);
            echo json_encode(["status" => "success", "message" => "Đã giao bài tập cho lớp!"]);
            exit;
        }
    }

} catch (PDOException $e) {
    // NẾU SQL BỊ LỖI, IN RA CHÍNH XÁC LỖI GÌ ĐỂ FIX
    echo json_encode([
        "status" => "error", 
        "message" => "Lỗi CSDL: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error", 
        "message" => "Lỗi PHP: " . $e->getMessage()
    ]);
}
?>