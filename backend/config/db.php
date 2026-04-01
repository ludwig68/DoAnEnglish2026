<?php
// Bật hiển thị lỗi tối đa để bắt bệnh
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost'; 
$dbname = 'viakingv_da2026'; 
$username = 'viakingv_da2026'; 
$password = 'viakingv_da2026'; // Đảm bảo mật khẩu này khớp 100%

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Nếu kết nối thành công, nó sẽ in dòng dưới đây
    echo "CHÚC MỪNG: Kết nối Database thành công rực rỡ!"; 
} catch (PDOException $e) {
    // Nếu lỗi, nó sẽ in chính xác lỗi gì (sai pass, sai user, hay chưa cấp quyền)
    die("LỖI THẬT SỰ ĐÂY: " . $e->getMessage());
}