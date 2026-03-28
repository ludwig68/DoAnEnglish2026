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
    // 1. READ: Lấy danh sách đăng ký tư vấn (Mới nhất xếp lên trên)
    case 'GET':
        $sql = "SELECT * FROM consultations ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $consultations = [];
        while($row = $result->fetch_assoc()) {
            $consultations[] = $row;
        }
        echo json_encode([
            "status" => "success", 
            "data" => $consultations
        ]);
        break;

    // 2. CREATE: Thêm yêu cầu tư vấn (Dành cho trường hợp Admin tự nhập tay)
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if(!empty($data->full_name) && !empty($data->phone)) {
            $full_name = $conn->real_escape_string($data->full_name);
            $phone = $conn->real_escape_string($data->phone);
            $email = !empty($data->email) ? $conn->real_escape_string($data->email) : "";
            $note = !empty($data->note) ? $conn->real_escape_string($data->note) : "";
            $status = !empty($data->status) ? $conn->real_escape_string($data->status) : "pending";
            
            $sql = "INSERT INTO consultations (full_name, phone, email, note, status) VALUES ('$full_name', '$phone', '$email', '$note', '$status')";
            if($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Thêm yêu cầu tư vấn thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi CSDL: " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Vui lòng nhập Họ tên và Số điện thoại"]);
        }
        break;

    // 3. UPDATE: Cập nhật thông tin và trạng thái tư vấn
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $id = isset($_GET['id']) ? (int)$_GET['id'] : (!empty($data->id) ? (int)$data->id : 0);

        if($id > 0 && !empty($data->full_name) && !empty($data->phone)) {
            $full_name = $conn->real_escape_string($data->full_name);
            $phone = $conn->real_escape_string($data->phone);
            $email = !empty($data->email) ? $conn->real_escape_string($data->email) : "";
            $note = !empty($data->note) ? $conn->real_escape_string($data->note) : "";
            $status = !empty($data->status) ? $conn->real_escape_string($data->status) : "pending";
            
            $sql = "UPDATE consultations SET full_name='$full_name', phone='$phone', email='$email', note='$note', status='$status' WHERE id=$id";
            if($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Cập nhật yêu cầu tư vấn thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi cập nhật CSDL"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Thiếu thông tin cập nhật bắt buộc"]);
        }
        break;

    // 4. DELETE: Xóa yêu cầu tư vấn
    case 'DELETE':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if($id > 0) {
            $sql = "DELETE FROM consultations WHERE id=$id";
            if($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Xóa thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi xóa CSDL"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Không tìm thấy ID để xóa"]);
        }
        break;
}

$conn->close();
?>