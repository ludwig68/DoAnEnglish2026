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
    // 1. READ: Lấy danh sách liên hệ (Mới nhất lên đầu)
    case 'GET':
        $sql = "SELECT * FROM contacts ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $contacts = [];
        while($row = $result->fetch_assoc()) {
            $contacts[] = $row;
        }
        echo json_encode([
            "status" => "success", 
            "data" => $contacts
        ]);
        break;

    // 2. CREATE: Thêm liên hệ (Thường do User gọi từ Frontend, nhưng làm thêm cho Admin nếu cần nhập tay)
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if(!empty($data->full_name) && !empty($data->email) && !empty($data->message)) {
            $full_name = $conn->real_escape_string($data->full_name);
            $email = $conn->real_escape_string($data->email);
            $message = $conn->real_escape_string($data->message);
            $is_replied = !empty($data->is_replied) ? 1 : 0;
            
            $sql = "INSERT INTO contacts (full_name, email, message, is_replied) VALUES ('$full_name', '$email', '$message', $is_replied)";
            if($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Thêm liên hệ thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi CSDL: " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Vui lòng nhập đủ Tên, Email và Nội dung"]);
        }
        break;

    // 3. UPDATE: Cập nhật thông tin / Đánh dấu đã phản hồi
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        $id = isset($_GET['id']) ? (int)$_GET['id'] : (!empty($data->id) ? (int)$data->id : 0);

        if($id > 0 && !empty($data->full_name)) {
            $full_name = $conn->real_escape_string($data->full_name);
            $email = $conn->real_escape_string($data->email);
            $message = $conn->real_escape_string($data->message);
            $is_replied = !empty($data->is_replied) ? 1 : 0;
            
            $sql = "UPDATE contacts SET full_name='$full_name', email='$email', message='$message', is_replied=$is_replied WHERE id=$id";
            if($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Cập nhật liên hệ thành công"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Lỗi cập nhật CSDL"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Thiếu thông tin cập nhật bắt buộc"]);
        }
        break;

    // 4. DELETE: Xóa liên hệ
    case 'DELETE':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if($id > 0) {
            $sql = "DELETE FROM contacts WHERE id=$id";
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