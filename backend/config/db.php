<?php
// backend/config/db.php

// Kiểm tra xem có đang chạy ở Localhost hay không
$isLocalhost = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1');

if ($isLocalhost) {
    // --- CẤU HÌNH CHO MÁY CỦA BẠN (LOCALHOST) ---
    $host = 'localhost';
    $dbname = 'learning_english';
    $username = 'root';
    $password = ''; // Thường để trống ở local
} else {
    // --- CẤU HÌNH CHO HOSTING (TTTN375) ---
    $host = 'localhost'; 
    $dbname = 'viakingv_da2026'; // Thay bằng tên DB thật trên host
    $username = 'viakingv_da2026';  // Thay bằng user thật trên host
    $password = 'viakingv_da2026'; // Thay bằng pass thật trên host
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Dòng này để test, nếu chạy ổn thì nên comment lại
    // echo "Kết nối thành công!"; 
} catch (PDOException $e) {
    // Đừng hiện lỗi chi tiết cho người dùng xem trên host để bảo mật
    if ($isLocalhost) {
        die("Lỗi kết nối Local: " . $e->getMessage());
    } else {
        die("Hệ thống đang bảo trì, vui lòng quay lại sau!");
    }
}