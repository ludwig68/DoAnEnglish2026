<?php
// backend/config/db.php

$host = 'localhost';
$db   = 'learning_english'; // Tên database ta đã tạo trước đó
$user = 'root';             // User mặc định của WAMP/XAMPP
$pass = '';                 // Mật khẩu mặc định của WAMP/XAMPP (thường để trống)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Báo lỗi rõ ràng
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Trả về mảng dễ đọc
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Bảo mật chống SQL Injection
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Nếu lỗi, trả về JSON để Frontend Vue.js có thể đọc được
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()
    ]);
    exit;
}
?>