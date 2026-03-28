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
    // 1. READ: Lấy danh sách
    case 'GET':
        $sql = "SELECT * FROM categories ORDER BY id DESC";
        $result = $conn->query($sql);
        $categories = [];
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        // Chuẩn hóa: bọc trong {"status": "success", "data": ...}
        echo json_encode([
            "status" => "success", 
            "data" => $categories
        ]);
        break;

    // 2. CREATE: Thêm mới
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if(!empty($data->name)) {
            $name = $conn->real_escape_string($data->name);
            $description = !empty($data->description) ? $conn->real_escape_string($data->description) : "";
            
            $sql = "INSERT INTO categories (name, description) VALUES ('$name', '$description')";
            if($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Thêm danh mục thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi CSDL: " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Vui lòng nhập tên danh mục"]);
        }
        break;

    // 3. UPDATE: Sửa danh mục
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        // Nếu dùng $_GET['id'] trong URL thì lấy từ $_GET, nếu trong body thì lấy từ $data
        $id = isset($_GET['id']) ? (int)$_GET['id'] : (!empty($data->id) ? (int)$data->id : 0);

        if($id > 0 && !empty($data->name)) {
            $name = $conn->real_escape_string($data->name);
            $description = !empty($data->description) ? $conn->real_escape_string($data->description) : "";
            
            $sql = "UPDATE categories SET name='$name', description='$description' WHERE id=$id";
            if($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Cập nhật thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi cập nhật CSDL"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Thiếu thông tin cập nhật"]);
        }
        break;

    // 4. DELETE: Xóa danh mục
    case 'DELETE':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if($id > 0) {
            $sql = "DELETE FROM categories WHERE id=$id";
            if($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Xóa thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi xóa CSDL (có thể do ràng buộc khóa ngoại)"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Không tìm thấy ID để xóa"]);
        }
        break;
}

$conn->close();
?>