<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Kết nối Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "learning_english";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Kết nối thất bại"]));
}
$conn->set_charset("utf8mb4");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // 1. READ: Lấy danh sách lộ trình học
    case 'GET':
        $sql = "SELECT * FROM learning_paths ORDER BY id DESC";
        $result = $conn->query($sql);
        $paths = [];
        while($row = $result->fetch_assoc()) {
            $paths[] = $row;
        }
        echo json_encode([
            "status" => "success", 
            "data" => $paths
        ]);
        break;

    // 2. CREATE: Thêm lộ trình mới
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if(!empty($data->title) && !empty($data->target_audience)) {
            $title = $conn->real_escape_string($data->title);
            $description = !empty($data->description) ? $conn->real_escape_string($data->description) : "";
            $target_audience = $conn->real_escape_string($data->target_audience);
            
            $sql = "INSERT INTO learning_paths (title, description, target_audience) VALUES ('$title', '$description', '$target_audience')";
            if($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Thêm lộ trình học thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi CSDL: " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Vui lòng nhập Tên lộ trình và Đối tượng mục tiêu"]);
        }
        break;

    // 3. UPDATE: Cập nhật lộ trình
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $id = isset($_GET['id']) ? (int)$_GET['id'] : (!empty($data->id) ? (int)$data->id : 0);

        if($id > 0 && !empty($data->title) && !empty($data->target_audience)) {
            $title = $conn->real_escape_string($data->title);
            $description = !empty($data->description) ? $conn->real_escape_string($data->description) : "";
            $target_audience = $conn->real_escape_string($data->target_audience);
            
            $sql = "UPDATE learning_paths SET title='$title', description='$description', target_audience='$target_audience' WHERE id=$id";
            if($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Cập nhật thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi cập nhật CSDL"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Thiếu thông tin cập nhật bắt buộc"]);
        }
        break;

    // 4. DELETE: Xóa lộ trình
    case 'DELETE':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if($id > 0) {
            $sql = "DELETE FROM learning_paths WHERE id=$id";
            if($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Xóa thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi xóa CSDL (có thể do lộ trình này đang có khóa học)"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Không tìm thấy ID để xóa"]);
        }
        break;
}

$conn->close();
?>